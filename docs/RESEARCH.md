# Evidence and competitive check

Reviewed October 8, 2026. Reddit posts are anecdotal, self-selected problem signals, not proof of a market or willingness to pay. No posters were contacted and no personal information was collected.

## What people described

| Source | Observed problem | Product implication |
|---|---|---|
| [r/productivity: seven household calendars](https://www.reddit.com/r/productivity/comments/1fgebuv/) | One partner prefers paper while the other prefers digital; coordinating both is difficult. | Preserve paper rather than forcing everyone into a new app. |
| [r/NotMyJob: school calendar printed without useful colors](https://www.reddit.com/r/NotMyJob/comments/160plwn/) | A printed calendar depends on color to convey important information. | Corrections must use explicit text and work in monochrome. |
| [r/daddit: missed school event](https://www.reddit.com/r/daddit/comments/1p1d3lt/) | Important information gets lost among frequent school communications. | Show the change and required decision, not another undifferentiated notification. |
| [r/workingmoms: extra school days off](https://www.reddit.com/r/workingmoms/comments/wutz06/) | Unexpected closures create childcare planning difficulty. | Quantify the care gap created by a schedule update. |
| [r/workingmoms: confidential work calendar](https://www.reddit.com/r/workingmoms/comments/1qbughx/) | Employer restrictions prevent work-calendar export. | Support manually entered availability without requiring work-account access. |

## What already exists

| Primary source | Existing capability | Consequence for our thesis |
|---|---|---|
| [Ohai](https://www.ohai.ai/how-it-works/) | Processes household documents and schedules. | Document-to-calendar extraction is already a category. |
| [Skylight Sidekick](https://skylight.zendesk.com/hc/en-us/articles/39335273393947-Skylight-Sidekick) | Creates events from photos, PDFs, and forwarded emails. | AI calendar import is not our unique claim. |
| [Calendaw](https://www.calendaw.com/) | Links printed school calendars to live digital information through QR codes. | A QR-equipped school calendar is already available. |
| [Cozi](https://www.cozi.com/feature-overview/) | Shared family calendar and household coordination. | A general family organizer would have strong established competition. |
| [GitHub: Leave-Me-Alone](https://github.com/ngweimeng/leave-me-alone) | Household-aware PTO optimization. | Multi-person leave optimization is not a novelty claim either. |

Our inference: **correction strips tied to a previously printed edition, with evidence and household-specific care impact**, may be a useful underserved workflow. The reviewed pages did not demonstrate this complete workflow. That is a limited observation, not evidence that it has never been built. Validate it through product trials and broader competitive searches.

## Technical references

[RFC 5545](https://datatracker.ietf.org/doc/html/rfc5545) informs our basic all-day ICS export. [icalendar](https://github.com/collective/icalendar) is a potential future parser; it is not a current dependency. The [Claude Messages API](https://platform.claude.com/docs/en/api/messages/create) and [strict tool use](https://platform.claude.com/docs/en/agents-and-tools/tool-use/strict-tool-use) inform the optional extraction adapter. No third-party repository code was copied.
