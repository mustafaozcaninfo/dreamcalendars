import argparse
import json
import sys
from pathlib import Path
from .engine import InputError, compare, fingerprint, plan, validate
from .export import write_bundle
from .claude import extract


def load(path):
    if path.stat().st_size > 2_000_000:
        raise InputError("Input exceeds the 2 MB prototype limit")
    return validate(json.loads(path.read_text(encoding="utf-8")))


def main(argv=None):
    parser = argparse.ArgumentParser(description="DreamCalendars Reprint — local school-change care planner")
    commands = parser.add_subparsers(dest="command", required=True)
    p = commands.add_parser("plan", help="Plan reviewed JSON input and export JSON/ICS/printable HTML")
    p.add_argument("input", type=Path)
    p.add_argument("--out", type=Path, required=True)
    p = commands.add_parser("extract", help="Use Claude to extract candidate dates from a text document; human review required")
    p.add_argument("document", type=Path)
    p.add_argument("--source-id", required=True)
    p.add_argument("--model", required=True)
    p.add_argument("--out", type=Path, required=True)
    p = commands.add_parser("diff", help="Compare revisions and show care impact")
    p.add_argument("old", type=Path)
    p.add_argument("new", type=Path)
    p.add_argument("--out", type=Path, required=True)
    try:
        if argv is None:
            argv = sys.argv[1:]
        args = parser.parse_args(argv)
        if args.command == "extract":
            if args.document.resolve() == args.out.resolve():
                raise InputError("Candidate output would overwrite the source document")
            if args.document.stat().st_size > 120000:
                raise InputError("Document exceeds 120,000 bytes")
            candidates = extract(args.document.read_text(encoding="utf-8"), args.source_id, args.model)
            args.out.parent.mkdir(parents=True, exist_ok=True)
            args.out.write_text(json.dumps(candidates, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
            print(json.dumps({"output": str(args.out.resolve()), "candidates": len(candidates["candidates"]), "review_required": True}))
            return 0
        data = load(args.input if args.command == "plan" else args.new)
        old = load(args.old) if args.command == "diff" else None
        # Avoid overwriting an input file through a predictable export filename.
        targets = {args.out.resolve() / name for name in ("plan.json", "calendar.ics", "index.html", "changes.json", "patch.html")}
        inputs = [args.input] if args.command == "plan" else [args.old, args.new]
        if any(v.resolve() in targets for v in inputs):
            raise InputError("Output directory would overwrite an input file")
        report = plan(data)
        report["input_sha256"] = fingerprint(data)
        impact = compare(old, data) if old is not None else None
        write_bundle(report, args.out, impact)
        print(json.dumps({"output": str(args.out.resolve()), "summary": report["summary"]}, ensure_ascii=False, indent=2))
        return 0
    except (InputError, KeyError, TypeError, AttributeError, OSError, UnicodeError, json.JSONDecodeError) as exc:
        print(f"Input/export error: {exc}", file=sys.stderr)
        return 2


if __name__ == "__main__":
    raise SystemExit(main())
