"""Half-hour care model and exact, bounded multi-budget dynamic program.

No network, AI claims, country holiday guesses or real children's data.
All intervals are local civil time in the household's single timezone.
"""
from __future__ import annotations

import copy
import hashlib
import itertools
import json
from datetime import date, timedelta
from zoneinfo import ZoneInfo, ZoneInfoNotFoundError


class InputError(ValueError):
    pass


def iso(value):
    try:
        parsed = date.fromisoformat(value)
        if parsed.isoformat() != value:
            raise ValueError()
        return parsed
    except (TypeError, ValueError):
        raise InputError(f"Expected an ISO date YYYY-MM-DD, got {value!r}") from None


def tick(value):
    try:
        hour, minute = map(int, value.split(":"))
        if value != f"{hour:02d}:{minute:02d}" or not (0 <= hour < 24 and minute in (0, 30)):
            raise ValueError()
        return hour * 2 + minute // 30
    except (AttributeError, ValueError):
        raise InputError(f"Time must be HH:MM on a 30-minute boundary: {value!r}") from None


def interval(value):
    if not isinstance(value, list) or len(value) != 2:
        raise InputError("Intervals need [start, end]")
    start, end = map(tick, value)
    if start >= end:
        raise InputError("Interval end must be after start; overnight intervals are unsupported")
    return range(start, end)


def _integer(value, low, high, label):
    if type(value) is not int or not low <= value <= high:
        raise InputError(f"{label} must be an integer between {low} and {high}")


def _weekdays(value):
    if not isinstance(value, list) or len(set(value)) != len(value):
        raise InputError("weekdays must be a list of unique integers")
    for item in value:
        _integer(item, 0, 6, "weekday (Monday=0)")


def validate(raw):
    if not isinstance(raw, dict):
        raise InputError("Input must be a JSON object")
    d = copy.deepcopy(raw)
    if d.get("schema_version") != 1:
        raise InputError("schema_version must be 1")
    _integer(d["revision"], 0, 1000000, "revision")
    if not isinstance(d["household_id"], str) or not d["household_id"]:
        raise InputError("household_id must be a nonempty string")
    try:
        ZoneInfo(d["timezone"])
    except (ZoneInfoNotFoundError, TypeError):
        raise InputError("Use an IANA timezone, e.g. America/New_York") from None
    first, last = iso(d["start"]), iso(d["end"])
    if not 0 <= (last - first).days <= 92:
        raise InputError("Planning horizon must be 1–93 inclusive days")
    for name, limit in (("adults", 6), ("children", 4)):
        if not isinstance(d[name], list) or not 1 <= len(d[name]) <= limit:
            raise InputError(f"{name}: need between 1 and {limit} entries")
        ids = [v["id"] for v in d[name]]
        if any(not isinstance(x, str) or not x for x in ids) or len(set(ids)) != len(ids):
            raise InputError(f"{name} IDs must be nonempty and unique")
    sources = d["sources"]
    if not isinstance(sources, list):
        raise InputError("sources must be a list")
    source_ids = [s["id"] for s in sources]
    if len(set(source_ids)) != len(source_ids):
        raise InputError("Duplicate source ID")
    for source in sources:
        for field in ("id", "title", "locator", "retrieved_on"):
            if not isinstance(source[field], str) or not source[field].strip():
                raise InputError(f"source.{field} must be nonempty")
        iso(source["retrieved_on"])
    for adult in d["adults"]:
        _integer(adult["pto_budget"], 0, 93, "pto_budget")
        _integer(adult["care_capacity"], 1, 4, "care_capacity")
        _weekdays(adult["work_weekdays"])
        interval(adult["work_hours"])
        for field in ("off_dates", "pto_blackouts"):
            for v in adult[field]:
                iso(v)
        for item in adult["unavailable"]:
            iso(item["date"])
            interval(item["hours"])
    for child in d["children"]:
        _weekdays(child["school_weekdays"])
        _weekdays(child["care_weekdays"])
        interval(child["school_hours"])
        interval(child["care_hours"])
        if child["baseline_confirmed"] is not True:
            raise InputError("School baseline must be explicitly confirmed by a reviewer")
        for item in child["external_care"]:
            iso(item["date"])
            interval(item["hours"])
    children = {v["id"] for v in d["children"]}
    event_ids = set()
    for event in d["events"]:
        if not isinstance(event["id"], str) or not event["id"] or event["id"] in event_ids:
            raise InputError("Event IDs must be nonempty and unique")
        event_ids.add(event["id"])
        if not isinstance(event["title"], str) or not event["title"].strip():
            raise InputError("Event title must be nonempty")
        if event["kind"] not in ("school_closed", "early_dismissal"):
            raise InputError("Supported kinds: school_closed, early_dismissal")
        if event["status"] not in ("confirmed", "proposed", "cancelled"):
            raise InputError("Supported statuses: confirmed, proposed, cancelled")
        if event["status"] == "confirmed" and not event.get("reviewed_by"):
            raise InputError("Confirmed event needs reviewed_by")
        iso(event["date"])
        if event["kind"] == "early_dismissal":
            tick(event["dismissal"])
        if not event["children"] or len(set(event["children"])) != len(event["children"]):
            raise InputError("Event needs unique affected children")
        if not set(event["children"]) <= children:
            raise InputError("Event references an unknown child")
        if event["source_id"] not in source_ids or not event["evidence"].strip():
            raise InputError("Each event needs a known source and evidence excerpt")
    # Conflicting overrides require review, never last-write-wins.
    overrides = set()
    for event in d["events"]:
        if event["status"] != "confirmed":
            continue
        for child in event["children"]:
            key = (child, event["date"])
            if key in overrides:
                raise InputError(f"Conflicting confirmed school events for {key}")
            overrides.add(key)
    return d


