# Build plan

## Implemented

Reviewed JSON snapshots → stable-ID event comparison → exact care/PTO planning → printable calendar and correction strips → JSON and basic all-day ICS exports.

The optional Claude adapter sends bounded document text to the first-party Messages API. It requests strict tool output, checks dates/times and exact evidence excerpts, and emits proposed candidates. Exact quote matching establishes that text is present; it does not prove the model interpreted the quote correctly. Review is always required. Candidate IDs must be reconciled by a reviewer into stable canonical IDs before comparing editions.

The optimizer minimizes uncovered child-hours, then total person-days of leave, subject to individual budgets, workdays, off dates, blackout dates, explicit caregiver capacity, and unavailable intervals. It uses a bounded dynamic program and aborts if it exceeds 25,000 states. It does not silently substitute an approximate answer.

## Boundaries

Inputs use one IANA household timezone, local same-day intervals on a 30-minute grid, and an inclusive horizon of up to 93 days. Caregivers are interchangeable except for capacity and availability; no custody, travel, child-specific permissions, or overnight shifts are modeled. School baseline schedules must be explicitly confirmed. Country holidays are never inferred. Simultaneous children count separately in child-hour totals.

Confirmed school changes enter ICS; proposed dates and suggested leave do not. ICS entries are all-day informational markers with dismissal time in the description, stable UID, and revision sequence. Importing a file does not establish ongoing sync; cancellation messages, subscription feeds, recurrence, and timed-event export remain future work. Revision impact separates school-event changes from simultaneous household-setting changes.

Generated HTML escapes input and makes no network requests. Private files and outputs are ignored by Git. No production site files, credentials, server config, or real family data are included in this repository.

## Four-week validation plan

1. Interview 10 paper-calendar households; ask about their last real schedule change. Test correction strips against simple digital reminders.
2. Pilot manually reviewed editions with 20 consenting households and one school or parent group. Measure correction usage and unresolved ambiguity, not just downloads.
3. Add PDF/image input, reviewer reconciliation, and a privacy-preserving edition registry. A future QR token should identify a public edition only; private care plans require household access control. Never put child identities or schedules in a QR payload.
4. Test $29/year pricing. Continue only if at least 8 of 20 households use a correction and at least 5 agree to pay. These are proposed gates, not achieved results.

## Existing-site integration

Introduce a small “Keep this calendar updated” action after printing on DreamCalendars. Build the edition registry and Claude ingestion as a separate backend, then let the PHP site reference published edition IDs. The product page and synthetic browser demo are published through GitHub Pages at reprint.dreamcalendars.com. The Python CLI and document extraction adapter remain separate; there is no hosted upload or AI endpoint. Before a live pilot, add consent, source permissions, reviewer authentication, retention/deletion controls, and rate/cost limits.
