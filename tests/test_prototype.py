import copy
import io
import itertools
import json
import random
import tempfile
import unittest
import urllib.error
from pathlib import Path
from unittest.mock import patch

from dreamreprint.claude import extract, validate_candidates
from dreamreprint.cli import main
from dreamreprint.engine import InputError, compare, day_options, days, fingerprint, plan, validate
from dreamreprint.export import calendar_ics, render_html, render_patch, write_bundle

ROOT = Path(__file__).resolve().parents[1]


def fixture(version=2):
    return json.loads((ROOT / f"examples/edition-v{version}.json").read_text())


class PlanningTests(unittest.TestCase):
    def test_demo_values_and_pto_dates(self):
        result = plan(fixture())
        self.assertEqual(result["summary"]["baseline_gap_child_hours"], 12)
        self.assertEqual(result["summary"]["remaining_gap_child_hours"], 0)
        self.assertEqual(result["summary"]["pto_by_adult"], {"parent-a": 1, "parent-b": 1})
        choices = {r["date"]: r["suggested_pto"] for r in result["days"] if r["suggested_pto"]}
        self.assertEqual(choices, {"2026-10-16": ["parent-a"], "2026-10-20": ["parent-b"]})

    def test_global_optimum_matches_bruteforce(self):
        # Independently enumerate the full Cartesian product of daily options.
        # This catches greedy allocation that spends PTO on a smaller early gap.
        rng = random.Random(17)
        for _ in range(20):
            d = fixture()
            d.update(start="2026-10-19", end="2026-10-21", events=[])
            for adult in d["adults"]:
                adult.update(pto_budget=rng.randint(0, 2), off_dates=[], pto_blackouts=[], unavailable=[])
            for day in days(d):
                d["events"].append(dict(fixture()["events"][1], id=day.isoformat(), date=day.isoformat(), dismissal=f"{rng.randint(10,16):02d}:00"))
            budgets = tuple(a["pto_budget"] for a in d["adults"])
            brute = []
            for choices in itertools.product(*(day_options(d, day) for day in days(d))):
                used = tuple(sum(c["cost"][i] for c in choices) for i in range(len(budgets)))
                if all(a <= b for a, b in zip(used, budgets)):
                    brute.append((sum(c["uncovered_slots"] for c in choices)/2, sum(used), used))
            optimum = min(brute)
            result = plan(d)["summary"]
            self.assertEqual((result["remaining_gap_child_hours"], result["suggested_pto_days"], tuple(result["pto_by_adult"].values())), optimum)

    def test_zero_budget_and_external_care(self):
        d = fixture()
        for a in d["adults"]:
            a["pto_budget"] = 0
        d["children"][0]["external_care"] = [{"date":"2026-10-20", "hours":["09:00","17:00"]}]
        self.assertEqual(plan(d)["summary"]["remaining_gap_child_hours"], 4)

    def test_capacity_requires_global_reallocation(self):
        d = fixture()
        d["children"].append(dict(copy.deepcopy(d["children"][0]), id="child-2"))
        for e in d["events"]:
            e["children"].append("child-2")
        for a in d["adults"]:
            a["care_capacity"] = 1
        # Capacity can make the best allocation change: use A on Oct 12 and B
        # on Oct 20 instead of spending A's leave on the shorter Oct 16 gap.
        r = plan(d)["summary"]
        self.assertEqual(r["baseline_gap_child_hours"], 32)
        self.assertEqual(r["remaining_gap_child_hours"], 16)

    def test_external_care_overlap_is_not_double_counted(self):
        d=fixture()
        for a in d["adults"]: a["pto_budget"]=0
        d["children"][0]["external_care"]=[{"date":"2026-10-16","hours":["13:00","15:00"]},
                                               {"date":"2026-10-16","hours":["14:00","16:00"]}]
        self.assertEqual(plan(d)["summary"]["remaining_gap_child_hours"],9)

    def test_ambiguous_events_never_enter_plan(self):
        d = fixture()
        d["events"] = [d["events"][-1]]
        self.assertEqual(plan(d)["summary"]["baseline_gap_child_hours"], 0)
        self.assertEqual(plan(d)["summary"]["review_queue_count"], 1)

    def test_fail_closed_on_solver_limit(self):
        with self.assertRaisesRegex(InputError, "state limit"):
            plan(fixture(), max_states=1)

    def test_working_from_home_is_not_assumed(self):
        d = fixture()
        for a in d["adults"]:
            a["pto_budget"] = 0
        self.assertEqual(plan(d)["summary"]["remaining_gap_child_hours"], 12)

    def test_conflicting_school_events_rejected(self):
        d = fixture()
        d["events"].append(dict(d["events"][1], id="conflicting-event"))
        with self.assertRaisesRegex(InputError, "Conflicting"):
            validate(d)

    def test_invalid_dates_time_and_approval_rejected(self):
        for change in (lambda d:d.update(start="2026-02-30"),
                       lambda d:d["adults"][0].update(work_hours=["09:15","17:00"]),
                       lambda d:d["events"][0].update(reviewed_by=None),
                       lambda d:d["children"][0].update(baseline_confirmed=False),
                       lambda d:d["adults"][0].update(pto_budget=True)):
            d=fixture(); change(d)
            with self.assertRaises(InputError):
                validate(d)

    def test_no_input_mutation(self):
        d=fixture(); before=copy.deepcopy(d)
        plan(d)
        self.assertEqual(d, before)
        self.assertEqual(fingerprint(d), fingerprint(before))


