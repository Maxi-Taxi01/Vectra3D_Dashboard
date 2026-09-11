# Dashboard validation

Checked 9 September 2026 against the supplied documents and in isolated Chromium browser sessions.

- Task status survives reload; text/week/status filters work.
- Deliverable status and evidence references persist.
- Aggregate recruitment counts are saved and exported.
- Hour entry, removal and undo work; graduation/business time is kept separate.
- JSON backup exports current editable data; restoration preserves it.
- Invalid JSON backup structure is rejected before replacement.
- Calendar export contains the baseline final-materials date and provisional labels.
- All nine routes fit 1440px desktop and 390px mobile widths; wider data tables scroll inside their containers.
- No JavaScript page errors during the functional checks.
- Source proposal planning table and agreement signature page inspected visually. Company/student signatures are visible; THUAS signature is not visible in its field.
- Original/source-copy hashes recorded in source_manifest.json.

Limitations: tested in Chromium only. No cloud sync, encryption, access-control enforcement or automated reminders are provided. File storage is managed by the browser; export backups regularly. The public project webpage and actual graduation research/design remain future student-owned deliverables.

## Targeted optimisation — 10 September 2026
Applied Impeccable hardening guidance to administrative reliability without adding plugins or changing the research/design scope.

Verified in isolated Chromium contexts:
- Edited final-submission task deadline flows to milestones and calendar; original source date remains visible.
- Evidence saves immediately and missing-reference counts update.
- Pre-restore saved data can be exported after restoration.
- A changed stored version is detected before saving, even without a storage event; another version is not overwritten.
- Unsaved local edits export successfully during a save conflict.
- Malformed existing saved data remains untouched and shows a persistent recovery warning.
- The original regression suite still passes: task persistence/filtering, deliverables, counts, log removal/undo, JSON restoration/rejection, calendar export and all nine routes at desktop/mobile widths.
- Updated milestone and deliverable layouts inspected in desktop/mobile screenshots. No user browser state or raw research data was used in tests.

Final bounded review: all three reported issues resolved. Targeted checks verified immediate edited-deadline display, keyboard focus after task status changes, and accurate warnings when browser storage fails. Final reviewer disposition: ship for those fixes. Source-copy hashes rechecked; shipped seed contains no test progress.
