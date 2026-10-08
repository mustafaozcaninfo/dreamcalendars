from __future__ import annotations

import hashlib
import html
import json
from datetime import datetime, timezone, timedelta
from .engine import iso


def _escape(value):
    return str(value).replace("\\", "\\\\").replace("\r\n", "\\n").replace("\r", "\\n").replace("\n", "\\n").replace(";", "\\;").replace(",", "\\,")


def _fold(line):
    # RFC 5545 uses 75 OCTETS, not characters. Never split a UTF-8 codepoint.
    lines, current, length = [], "", 0
    for char in line:
        size = len(char.encode("utf-8"))
        if length + size > 75:
            lines.append(current)
            current, length = " ", 1
        current += char
        length += size
    lines.append(current)
    return "\r\n".join(lines)


def calendar_ics(report):
    """Confirmed source events only. PTO suggestions never masquerade as bookings."""
    lines = ["BEGIN:VCALENDAR", "VERSION:2.0", "PRODID:-//DreamCalendars//Reprint Prototype//EN", "CALSCALE:GREGORIAN"]
    stamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    for event in report["confirmed_events"]:
        uid = hashlib.sha256((report["household_id"] + ":" + event["id"]).encode()).hexdigest()[:32]
        start = iso(event["date"])
        details = f"Kind: {event['kind']}; source: {event['source_id']}; evidence: {event['evidence']}"
        if event["kind"] == "early_dismissal":
            details += f"; dismissal: {event['dismissal']} ({report['timezone']})"
        lines.extend(["BEGIN:VEVENT", f"UID:{uid}@reprint.dreamcalendars.com",
                      f"DTSTAMP:{stamp}", f"SEQUENCE:{report['revision']}",
                      "DTSTART;VALUE=DATE:" + start.strftime("%Y%m%d"),
                      "DTEND;VALUE=DATE:" + (start + timedelta(days=1)).strftime("%Y%m%d"),
                      "SUMMARY:" + _escape(event["title"]), "DESCRIPTION:" + _escape(details),
                      "STATUS:CONFIRMED", "TRANSP:TRANSPARENT", "END:VEVENT"])
    lines.append("END:VCALENDAR")
    return "\r\n".join(_fold(line) for line in lines) + "\r\n"