class RevisionTests(unittest.TestCase):
    def test_event_change_impact(self):
        result = compare(fixture(1), fixture(2))
        self.assertEqual(result["calendar_only_added_gap_child_hours"], 10)
        self.assertEqual(len(result["event_changes"]), 3)
        patch_html = render_patch(plan(fixture()), result)
        self.assertIn("dismiss at 13:00", patch_html)
        self.assertIn("dismiss at 15:00", patch_html)
        self.assertNotIn("Possible closure", patch_html)

    def test_household_edits_are_not_misattributed_to_calendar(self):
        old=fixture(1); new=copy.deepcopy(old); new["revision"]=2
        new["adults"][1]["off_dates"]=[]
        result=compare(old,new)
        self.assertTrue(result["household_settings_changed"])
        self.assertEqual(result["calendar_only_added_gap_child_hours"],0)
        self.assertEqual(result["total_added_gap_child_hours"],8)

    def test_removed_published_entry_creates_correction(self):
        old=fixture(1); new=copy.deepcopy(old); new["revision"]=2
        new["events"] = []
        text=render_patch(plan(new),compare(old,new))
        self.assertIn("Remove the old entry",text)

    def test_source_only_update_does_not_require_reprint(self):
        old=fixture(1); new=copy.deepcopy(old); new["revision"]=2
        new["events"][0]["evidence"]="Updated evidence excerpt for the same event."
        text=render_patch(plan(new),compare(old,new))
        self.assertIn("No reviewed changes",text)

    def test_unrelated_or_unordered_snapshots_rejected(self):
        for change in (lambda d:d.update(household_id="other"), lambda d:d.update(revision=1), lambda d:d.update(end="2026-10-24")):
            d=fixture(2); change(d)
            with self.assertRaises(InputError): compare(fixture(1),d)


class ExportTests(unittest.TestCase):
    def test_only_confirmed_source_events_exported(self):
        value=calendar_ics(plan(fixture()))
        self.assertEqual(value.count("BEGIN:VEVENT"),3)
        self.assertNotIn("Possible closure",value)
        self.assertNotIn("parent-a",value)
        self.assertIn("DTEND;VALUE=DATE:20261017",value)
        self.assertIn("SEQUENCE:2",value)

    def test_uid_survives_date_change(self):
        d=fixture(1)
        old=calendar_ics(plan(d))
        d["revision"]=2; d["events"][0]["date"]="2026-10-13"
        new=calendar_ics(plan(d))
        uid=lambda s:[line for line in s.splitlines() if line.startswith("UID:")]
        self.assertEqual(uid(old),uid(new))

    def test_utf8_folding_and_injection_escaping(self):
        d=fixture()
        d["events"][0]["title"]="İ"*120+"\nBEGIN:VEVENT,<script>"
        value=calendar_ics(plan(d))
        self.assertTrue(all(len(line.encode())<=75 for line in value.split("\r\n")))
        self.assertEqual(value.count("\r\nBEGIN:VEVENT\r\n"),3)
        self.assertIn("&lt;script&gt;",render_html(plan(d)))

    def test_reused_bundle_removes_stale_diff_files(self):
        with tempfile.TemporaryDirectory() as folder:
            out=Path(folder); r=plan(fixture())
            write_bundle(r,out,compare(fixture(1),fixture()))
            self.assertTrue((out/"patch.html").exists())
            write_bundle(r,out)
            self.assertFalse((out/"patch.html").exists())

    def test_cli_invalid_input_exits_without_bundle(self):
        with tempfile.TemporaryDirectory() as folder:
            p=Path(folder)/"invalid.json"; p.write_text('{"schema_version":999}')
            with patch('sys.stderr', new=io.StringIO()):
                self.assertEqual(main(["plan",str(p),"--out",str(Path(folder)/"out")]),2)
            self.assertFalse((Path(folder)/"out").exists())


class ClaudeTests(unittest.TestCase):
    text="On October 20, 2026, school will be closed for a teacher planning day."

    def payload(self):
        return {"events":[{"date":"2026-10-20","title":"Teacher planning day","kind":"school_closed","dismissal":None,"evidence":self.text}],"ambiguities":[]}

    def test_transport_request_and_proposed_output(self):
        response={"stop_reason":"tool_use","model":"test-model","content":[{"type":"tool_use","name":"submit_candidates","input":self.payload()}],"usage":{"input_tokens":1}}
        def transport(request,timeout):
            body=json.loads(request.data)
            self.assertTrue(body["tools"][0]["strict"])
            self.assertEqual(timeout,45)
            return io.BytesIO(json.dumps(response).encode())
        result=extract(self.text,"demo-source","test-model",api_key="fake-test-key",transport=transport)
        self.assertTrue(result["review_required"])
        self.assertEqual(result["candidates"][0]["status"],"proposed")
        self.assertIsNone(result["candidates"][0]["reviewed_by"])

    def test_fabricated_quote_rejected(self):
        payload=self.payload(); payload["events"][0]["evidence"]="invented source content"
        with self.assertRaisesRegex(InputError,"exact source quote"):
            validate_candidates(payload,self.text,"source")

    def test_truncated_output_rejected(self):
        transport=lambda *a,**k:io.BytesIO(json.dumps({"stop_reason":"max_tokens","content":[]}).encode())
        with self.assertRaisesRegex(InputError,"complete tool response"):
            extract(self.text,"source","model",api_key="fake",transport=transport)

    def test_errors_never_echo_api_key_or_document(self):
        def transport(*a,**k):
            raise urllib.error.HTTPError("https://api.anthropic.com",401,"unauthorized",{},None)
        with self.assertRaises(InputError) as error:
            extract(self.text,"source","model",api_key="SECRET-TEST",transport=transport)
        self.assertNotIn("SECRET-TEST",str(error.exception))
        self.assertNotIn(self.text,str(error.exception))


if __name__ == '__main__':
    unittest.main()
