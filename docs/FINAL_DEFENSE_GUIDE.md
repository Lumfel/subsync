# SubSync: Master Final Defense Presentation Guide & Demonstration Script

**Project Title:** SubSync: An Automated Web-Based Platform for Enhancing Community Engagement in Residential Subdivisions  
**Institution:** Carlos Hilado Memorial State University (CHMSU) — Alijis Campus, Bacolod City  
**Proponents:** Christian J. Barbas Jr., Jurriel A. Guanzon, Shirley Ann M. Salazar, Paul Felippe G. Tibo-oc  
**Panel:** Dr. Jayrelle Usy, Dr. Giovanni Yen, Engr./Prof. Mark Jarus Talaquines (Chair)

---

## 🖥️ Screen Setup (Dual-Browser Split Screen)

```
┌──────────────────────────────────────────┬──────────────────────────────────────────┐
│          LEFT WINDOW (Chrome)            │      RIGHT WINDOW (Edge / Incognito)     │
│         RESIDENT PORTAL (Pedro)          │          ADMIN / OFFICER PORTAL          │
│        resident1@subsync.com             │           admin@subsync.com              │
│       http://subsync.test/login          │       http://subsync.test/admin-access   │
└──────────────────────────────────────────┴──────────────────────────────────────────┘
```

### Verified Active Credentials
| Role | Portal URL | Login Email | Password | Persona / Role in Demo |
| :--- | :--- | :--- | :--- | :--- |
| **System Admin** | `/admin-access` | `admin@subsync.com` | `Admin@12345` | Overall Administrator, Approvals, Ledger |
| **Security Officer** | `/login` | `officer1@subsync.com` | `Officer@123` | Juan dela Cruz (Security Head, Patrol Reports) |
| **Maintenance Officer**| `/login` | `officer3@subsync.com` | `Officer@123` | Ricardo Dalisay (Repairs, Work Orders) |
| **Active Resident** | `/login` | `resident1@subsync.com` | `Resident@123` | Pedro Reyes (`Blk 1 Lot 5`, ₱500 Balance) |
| **Compliant Resident** | `/login` | `resident2@subsync.com` | `Resident@123` | Ana Garcia (`Blk 2 Lot 3`, ₱0.00 Balance) |
| **Non-Compliant Resident**| `/login` | `resident3@subsync.com` | `Resident@123` | Carlos Bautista (`Blk 3 Lot 8`, ₱2,500 Overdue) |
| **Pending Resident** | `/login` | `resident11@subsync.com` | `Resident@123` | Grace Alcantara (**For Live Approval Demo**) |
| **Pending Officer** | `/login` | `officer_pending@subsync.com`| `Officer@123` | Jaime Mercado (**For Live Officer Approval Demo**) |

---

## ⏱️ 15-Minute Timeline

| Time | Segment | Focus | Lead Presenter |
| :--- | :--- | :--- | :--- |
| **00:00 - 02:00** | Act 1 | Theoretical Framing & 4 Specific Objectives | Presenter 1 |
| **02:00 - 04:00** | Act 2 | RA 10173 Consent, Registration & Multi-Role Approvals | Presenter 1 & 2 |
| **04:00 - 06:00** | Act 3 | Interactive Dynamic Mapping & Subdivision Facilities | Presenter 2 |
| **06:00 - 08:30** | Act 4 | Geofenced Issue Reporting, Auto-Priority & Staff Resolution | Presenter 2 & 3 |
| **08:30 - 10:30** | Act 5 | Announcements Draft-to-Publish & Real-Time Push | Presenter 3 |
| **10:30 - 12:30** | Act 6 | Financial Transparency, GCash Validation & CSV Export | Presenter 3 & 4 |
| **12:30 - 14:00** | Act 7 | Participatory Governance, Formal Messaging & Household Census | Presenter 4 |
| **14:00 - 15:00** | Act 8 | Automated Security, Backups & ISO 25010 Summary | Presenter 4 & Wrap-up |

---

## 🎬 Step-by-Step Demonstration Flow

### Act 1: Introduction & Problem Statement (2 Mins)
* **Opening Spiel:** Present problem of traditional paper blotters, unverified bulletin announcements, and manual ledger tracking.
* **Introduce 4 Objectives:**
  1. Integrated Communication (Draft-to-publish notices, formal messaging)
  2. Issue Reporting (Geofencing, auto-priority, audit trail)
  3. Community Recommendations (Participatory governance)
  4. Dynamic Geospatial Mapping (Leaflet interactive subdivision visualization)

### Act 2: Registration, RA 10173 Consent & Approvals (2 Mins)
* **Landing Page (`/`):** Click the **Privacy Policy (`/privacy`)** link demonstrating compliance with RA 10173 (Data Privacy Act of 2012).
* **Self-Registration:** Register *"Juan Resident"* with Block/Lot and mandatory privacy consent checkbox. State: `Pending`.
* **Login Guard:** Attempt to log in immediately; system denies access with pending approval notice.
* **Admin Approval (Right Window):** Log in as `admin@subsync.com`. Open Residents table, locate `resident11@subsync.com` (*Grace Alcantara*), click **Approve**.
* **Officer Approval Demo:** Open Officers table, locate `officer_pending@subsync.com` (*Jaime Mercado*), click **Approve**.

### Act 3: Dynamic Mapping & Facilities (2 Mins)
* **Geofenced Perimeter (S4):** Show the boundary coordinates stored in `map_settings`.
* **Numbered Issue Pins (S3 / S18):** Active concerns display as numbered pins color-coded by priority.
* **10 Mapped Facilities:** Clubhouse, Basketball Court, Main Entrance Gate, Playground, Swimming Pool, MRF, Tennis Court, North Perimeter Gate, Central Park.
* **Household Status (S10):** Occupied vs. Vacant categorization.