def days(d):
    first, last = iso(d["start"]), iso(d["end"])
    return [first + timedelta(days=i) for i in range((last - first).days + 1)]


def _demand(d, day):
    """Uncovered-by-school/external-care children per half-hour slot."""
    key = day.isoformat()
    slots = [0] * 48
    for child in d["children"]:
        if day.weekday() not in child["care_weekdays"]:
            continue
        school = set(interval(child["school_hours"])) if day.weekday() in child["school_weekdays"] else set()
        for event in d["events"]:
            if event["status"] != "confirmed" or event["date"] != key or child["id"] not in event["children"]:
                continue
            school = set() if event["kind"] == "school_closed" else {s for s in school if s < tick(event["dismissal"])}
        external = {s for v in child["external_care"] if v["date"] == key for s in interval(v["hours"])}
        for s in interval(child["care_hours"]):
            if s not in school and s not in external:
                slots[s] += 1
    return slots


def _capacity(adult, day, leave):
    key = day.isoformat()
    working = day.weekday() in adult["work_weekdays"] and key not in adult["off_dates"]
    unavailable = {s for v in adult["unavailable"] if v["date"] == key for s in interval(v["hours"])}
    if working and not leave:
        unavailable.update(interval(adult["work_hours"]))
    return [0 if s in unavailable else adult["care_capacity"] for s in range(48)]


def day_options(d, day):
    demands = _demand(d, day)
    adults = d["adults"]
    eligible = [i for i, a in enumerate(adults) if a["pto_budget"] > 0
                and day.weekday() in a["work_weekdays"] and day.isoformat() not in a["off_dates"]
                and day.isoformat() not in a["pto_blackouts"]]
    options = []
    for mask in itertools.product((0, 1), repeat=len(eligible)):
        leave = {i for i, flag in zip(eligible, mask) if flag}
        capacities = [_capacity(a, day, i in leave) for i, a in enumerate(adults)]
        residual = [max(0, need - sum(c[s] for c in capacities)) for s, need in enumerate(demands)]
        options.append({"cost": tuple(int(i in leave) for i in range(len(adults))),
                        "residual": residual, "uncovered_slots": sum(residual)})
    # Dominated choices cannot improve any later budget allocation.
    return [a for a in options if not any(
        b["uncovered_slots"] <= a["uncovered_slots"] and all(x <= y for x, y in zip(b["cost"], a["cost"]))
        and (b["uncovered_slots"] < a["uncovered_slots"] or b["cost"] != a["cost"])
        for b in options)]


def _spans(slots):
    spans, start = [], None
    for i in range(49):
        active = i < 48 and slots[i] > 0
        if active and start is None:
            start = i
        if not active and start is not None:
            clock = lambda s: f"{s // 2:02d}:{(s % 2) * 30:02d}"
            spans.append([clock(start), clock(i)])
            start = None
    return spans


