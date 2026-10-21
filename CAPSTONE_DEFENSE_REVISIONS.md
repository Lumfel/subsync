# SubSync — Capstone Defense: Panel Revisions (transcribed & triaged)

**Source:** Photo of the signed "Minutes of the Capstone Defense (Title, Proposal, and Final)" — BS Information Technology.
**Project:** SUBSYNC — Proponents: Barbas, Guanzon, Salazar, Tibo-oc.
**Approved by:** Jayrelle Usy, PhD (Member) · Giovanni Yen, PhD (Member) · Mark Jarus Talaquines, MIT (Chairman).

> ⚠️ **No code changes were made for this log** (per request). This is a transcription + triage only.
> Handwriting is partly ambiguous — uncertain readings are marked *(?)* and should be confirmed with the panel.

---

## A. Documentation column

| # | Written | Interpretation | Current state | Recommendation |
|---|---|---|---|---|
| D1 | *(circled)* **Realtime tracking** | The documentation must cover the realtime feature | Implemented (SSE + Reverb/WebSocket; fetch-based SSE fallback for ngrok) but **not written up** | Add a "Realtime updates" section to Chapter 3/4 documentation: architecture (SSE bus + observers), fallback, and where it's used |
| D2 | **Exclude [payment]** *(?)* (red underline) | Either "exclude payments from the documentation" **or** "include payment" — ambiguous | Payments exist in the system (GCash submit → admin validation → ledger/receipts), beyond the stated objectives 1.1–1.4 | **Confirm with panel.** If "exclude": drop payment from the write-up/scope diagrams; if "include": add payments as an explicit scope item + ERD/data dictionary entries |

---

## B. System Revision column

| # | Written | Interpretation | Current state | Recommendation |
|---|---|---|---|---|
| S1 | **Change UI** | General UI improvement pass | Largely done: simpler nav + terminology (Issues/Documents/Households), wider sidebar, left-aligned nav, contrast/scrollbar fixes, cleaner fonts | Fold remaining UI polish into the P2 items; capture before/after screenshots for the defense |
| S2 | **Priority level (autoset) based on issue details (auto)** + **resident can add details including pictures** | Auto-priority from the issue text/details, and let the resident add more details (incl. photos) | Auto-priority ✅ exists (`IssueReport::autoSetPriority` on create, by category). Photos on submit ✅. **Resident cannot add details/photos after submitting** (no self-edit route) | Add "append details/photo to my open issue" — `POST /api/issues/{id}/details` (owner, status ≠ Resolved) + a resident UI action |
| S3 | **Map Pin / Accurate** | Map pins must be accurate | Households/facilities/issues store lat/lng; residents can drop a pin on submit; admin can drag households; map bounded by `MapSetting` | Improve accuracy: confirm-your-pin step, snap/validate to subdivision bounds, and display the pin the resident set on their own issue |
| S4 | **Add database range of subdivision** | Store the subdivision's area/boundary in the DB | `map_settings` holds `min_lat/max_lat/min_lng/max_lng` (defaults in code) | Seed/configure the actual Terra Nova bounds and document the `map_settings` table; expose bounds editing in admin (already editable) |
| S5 | **Pre-templated [Notice] / Announcements, use date picker** *(?)* | Use pre-made templates for notices/announcements, and a date picker for dates | `announcement_templates` exist + admin "template buttons" in the create modal; announcement date is `<input type="date">` (native picker) | Expose announcement templates in the **officer** portal too; consider a nicer date-picker and default template prompts |
| S6 | **Fixed Date from–to [filter] (improved)** *(?)* | Improve the date range (from–to) filters | Admin issues + reports have From/To dates (`issueDateFrom/To`, `reportDateFrom/To`); reports support date ranges | Validate from ≤ to, default ranges, and apply consistently across lists/exports |
| S7 | **Revise text** | Proofread wording | Nav/terminology already standardised; some copy/capitalisation inconsistencies remain | Run a text/copy audit across all three portals (labels, empty states, errors) |
| S8 | **Check document (subdivision project)** *(?)* | Subdivision project documents / document checking | Officer **files** upload exists (flat); no folders/permissions/versioning | Covered by P2 "document library upgrade"; add a "Subdivision project documents" category |
| S9 | **Validation** | Strengthen input validation | Good coverage (roles, ownership, required fields, numeric IDs, CSV sanitisation); some free-form statuses remain | Tighten remaining free-form fields (e.g., HOA expense/policy status) and date/range validation |
| S10 | **Change "delinquent"** | Replace the word "delinquent" | ✅ Already done — "delinquent" no longer exists in code; it became "Non-Compliant", and **household status is now Occupied/Vacant** | Nothing to do; cite the change in documentation |
| S11 | **Excel (should be CSV) Payment** | Payment-related files/export should be CSV, not Excel | Payments export = CSV ✅. But officer "Send Excel File to Admin" + issue documents accept `.xlsx/.xls` (no server-side Excel parsing; there's a dead SheetJS `<script>` left in admin) | Standardise on CSV for payment proofs/uploads; remove the unused SheetJS include; update upload hints/copy |
| S12 | **Transparency** | Keep/expand financial transparency | ✅ HOA expenses + policies + ledger/aging + payments transparency summary visible to residents | Document it as a feature and add it to the evaluation script; consider a dedicated "Transparency" resident section |
| S13 | **Announcement Status** | Announcement status handling | Announcements have `status` (Active/Archived), a status filter, and an `ArchiveStaleAnnouncements` command | Surface status more clearly (badges), consider **Draft/Scheduled**, and expose archive/restore consistently |

---

## Priority triage (my read)

**High (panel explicitly asked, small effort):**
- S2 — resident can add details/photos after submitting (the auto-priority half is already done).
- S10 — "delinquent" → already resolved.
- S11 — payment/export should be CSV; drop Excel-specific bits.
- S5 — announcement templates available to officers + date picker polish.

**Medium:**
- S6 — from–to date filters validated/consistent.
- S13 — announcement status clarity (~Draft/Scheduled).
- S3 — map pin accuracy improvements.

**Documentation-only:**
- D1 — write up realtime tracking.
- D2 — confirm "exclude/include payment".
- S1/S7/S12 — capture UI changes, revise copy, document transparency.

**Needs panel confirmation (ambiguous handwriting):**
- D2 (exclude/include payment), S5 ("We templated…"), S6 wording, S8 ("Check document").

---

## Note
This file records the panel's feedback and a triage only. **No application code was modified.** Say the word and I'll implement the High items first (S2, S11, S5) with tests.