def render_html(report, impact=None):
    esc = lambda v: html.escape(str(v), quote=True)
    summary = report["summary"]
    events = report["confirmed_events"]
    cells = []
    for row in report["days"]:
        titles = "".join(f'<p class="event">{esc(e["title"])}</p>' for e in events if e["date"] == row["date"])
        pto = " · ".join(row["suggested_pto"])
        gap = row["remaining_gap_child_hours"]
        windows = ", ".join("–".join(v) for v in row["gap_windows"])
        cells.append(f'<article class="day {"risk" if gap else ""}"><h3>{esc(row["date"])}</h3>{titles}'
                     f'<p>{esc(pto) + ": PTO suggestion" if pto else "No PTO suggested"}</p>'
                     f'<strong>{esc(gap)} child-hours uncovered</strong><p>{esc(windows)}</p></article>')
    queue = "".join(f'<li><strong>{esc(e["date"])}</strong> — {esc(e["title"])}<br><small>{esc(e["evidence"])} · {esc(e["source_id"])}</small></li>' for e in report["review_queue"]) or "<li>No records awaiting review.</li>"
    source_rows = "".join(f'<tr><td>{esc(e["date"])}</td><td>{esc(e["title"])}</td><td>{esc(e["source_id"])}</td><td>{esc(e["evidence"])}</td><td>{esc(e["reviewed_by"])}</td></tr>' for e in events)
    impact_html = ""
    if impact:
        change_items = "".join(f'<li>{esc(v["id"])} — {esc(v["change"])} · {esc((v["after"] or v["before"])["status"])}</li>' for v in impact["event_changes"])
        impact_html = f'<section class="impact"><p class="eyebrow">Change impact · v{impact["old_revision"]} → v{impact["new_revision"]}</p><h2>Calendar changes created {impact["calendar_only_added_gap_child_hours"]:+g} child-hours of additional care demand.</h2><ul>{change_items}</ul><p>Remaining gap in the new plan: {impact["new_remaining_gap_child_hours"]} child-hours.</p></section>'
    return f'''<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>DreamCalendars Reprint · Example care plan</title>
<style>
:root{{--ink:#173b36;--paper:#f4f3eb;--green:#cdea8c;--muted:#5a706a;--line:#d5ded2}}*{{box-sizing:border-box}}body{{margin:0;background:var(--paper);color:var(--ink);font:16px/1.6 system-ui,sans-serif}}main{{max-width:1180px;margin:auto;padding:36px 24px}}nav{{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--line);padding-bottom:18px}}.brand{{font-weight:800}}.badge{{border:1px solid var(--ink);border-radius:30px;padding:4px 12px;font-size:12px}}.hero{{display:grid;grid-template-columns:2fr 1fr;gap:40px;padding:50px 0 28px}}h1{{font-size:clamp(32px,5vw,60px);line-height:1.08;margin:12px 0 20px;letter-spacing:-2px}}h2{{font-size:26px;line-height:1.25}}h3{{font-size:15px;margin:0 0 16px}}p{{margin:8px 0}}.eyebrow{{text-transform:uppercase;letter-spacing:2px;font-size:11px;font-weight:800}}.note{{padding:24px;border:1px solid var(--line);border-radius:16px;background:#fff}}.metrics{{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:12px 0 32px}}.metric{{padding:20px;border-radius:12px;border:1px solid var(--line);background:#fff}}.metric strong{{font-size:34px;display:block}}.metric.featured{{background:var(--green)}}.impact{{background:var(--ink);color:white;padding:24px 30px;border-radius:16px;margin:32px 0}}.calendar{{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}}.day{{padding:18px;border:1px solid var(--line);border-radius:12px;background:#fff;min-height:170px}}.day p{{font-size:13px}}.risk{{border:2px solid #a75528}}.risk strong{{color:#87431d}}.event{{font-weight:700}}.review{{margin:36px 0;padding:22px;border:1px dashed var(--muted);border-radius:12px}}small,.muted{{color:var(--muted)}}table{{border-collapse:collapse;width:100%;font-size:13px}}td,th{{padding:12px;text-align:left;border-bottom:1px solid var(--line)}}.tablewrap{{overflow-x:auto}}footer{{margin-top:32px;font-size:12px;border-top:1px solid var(--line);padding-top:16px}}button{{background:var(--ink);color:#fff;padding:12px 20px;border:0;border-radius:8px;cursor:pointer;font:inherit}}@media(max-width:760px){{.hero{{grid-template-columns:1fr}}.metrics,.calendar{{grid-template-columns:repeat(2,1fr)}}}}@media print{{body{{background:white}}main{{padding:0}}button,.hero .note{{display:none}}.hero{{display:block;padding:12px 0}}h1{{font-size:30px}}.metrics{{margin-bottom:16px}}.metric strong{{font-size:22px}}.day{{break-inside:avoid;min-height:120px}}.impact{{background:white;color:var(--ink);border:1px solid var(--ink)}}.review{{break-inside:avoid}}td{{overflow-wrap:anywhere}}@page{{size:A4 landscape;margin:12mm}}}}
</style></head><body><main>
<nav><span class="brand">DreamCalendars / Reprint</span><span class="badge">SYNTHETIC DEMO · v{report["revision"]}</span></nav>
<section class="hero"><div><p class="eyebrow">From printed dates to updated decisions</p><h1>The school day changed.<br>Did your paper plan?</h1><p>See what changed since your calendar was printed, and what it means for your household.</p><p class="muted">{esc(report["start"])} — {esc(report["end"])} · {esc(report["timezone"])}</p>{'<p><a href="patch.html">Print only the changed entries →</a></p>' if impact else ''}</div><aside class="note"><strong>A paper calendar with a change history.</strong><p>Source evidence → human review → change receipt → care impact → printable patch.</p><small>Synthetic data. Sources have not been verified online. PTO suggestions are not approved bookings.</small></aside></section>
<div class="metrics"><div class="metric"><strong>{summary["baseline_gap_child_hours"]:g}</strong>Initial care gap / child-hours</div><div class="metric featured"><strong>{summary["covered_by_suggested_pto_child_hours"]:g}</strong>Potentially covered by PTO / child-hours</div><div class="metric"><strong>{summary["remaining_gap_child_hours"]:g}</strong>Remaining care gap / child-hours</div><div class="metric"><strong>{summary["suggested_pto_days"]}</strong>Suggested PTO / person-days</div></div>
{impact_html}<section><h2>Day-by-day care plan</h2><p class="muted">Two children needing care for one hour count as two child-hours. An amber border marks an unresolved gap.</p><div class="calendar">{"".join(cells)}</div></section>
<section class="review"><h2>Awaiting review · {summary["review_queue_count"]}</h2><p>These records are excluded from calculations and calendar export.</p><ul>{queue}</ul></section>
<section><h2>What is the evidence?</h2><div class="tablewrap"><table><thead><tr><th>Date</th><th>Event</th><th>Source</th><th>Evidence</th><th>Reviewer</th></tr></thead><tbody>{source_rows}</tbody></table></div></section>
<footer><button type="button" onclick="window.print()">Print plan / Save as PDF</button><p>Local prototype · this report makes no network requests or account connections. Printed edition v{report["revision"]} is a snapshot; it does not update automatically.</p></footer>
</main></body></html>'''


