# Claude for Startups application pack

Prepared in English on **October 8, 2026**. These are reusable drafts, not a submitted application or a reproduction of private Console form fields.

## What are you building?

DreamCalendars Reprint helps families keep printed calendars useful when school schedules change. Most calendar assistants add events to a screen. Reprint compares a reviewed update with the edition a family printed and creates a small correction strip showing what changed and what the family needs to reconsider.

For example, if an early dismissal moves from 3 pm to 1 pm, Reprint can identify the additional childcare demand, suggest a feasible leave plan, and produce a patch for the original calendar. Families can keep using the paper calendar they prefer.

We are developing this as an extension of DreamCalendars.com, an existing printable-calendar website. Our initial target is parents of elementary-school children. We plan to test a household subscription and later explore school publishing tools.

We have built an offline prototype for edition comparison, printable correction strips, source evidence, and care planning. We also implemented an optional Claude API adapter that extracts proposed closure and dismissal events from document text. Its output requires human review; the integration has transport tests but has not yet passed a live authenticated API test. We have not validated customer demand, pricing, or retention, and are not claiming startup revenue or institutional funding.

## How would Anthropic help?

We would use Claude API credits to test schedule extraction from school notices, PDFs, and revised calendars. We want to measure date accuracy, evidence quality, ambiguous-event abstention, and cost per reviewed correction. Applied AI office hours would help us design reliable extraction and evaluation workflows. Claude Team would support development and product research.

Our deterministic engine handles revision comparison and care calculations. Claude's intended role is understanding inconsistent source documents and proposing evidence-backed changes for review. We want families to receive useful corrections without silently trusting uncertain dates.

## Before submitting

Use the actual legal company name, founding date, and domain-matching company email. These facts were not supplied or verified. Identify existing Anthropic benefits accurately; do not present the older DreamCalendars site's age as a new company's founding date. Check the final eligibility and benefit terms in Console.

The [current program page](https://claude.com/programs/startups) says the program accepts bootstrapped startups, requires a Console account and a company email matching the website domain, and covers startups founded in the last five years **or** funded in the last two. Approved companies can receive $1,000 API credits; the Team offer requires being new to Team. These are program benefits, not an equity investment. See the [official terms](https://www.anthropic.com/startup-program-official-terms) and [application](https://platform.claude.com/offers/startups-application).
