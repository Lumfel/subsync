# 🎯 SubSync: Defense Presentation Cue Cards & Demo Guide

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

---

## 🔑 Demo Accounts Cheat Sheet

| Role | Login Email | Password | Lot / Balance | Purpose for Demo |
| :--- | :--- | :--- | :--- | :--- |
| **System Admin** | `admin@subsync.com` | `Admin@12345` | HQ Portal | Approvals, Validation, Backups |
| **Security Officer** | `officer1@subsync.com` | `Officer@123` | Juan dela Cruz | Assigned Cases, Roving Reports |
| **Maintenance Officer**| `officer3@subsync.com` | `Officer@123` | Ricardo Dalisay | Repairs, Fire Hydrant, Work Orders |
| **Resident 1 (Pedro)**| `resident1@subsync.com` | `Resident@123` | Blk 1 Lot 5 (₱500 due) | Primary Demo: Issues, GCash, Chat |
| **Resident 2 (Ana)** | `resident2@subsync.com` | `Resident@123` | Blk 2 Lot 3 (₱0.00 clean) | Compliant Resident: Resolved Noise |
| **Resident 3 (Carlos)**| `resident3@subsync.com` | `Resident@123` | Blk 3 Lot 8 (₱2,500 due) | Non-Compliant: Rejected Payment Note |
| **Pending Resident** | `resident11@subsync.com` | `Resident@123` | Blk 11 Lot 15 | **Live Resident Approval Demo** |
| **Pending Officer** | `officer_pending@subsync.com`| `Officer@123` | Safety Head | **Live Officer Approval Demo** |

---

## ⏱️ 15-Minute Timeline

| Time | Act | Focus | Lead |
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

# 🎬 Live Demonstration Cue Cards

---

### Act 1: Theoretical Framing & Objectives (00:00 - 02:00)
* **Screen:** Show Landing Page (`http://subsync.test/`).
* **Split-Screen Ready:** Left Window (Resident Login), Right Window (Admin Login).

> 🗣️ **Presenter 1 Spiel:**  
> *"Good day esteemed panel members: Dr. Usy, Dr. Yen, and Engr. Talaquines. We present SubSync: An Automated Web-Based Platform for Enhancing Community Engagement in Residential Subdivisions.*  
> *Traditional HOAs rely on paper records, physical bulletin boards, and manual ledgers. SubSync digitalizes subdivision governance around 4 core objectives:*  
> *1. **Communication:** Draft-to-publish notices and formal messaging.*  
> *2. **Issue Reporting:** Geofenced reporting, auto-priority, and audit trail.*  
> *3. **Recommendations:** Civic feedback module.*  
> *4. **Geospatial Mapping:** Leaflet interactive map of facilities and occupancy.*  
> *Allow us to demonstrate the live workflow."*

---

### Act 2: Registration & Multi-Role Approvals (02:00 - 04:00)

* **📱 Left Window [Resident]:**
  1. Point to **Data Privacy Notice (`/privacy`)** — *RA 10173 compliant*.
  2. Click **Register**. Enter: *Juan Resident*, *Blk 3 Lot 14*. Check mandatory **Privacy Consent** box. Submit.
  3. Try logging in immediately: Screen blocks with **"Pending Approval"**.
* **💻 Right Window [Admin]:**
  4. Log into `/admin-access`. Point out top **Pending Registrations badge**.
  5. Go to **Households & Users** ➔ Residents ➔ Find `resident11@subsync.com` (*Grace Alcantara*) ➔ Click **Approve**. Status turns `Active`!
  6. Go to **Officers** ➔ Find `officer_pending@subsync.com` (*Jaime Mercado*) ➔ Click **Approve**.

> 🗣️ **Key Defense Line:**  
> *"Both residents and staff accounts require administrator vetting before entering the system, safeguarding community records."*

---

### Act 3: Interactive Map & Facilities (04:00 - 06:00)

* **💻 Right Window [Admin / Officer]:**
  1. Open **Interactive Map**.
  2. **Geofence Range (S4):** Point out boundary coordinates in `map_settings` (10.6195 to 10.6260 Lat).
  3. **Numbered Pins (S3/S18):** Show active concerns appear as numbered priority pins.
  4. **Facilities (10 Mapped):** Click *Clubhouse*, *Basketball Court*, *Gate 1*, *Pool*, *MRF*.
  5. **Occupancy Status (S10):** Point out lots are categorized strictly as **Occupied** or **Vacant**.

> 🗣️ **Key Defense Line:**  
> *"As requested in Revision S10, the term 'delinquent' was completely removed. Lots are categorized as Occupied or Vacant, and accounts are Active or Non-Compliant."*

---

### Act 4: Issue Reporting & Staff Resolution (06:00 - 08:30)

* **📱 Left Window [Resident - Pedro Reyes `resident1@subsync.com`]:**
  1. Click **Report an Issue**. Select Category: **Infrastructure** ➔ Title: *Broken Street Light*.
  2. **Auto-Priority (S2):** Show priority auto-set to **High** based on safety rules.
  3. **Draggable Pin (S3):** Click inside Block 1. *(Quick drag outside border ➔ show out-of-bounds warning)*. Submit report.
  4. **Add Details Later (S2):** Click on submitted ticket ➔ Click **Add Details / Pictures** ➔ Append: *"Bracket is cracked."*
* **🛡️ Right Window [Officer - Juan dela Cruz `officer1@subsync.com`]:**
  5. Filter by **"Assigned to Me"**: Shows Officer Juan's 3 assigned security tasks.
  6. Open Pedro's streetlight issue.
  7. Change status to **In Progress** ➔ then **Resolved**.
  8. Click **History / Audit Log**: Shows immutable timestamp of who acted.

