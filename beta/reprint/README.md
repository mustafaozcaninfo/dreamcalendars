# DreamCalendars Reprint

**Keep the paper calendar. Update the decision.**

A Claude-enabled startup prototype from [DreamCalendars](https://www.dreamcalendars.com/): when a school date changes after a calendar is printed, generate a small correction strip showing the old date, the new information, and the household's resulting care gap.

**Example:** an early dismissal moves from 3 pm to 1 pm. Reprint produces a correction for that entry, shows how many child-hours now need covering, and suggests PTO within each adult's budget. The fridge calendar gets a patch instead of a complete replacement.

Stage: working offline proof of concept, plus an optional Claude API extraction adapter. No customers, revenue, institutional funding, or production Claude usage are claimed. All committed examples are synthetic.

## Run the demo

Python 3.11+; the offline prototype has no third-party dependencies. Run from the `beta/reprint` directory in the DreamCalendars repository:

```sh
cd beta/reprint
python3 -m dreamreprint.cli plan examples/edition-v1.json --out build/v1
python3 -m dreamreprint.cli diff examples/edition-v1.json examples/edition-v2.json --out build/v2
python3 -m unittest discover -s tests -v
```

Open `build/v2/patch.html` for correction strips and `build/v2/index.html` for the care plan. Both support browser printing / Save as PDF. The command also exports `plan.json`, `changes.json`, and `calendar.ics`. An optional GitHub Actions template is provided in `ci/github-actions.yml`; it is not active.

## Claude extraction

Set `ANTHROPIC_API_KEY` in your environment and choose an available Claude model ID from your Console. This command sends the document text to Anthropic and uses your API account.

```sh
python3 -m dreamreprint.cli extract examples/school-notice.txt \
  --source-id demo-notice --model claude-sonnet-4-6 \
  --out private/candidates.json
```

The adapter requests schema-constrained tool output, validates exact evidence quotes, and keeps every extracted event `proposed`. A reviewer must assign affected children, match stable event IDs across revisions, and explicitly confirm the record before it enters a plan. PDF/image parsing and automatic event reconciliation are future work. Live API execution was **not verified**: the available credential returned HTTP 401 on a model-listing check. Adapter tests use mocked transport.

## Read next

- [Product thesis](docs/CONCEPT.md)
- [Claude for Startups application drafts](docs/CLAUDE-STARTUPS.md)
- [Research and competing products](docs/RESEARCH.md)
- [Architecture, boundaries, and next steps](docs/BUILD.md)
- [Demo results and validation](docs/VALIDATION.md)

The proposed distinction is **edition-specific paper corrections + source evidence + household impact**. Adjacent products already exist; originality is a hypothesis to validate, not a claim that nobody else has built this. QR edition lookup, school monitoring, and notifications are planned, not shipped.
