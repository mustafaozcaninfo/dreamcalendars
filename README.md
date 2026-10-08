# DreamCalendars Reprint

### Keep the paper calendar. Update the decision.

DreamCalendars Reprint is a calendar planning product that helps families keep printed calendars useful when school schedules change. It compares a reviewed update with the edition a family printed, creates a small correction strip for the changed entry, and explains the effect on childcare and leave planning.

Built as a new product direction for [DreamCalendars.com](https://www.dreamcalendars.com/), Reprint connects the familiarity of a fridge calendar with the ability to track changing information.

## The problem

A parent prints a school calendar and plans around it. Later, a school notice changes a pickup time or adds a closure. The notice arrives digitally, while the calendar on the fridge still shows the old information.

Reprint asks a specific question: **what changed since you printed this calendar, and what does your family need to reconsider?**

## A simple example

| Printed edition | Reviewed update | Reprint output |
| --- | --- | --- |
| October 16: dismissal at 3 pm | Dismissal moves to 1 pm | A correction strip, two additional child-hours of care demand, and a leave suggestion within the household's budget. |

Print the correction, cut along the border, and attach it to the original calendar. Keep the notes and plans already written on the page.

## What works today

- **Edition comparison:** identify added, updated, and removed school events using stable event IDs.
- **Printable corrections:** generate strips showing the old entry, new information, source reference, and care impact.
- **Household planning:** calculate care gaps and suggest leave within individual budgets, work schedules, blackout dates, and caregiver availability.
- **Review controls:** uncertain events stay in a review queue and are excluded from planning and calendar export.
- **Exports:** produce printable HTML, JSON reports, and basic all-day `.ics` calendar files.
- **Optional Claude adapter:** extract proposed closure and dismissal events from document text, with exact evidence quotes for human review.

**Status:** working local prototype with synthetic examples and **25 passing tests**. Claude transport is tested with mocked responses; authenticated live extraction is not yet verified. PDF/image ingestion, QR edition lookup, automatic school monitoring, and notifications are planned.

## Live demo

Visit [reprint.dreamcalendars.com](https://reprint.dreamcalendars.com/) to explore the product and try the synthetic care-planning demo. The public demo runs entirely in the browser; it does not upload documents or call an AI service.

## Run it

Python **3.11+**. The offline demo needs no third-party dependencies and no API key.

```sh
git clone https://github.com/mustafaozcaninfo/dreamcalendars.git
cd dreamcalendars

python3 -m dreamreprint.cli plan examples/edition-v1.json --out build/v1
python3 -m dreamreprint.cli diff examples/edition-v1.json examples/edition-v2.json --out build/v2
```

Open `build/v2/patch.html` for correction strips or `build/v2/index.html` for the care plan. Use the browser's print option to print or save as PDF. The same folder contains `plan.json`, `changes.json`, and `calendar.ics`.

The included example adds **10 child-hours** of care demand between editions. The planner suggests one leave day for each adult; the unverified event remains excluded. See [validation results](docs/VALIDATION.md) and [pre-generated demo files](docs/demo).

## Where Claude fits

Claude's intended role is to understand inconsistent school notices and propose evidence-backed schedule changes. A reviewer confirms dates and matches event IDs before the deterministic engine compares editions and calculates care impact. Model output never automatically becomes an approved event or leave booking.

To try the text extraction adapter, set `ANTHROPIC_API_KEY` in your environment and choose a model available in your Claude Console:

```sh
python3 -m dreamreprint.cli extract examples/school-notice.txt \
  --source-id demo-notice --model claude-sonnet-4-6 \
  --out private/candidates.json
```

This optional command sends the document text to Anthropic and uses your API account. Extracted records remain `proposed` and require review.

## Product direction

Start with households that already use printable school calendars. Test whether corrections are more useful than another reminder, then validate a household subscription. A future school publishing tool could distribute a single reviewed update to subscribed calendar editions.

Our proposed distinction is **edition-specific paper corrections with source evidence and household impact**. AI calendar import, family organizers, and QR calendars already exist. The combined workflow is a product hypothesis, not a claim of proven worldwide uniqueness.

## Documentation

| Document | Purpose |
| --- | --- |
| [Concept](docs/CONCEPT.md) | Problem, target user, and business hypothesis |
| [Research](docs/RESEARCH.md) | Reddit problem signals and competing products |
| [Build plan](docs/BUILD.md) | Architecture, limitations, and next steps |
| [Validation](docs/VALIDATION.md) | Tests and synthetic demo results |

## Development

```sh
python3 -m unittest discover -s tests -v
python3 scripts/dc-git-secret-check.py
```

`dreamreprint/` contains the planner, Claude adapter, exports, and command-line interface. `examples/` contains synthetic inputs; `tests/` contains the test suite. An optional GitHub Actions template is available in `ci/github-actions.yml`; it is not enabled.

This repository contains the Reprint project. The existing DreamCalendars production website is managed separately.