> 🗣️ **Key Defense Line:**  
> *"Per Revision S2, the resident can append extra photos and notes anytime while keeping the original incident report tamper-proof."*

---

### Act 5: Announcements & WebSocket Push (08:30 - 10:30)

* **🛡️ Right Window [Officer]:**
  1. Go to **Announcements** ➔ Create.
  2. **Template (S5):** Select *"Maintenance Schedule"* quick-fill preset.
  3. **Draft Mode (S13):** Click **Save as Draft**.
  4. *Look at Left Window (Resident):* **Draft is completely hidden!**
  5. In Officer window ➔ Click **Publish**.
* **📱 Left Window [Resident]:**
  6. **Watch screen pop up instantly** via Laravel Reverb (WebSocket) without page reload!
  7. Click notice to read ➔ Clears notification badge automatically.
  8. Go to **Events** ➔ *General Assembly* ➔ Click **"Going"**.

> 🗣️ **Key Defense Line:**  
> *"Revision S13 is fully satisfied: drafts are strictly isolated on the server until published, at which point WebSocket broadcasting notifies residents instantly."*

---

### Act 6: Finance, GCash & CSV Export (10:30 - 12:30)

* **📱 Left Window [Resident]:**
  1. Open **HOA Expenses**: Shows 12 audited expenses (Security ₱25k, Landscaping ₱12k). Click **"View Receipt"** to open PDF receipt.
  2. Check Balance: Pedro owes **₱500.00**.
  3. Click **Pay via GCash**: Shows QR code & pending submission (Ref `GCASH-20260616-9988`).
* **💻 Right Window [Admin]:**
  4. Go to **Payments Queue**.
  5. Find Pedro's submission ➔ Click **Validate Payment**.
  6. *Result:* Pedro's balance on Left turns to **₱0.00** immediately!
  7. Click **Export to CSV (S11)**: Downloads standard RFC-compliant CSV file.

> 🗣️ **Key Defense Line:**  
> *"As requested in Revision S11, heavy spreadsheet dependencies were replaced with streaming RFC-compliant CSV exports."*

---

### Act 7: Governance, Chat & Census (12:30 - 14:00)

* **📱 Left Window [Resident]:**
  1. **Recommendations:** View 10 civic suggestions ➔ Submit: *"Playground Mats"*.
  2. **Messages:** Send inquiry to Officer over formal messaging channel.
  3. **Household Members:** Pedro shows occupants: *Lina (Spouse)* & *Ricky (Child)*.
* **💻 Right Window [Admin]:**
  4. Go to **Household Members**: Shows complete subdivision census of **15 occupants** across all blocks with relationship filters.

> 🗣️ **Key Defense Line:**  
> *"Residents can only manage their own household occupants, protected by strict IDOR authorization, while the administration retains a full community census for emergencies."*

---

### Act 8: Backups & ISO 25010 Summary (14:00 - 15:00)

* **💻 Right Window [Admin]:**
  1. Go to **Settings ➔ System & Security**.
  2. **Automated Backups:** Point to 14-day rotating MySQL snapshots.
  3. Click **"Back Up Now"**: Shows live database dump created in real time.
  4. **Activity Trail:** Review the immutable activity log.

> 🗣️ **Closing Spiel:**  
> *"SubSync was rigorously evaluated using the ISO 25010 software quality standard across functional suitability, reliability, usability, and security. We are now ready for your questions."*

---

## 🛡️ Panel Revisions Quick Match (Cheat Sheet)

* **S2 (Auto-Priority & Details):** Rule-based auto-priority; built `POST /api/issues/{id}/details` to append photos/notes.
* **S3 & S4 (Pin & Bounds):** Boundary stored in `map_settings`; Leaflet draggable pin with live coordinate preview and bounds check.
* **S5 (Templates & Dates):** Predefined announcement templates; native HTML5 datepickers.
* **S6 (Date Filter):** Server-side auto-swap for reversed date ranges (`IssueController::dateRange`).
* **S10 ("Delinquent" Removed):** Households are **Occupied / Vacant**; accounts are **Active / Non-Compliant**.
* **S11 (CSV Standardization):** Replaced SheetJS with streaming RFC-compliant CSV exports.
* **S13 (Draft Handling):** Draft status is server-isolated from resident feeds until explicitly published.
* **D1 (Real-Time Docs):** Laravel Reverb WebSocket private channel architecture with SSE fallback.

---

## ❓ Top 4 Defense Questions & Answers

1. **"Why have two financial ledgers (Personal vs. HOA Expenses)?"**  
   *Answer:* "SubSync intentionally separates individual resident obligations (monthly dues and GCash receipts) from civic transparency. The personal ledger tracks private resident accounts, while the HOA Expenses ledger publicly discloses how association fees are utilized with verifiable PDF receipts."

2. **"How do you prevent Resident A from tampering with Resident B's family members?"**  
   *Answer:* "In `ResidentController::authorizeMember()`, resident queries are strictly scoped to their authenticated `house_id`. Any attempt to read or modify other households returns an immediate `403 Forbidden`."

3. **"How do you prevent session clash when Admin and Resident open tabs on the same computer?"**  
   *Answer:* "SubSync utilizes Laravel multi-guard authentication (`admin`, `officer`, `resident`). Separate session cookies (`subsync_admin_session`, `subsync_resident_session`) are resolved by our custom `ResolvePortalSession` middleware before session start."

4. **"What happens if WebSockets fail or are blocked by carrier firewalls?"**  
   *Answer:* "SubSync uses a dual-layer real-time architecture: it is WebSocket-first using Laravel Reverb for low-latency push, but automatically downgrades to an HTTP-based Server-Sent Events (SSE) stream (`/api/stream/updates`) if WebSocket handshakes fail."
