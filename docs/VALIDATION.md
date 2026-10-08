# Prototype validation

Validated locally on October 8, 2026 with Python 3.14.8. **25 tests passed.** A GitHub Actions template for Python 3.11 and 3.13 is included in `ci/github-actions.yml`. It is not enabled; publishing active workflows requires an additional permission that the current GitHub login does not have.

## Synthetic demo

Comparing printed edition v1 with reviewed edition v2:

- October 16 dismissal changes from 15:00 to 13:00.
- October 20 adds a confirmed school closure.
- An unverified October 23 candidate stays in the review queue.
- Initial uncovered care demand grows from **2 to 12 child-hours**: **10 additional child-hours** attributable to school-event changes.
- With one leave day per adult, blackout dates, and unavailable intervals, the engine suggests parent A on October 16 and parent B on October 20. This can cover all 12 child-hours if the leave is approved.
- The paper correction page contains two strips. The proposed October 23 record is excluded.

See [the correction strips](demo/patch.html), [care plan](demo/index.html), [machine-readable impact](demo/changes.json), and [ICS file](demo/calendar.ics). These are generated synthetic examples, not customer outcomes.

## Checks

Tests cover global optimization against exhaustive enumeration on 20 small randomized scenarios, caregiver capacity, blackout/unavailable intervals, overlapping external care, proposed-event exclusion, conflicting source events, invalid dates, solver limits, event/settings impact attribution, cancellations/removals, stable ICS UIDs, byte-safe UTF-8 folding, HTML/ICS escaping, stale output cleanup, and Claude adapter transport/error handling.

Claude extraction was tested with mocked responses. A live model-listing check returned HTTP 401; **no live extraction success, accuracy score, or production usage is claimed**. A valid API credential and a reviewed document evaluation set are needed next.