def plan(d, *, max_states=25000):
    d = validate(d)
    horizon = days(d)
    budgets = tuple(a["pto_budget"] for a in d["adults"])
    zero = (0,) * len(budgets)
    # budget -> (uncovered child-half-hours, chosen daily options)
    states = {zero: (0, [])}
    before = 0
    for day in horizon:
        options = day_options(d, day)
        baseline = next(o for o in options if not any(o["cost"]))
        before += baseline["uncovered_slots"]
        next_states = {}
        for used, (score, path) in states.items():
            for option in options:
                new_used = tuple(a + b for a, b in zip(used, option["cost"]))
                if any(a > b for a, b in zip(new_used, budgets)):
                    continue
                new_score = score + option["uncovered_slots"]
                if new_used not in next_states or new_score < next_states[new_used][0]:
                    next_states[new_used] = (new_score, path + [option])
                if len(next_states) > max_states:
                    raise InputError("Exact solver state limit exceeded; reduce horizon/budgets. No approximate result exported.")
        states = next_states
    used, (remaining, chosen) = min(states.items(), key=lambda v: (v[1][0], sum(v[0]), v[0]))
    rows = []
    for day, option in zip(horizon, chosen):
        baseline = next(o for o in day_options(d, day) if not any(o["cost"]))
        rows.append({"date": day.isoformat(), "baseline_gap_child_hours": baseline["uncovered_slots"] / 2,
                     "remaining_gap_child_hours": option["uncovered_slots"] / 2,
                     "gap_windows": _spans(option["residual"]),
                     "suggested_pto": [a["id"] for a, flag in zip(d["adults"], option["cost"]) if flag]})
    confirmed = [e for e in d["events"] if e["status"] == "confirmed" and d["start"] <= e["date"] <= d["end"]]
    pending = [e for e in d["events"] if e["status"] == "proposed" and d["start"] <= e["date"] <= d["end"]]
    return {"schema_version": 1, "household_id": d["household_id"], "revision": d["revision"],
            "timezone": d["timezone"], "start": d["start"], "end": d["end"],
            "summary": {"baseline_gap_child_hours": before / 2, "remaining_gap_child_hours": remaining / 2,
                        "covered_by_suggested_pto_child_hours": (before - remaining) / 2,
                        "suggested_pto_days": sum(used), "pto_by_adult": dict(zip((a["id"] for a in d["adults"]), used)),
                        "review_queue_count": len(pending)},
            "days": rows, "confirmed_events": confirmed, "review_queue": pending, "sources": d["sources"],
            "assumptions": ["Synthetic/demo status depends on input; no source was fetched or independently verified.",
                            "Single household timezone; 30-minute same-day intervals; interchangeable caregivers with explicit capacity.",
                            "PTO suggestions require employer and caregiver approval; no appointment is booked.",
                            "Child-hours count simultaneous children separately. Unknown/proposed events are not treated as confirmed."]}


def compare(old, new):
    old, new = validate(old), validate(new)
    if old["household_id"] != new["household_id"] or old["timezone"] != new["timezone"]:
        raise InputError("Compare snapshots from the same household and timezone")
    if (old["start"], old["end"]) != (new["start"], new["end"]):
        raise InputError("Snapshots must have the same horizon for meaningful impact totals")
    if new["revision"] <= old["revision"]:
        raise InputError("New revision must be greater than old revision")
    a, b = {e["id"]: e for e in old["events"]}, {e["id"]: e for e in new["events"]}
    changes = []
    for key in sorted(a.keys() | b.keys()):
        if a.get(key) != b.get(key):
            changes.append({"id": key, "change": "added" if key not in a else "removed" if key not in b else "updated",
                            "before": a.get(key), "after": b.get(key)})
    # Isolate event-change effects from simultaneous household settings changes.
    counterfactual = copy.deepcopy(new)
    counterfactual["events"], counterfactual["sources"] = old["events"], old["sources"]
    if {c["id"] for c in old["children"]} != {c["id"] for c in new["children"]}:
        raise InputError("Child IDs must remain stable across a comparison")
    old_plan, new_plan, event_baseline = plan(old), plan(new), plan(counterfactual)
    config_fields = ("adults", "children")
    config_changed = any(old[k] != new[k] for k in config_fields)
    return {"old_revision": old["revision"], "new_revision": new["revision"], "event_changes": changes,
            "source_metadata_changed": old["sources"] != new["sources"], "household_settings_changed": config_changed,
            "calendar_only_added_gap_child_hours": new_plan["summary"]["baseline_gap_child_hours"] - event_baseline["summary"]["baseline_gap_child_hours"],
            "total_added_gap_child_hours": new_plan["summary"]["baseline_gap_child_hours"] - old_plan["summary"]["baseline_gap_child_hours"],
            "new_remaining_gap_child_hours": new_plan["summary"]["remaining_gap_child_hours"],
            "days_changed": [{"date": x["date"], "before": x, "after": y} for x, y in zip(old_plan["days"], new_plan["days"]) if x != y]}


def fingerprint(d):
    return hashlib.sha256(json.dumps(d, ensure_ascii=False, sort_keys=True, separators=(",", ":")).encode()).hexdigest()