### Act 4: Issue Reporting & Staff Resolution (2.5 Mins)
* **Report Issue (Left Window - Pedro Reyes):** Select Infrastructure, title *"Broken Street Light near Lot 5"*. Show **Auto-Priority** (S2) set to High based on safety criteria.
* **Draggable Pin & Boundary Validation (S3):** Attempt to drag pin outside perimeter; show out-of-bounds warning.
* **Add Post-Submission Details (S2):** Open issue, click **Add Details / Additional Photos**, append note: *"Streetlight bracket cracked."*
* **Staff Dispatch & SLA (Right Window - Officer Juan):** Filter by **"Assigned to Me"** (3 active security cases). Open streetlight issue, update status to **In Progress** -> **Resolved**. Show **History / Audit Log**.

### Act 5: Announcements Draft-to-Publish & Real-Time Push (2 Mins)
* **Predefined Templates (S5):** In Officer Portal, select *"Maintenance Schedule"* template. Use native HTML5 date picker.
* **Draft Status (S13):** Click **Save as Draft**. In resident window, show it is **completely invisible**.
* **Publish & WebSocket Push:** Click **Publish**. Notice appears on resident screen in real time via **Laravel Reverb (WebSocket) / SSE**.
* **Community Events:** Show *Annual General Assembly* and toggle RSVP to **"Going"**.

### Act 6: HOA Financial Transparency, GCash & CSV Export (2 Mins)
* **HOA Expenses Transparency:** Resident views 12 audited expenses (Security ₱25k, Landscaping ₱12k, Utilities). Click **"View Receipt"** to stream PDF receipt.
* **Payment Submission:** Pedro views balance (₱500.00). Shows pending submission Ref `GCASH-20260616-9988`.
* **Admin Validation Queue:** Admin inspects GCash proof, clicks **Validate Payment**. Balance drops to ₱0.00; official receipt timestamp created.
* **CSV Export (S11):** Click **Export to CSV** for streaming export.

### Act 7: Governance, Formal Messaging & Household Census (1.5 Mins)
* **Recommendations (Obj 1.3):** Resident submits *"Rubberized Playground Mats"*; Admin marks **Approved**.
* **Formal Messaging (Obj 1.1):** Direct message between Pedro and Officer over private WebSocket channel without social media clutter.
* **Household Census:** Pedro views occupants (*Lina Reyes [Spouse]* & *Ricky Reyes [Child]*). Admin views **15 household members** with search and relationship filters.

### Act 8: Security, Automated Backups & Wrap-up (1 Min)
* **Automated Backups:** Show daily automated MySQL snapshots (`subsync:backup-database`) with 14-day rotation and click **"Back Up Now"**.
* **Activity Log:** Show append-only audit trail.
* **ISO 25010 Summary:** Hand floor over for panel Q&A.

---

## 🛡️ Panel Revisions Defense Scorecard

| ID | Panel Revision Request | Code Implementation in SubSync |
| :--- | :--- | :--- |
| **S2** | Auto-priority based on details + resident can add details/pictures | Rule-based auto-priority on creation; built `POST /api/issues/{id}/details` for appending notes/photos without tampering with original records. |
| **S3 & S4** | Accurate map pin + Database subdivision area range | `map_settings` table stores boundary coordinates; draggable Leaflet pin with real-time coordinate preview and geofence rejection. |
| **S5** | Pre-templated Announcements + Date Picker | Predefined templates (*Notice, Urgent, Maintenance*); all dates enforce native HTML5 date pickers. |
| **S6** | Fixed Date From–To filters | Server-side date normalization (`IssueController::dateRange`) with client-side auto-swap for reversed ranges. |
| **S10** | Replace the term "Delinquent" | Completely replaced: households are **Occupied / Vacant**; accounts are **Active / Non-Compliant**. |
| **S11** | Standardize on CSV instead of Excel | SheetJS dependencies removed; replaced with RFC-compliant streaming CSV exports. |
| **S13** | Announcement Draft handling | Added **Draft vs. Active** status. Drafts are strictly isolated from resident feeds until explicitly published. |
| **D1** | Real-time tracking documentation | Architectural write-up of Laravel Reverb WebSocket private channel with automatic Server-Sent Events (SSE) fallback. |

---

## ❓ Anticipated Panel Q&A

**Q: "Why are there two separate financial views (Personal Balance vs. HOA Expenses)?"**  
*Answer:* "SubSync intentionally separates individual resident obligations (monthly dues and GCash validation receipts) from community fiscal transparency. The HOA Expenses ledger publicly discloses how association fees are utilized (security payroll, pool chemicals, street lighting) with verifiable PDF receipts, satisfying civic transparency."

**Q: "How does the system protect against Insecure Direct Object Reference (IDOR) on household members?"**  
*Answer:* "In `ResidentController::authorizeMember()`, resident requests are strictly scoped to their authenticated `house_id`. Any attempt by a resident to read, modify, or delete family members belonging to another household returns an immediate 403 Forbidden."

**Q: "How do you avoid session collisions when Admin and Resident open tabs on the same computer?"**  
*Answer:* "SubSync employs Laravel multi-guard authentication (`admin`, `officer`, and `resident` guards). Custom session cookies (`subsync_admin_session`, `subsync_resident_session`) are resolved by our global `ResolvePortalSession` middleware before session initialization, preventing privilege escalation or accidental session overwrite."

**Q: "What happens if WebSockets fail or are blocked by carrier firewalls?"**  
*Answer:* "SubSync uses a dual-layer real-time architecture: it is WebSocket-first using Laravel Reverb for low-overhead push, but automatically downgrades to an HTTP-based Server-Sent Events (SSE) stream (`/api/stream/updates`) if WebSocket handshakes fail."