def write_bundle(report, out, impact=None):
    out.mkdir(parents=True, exist_ok=True)
    (out / "plan.json").write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    (out / "calendar.ics").write_bytes(calendar_ics(report).encode("utf-8"))
    (out / "index.html").write_text(render_html(report, impact), encoding="utf-8")
    if impact is not None:
        (out / "changes.json").write_text(json.dumps(impact, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        (out / "patch.html").write_text(render_patch(report, impact), encoding="utf-8")
    else:
        # A reused export folder must not expose an obsolete correction receipt.
        for name in ("changes.json", "patch.html"):
            (out / name).unlink(missing_ok=True)


def render_patch(report, impact):
    """Physical correction strip for reviewed changes since a printed edition."""
    esc = lambda v: html.escape(str(v), quote=True)
    strips = []
    for change in sorted(impact["event_changes"], key=lambda v: ((v["after"] or v["before"])["date"], v["id"])):
        before, after = change["before"], change["after"]
        published_before = before if before and before["status"] == "confirmed" else None
        published_after = after if after and after["status"] == "confirmed" else None
        if not published_before and not published_after:
            continue
        # A source/evidence-only edit doesn't change the physical calendar entry.
        visible = lambda e: {k: e.get(k) for k in ("date", "title", "kind", "dismissal", "children")} if e else None
        if visible(published_before) == visible(published_after):
            continue
        describe = lambda e: f"{e['date']}: {e['title']}" + (f" — dismiss at {e['dismissal']}" if e["kind"] == "early_dismissal" else " — school closed")
        previous = describe(published_before) if published_before else "No published entry"
        current = describe(published_after) if published_after else "Remove the old entry; closure/dismissal is no longer confirmed."
        day = (published_after or published_before)["date"]
        row = next((v for v in report["days"] if v["date"] == day), None)
        prior = next((v["before"] for v in impact["days_changed"] if v["date"] == day), None)
        if row:
            delta = row["baseline_gap_child_hours"] - (prior["baseline_gap_child_hours"] if prior else row["baseline_gap_child_hours"])
            leave = ", ".join(row["suggested_pto"]) or "none"
            action = (f"Care gap before leave: {row['baseline_gap_child_hours']:g} child-hours ({delta:+g} vs printed edition). "
                      f"Suggested PTO: {leave}. With suggested leave: {row['remaining_gap_child_hours']:g} child-hours remain. "
                      "Leave is not yet approved.")
        else:
            action = "Change falls outside this planning horizon."
        strips.append(f'<article><p class="label">CORRECTION / {esc(change["id"])}</p><p><del>{esc(previous)}</del></p><h2>{esc(current)}</h2><p>{esc(action)}</p><small>Event evidence: {esc((published_after or published_before)["source_id"])}</small></article>')
    content = "".join(strips) or "<p>No reviewed changes require a paper correction.</p>"
    return f'''<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reprint · Calendar correction strips</title><style>
body{{font:16px/1.5 system-ui,sans-serif;margin:30px auto;max-width:820px;padding:20px;color:#173b36;background:#f4f3eb}}h1{{font-size:32px}}h2{{font-size:19px;margin:8px 0}}article{{background:white;border:2px dashed #63766e;padding:18px;margin:22px 0;break-inside:avoid}}.label{{font-size:11px;font-weight:800;letter-spacing:2px}}del{{color:#586963}}button{{padding:10px 16px;background:#173b36;color:white;border:0;border-radius:6px}}small{{font-size:12px}}@media print{{body{{margin:0;background:white;padding:0}}button{{display:none}}article{{margin:10px 0}}}}@page{{size:A4;margin:15mm}}
</style></head><body><p class="label">DREAMCALENDARS / REPRINT · SYNTHETIC DEMO</p><h1>Your printed dates changed.</h1><p>Correction receipt: edition v{impact["old_revision"]} → v{impact["new_revision"]}. Cut along the dashed borders and attach to the old calendar.</p><p>The original calendar is a snapshot. Only reviewed changes appear below. Care suggestions still require human approval.</p>{content}<button onclick="window.print()">Print correction strips</button><footer><p>New snapshot SHA-256: {esc(report.get("input_sha256", "not available"))}</p><small>Offline prototype. No QR resolver or automatic school monitoring is active.</small></footer></body></html>'''
