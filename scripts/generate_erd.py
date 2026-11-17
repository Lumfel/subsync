import os
import resvg_py

def build_erd_svg():
    width = 2560
    height = 1420

    # Colors & styling (matching the academic ERD format of the user's image)
    BG_COLOR = "#FFFFFF"
    TABLE_BORDER = "#475569"
    HEADER_BG = "#E2E8F0"
    HEADER_TEXT = "#0F172A"
    ROW_BG = "#FFFFFF"
    ROW_LINE = "#CBD5E1"
    COL_LINE = "#E2E8F0"
    TYPE_TEXT = "#64748B"
    NAME_TEXT = "#1E293B"
    PK_TEXT = "#0369A1"
    FK_TEXT = "#475569"
    LINE_COLOR = "#334155"
    LINE_WIDTH = 1.4

    ROW_H = 25
    HEADER_H = 32
    TYPE_W = 62
    NAME_W = 148
    KEY_W = 35
    TABLE_W = TYPE_W + NAME_W + KEY_W  # 245px

    tables = {
        "HOUSEHOLD": {
            "x": 440, "y": 170,
            "fields": [
                ("int", "House_ID", "PK"),
                ("string", "Block_Lot_Number", ""),
                ("string", "Status", ""),
                ("decimal", "Latitude", ""),
                ("decimal", "Longitude", ""),
                ("datetime", "Created_At", ""),
                ("datetime", "Updated_At", "")
            ]
        },
        "RESIDENT": {
            "x": 340, "y": 480,
            "fields": [
                ("int", "Resident_ID", "PK"),
                ("int", "House_ID", "FK"),
                ("string", "Name", ""),
                ("string", "Email", ""),
                ("string", "Password", ""),
                ("string", "Contact_Number", ""),
                ("string", "Status", ""),
                ("decimal", "Current_Balance", ""),
                ("datetime", "Created_At", ""),
                ("datetime", "Updated_At", "")
            ]
        },
        "HOUSEHOLD_MEMBER": {
            "x": 720, "y": 500,
            "fields": [
                ("int", "Member_ID", "PK"),
                ("int", "House_ID", "FK"),
                ("string", "Name", ""),
                ("string", "Relationship", ""),
                ("string", "Contact_Number", ""),
                ("datetime", "Created_At", "")
            ]
        },
        "ANNOUNCEMENT_VIEW": {
            "x": 940, "y": 40,
            "fields": [
                ("int", "View_ID", "PK"),
                ("int", "Resident_ID", "FK"),
                ("int", "Announcement_ID", "FK"),
                ("datetime", "Viewed_At", "")
            ]
        },
        "ANNOUNCEMENT": {
            "x": 1380, "y": 40,
            "fields": [
                ("int", "Announcement_ID", "PK"),
                ("int", "Admin_ID", "FK"),
                ("int", "Officer_ID", "FK"),
                ("string", "Title", ""),
                ("text", "Content", ""),
                ("datetime", "Created_At", ""),
                ("datetime", "Updated_At", "")
            ]
        },
        "ADMIN": {
            "x": 1060, "y": 390,
            "fields": [
                ("int", "Admin_ID", "PK"),
                ("string", "Name", ""),
                ("string", "Email", ""),
                ("string", "Password", ""),
                ("string", "Contact_Number", ""),
                ("string", "Status", ""),
                ("datetime", "Created_At", ""),
                ("datetime", "Updated_At", "")
            ]
        },
        "OFFICER": {
            "x": 1460, "y": 400,
            "fields": [
                ("int", "Officer_ID", "PK"),
                ("int", "Admin_ID", "FK"),
                ("string", "Name", ""),
                ("string", "Email", ""),
                ("string", "Password", ""),
                ("string", "Contact_Number", ""),
                ("string", "Status", ""),
                ("string", "Role_Position", ""),
                ("datetime", "Created_At", "")
            ]
        },
        "MEDIA": {
            "x": 1880, "y": 60,
            "fields": [
                ("int", "Media_ID", "PK"),
                ("int", "Admin_ID", "FK"),
                ("int", "Officer_ID", "FK"),
                ("int", "Resident_ID", "FK"),
                ("int", "Uploader_ID", ""),
                ("string", "Uploader_Type", ""),
                ("string", "Entity_Type", ""),
                ("int", "Entity_ID", ""),
                ("string", "File_Name", ""),
                ("string", "File_Path", ""),
                ("datetime", "Created_At", "")
            ]
        },
        "CONVERSATION": {
            "x": 2220, "y": 430,
            "fields": [
                ("int", "Conversation_ID", "PK"),
                ("string", "Title", ""),
                ("datetime", "Created_At", "")
            ]
        },
        "FACILITY": {
            "x": 2220, "y": 660,
            "fields": [
                ("int", "Facility_ID", "PK"),
                ("int", "Admin_ID", "FK"),
                ("string", "Name", ""),
                ("string", "Description", ""),
                ("decimal", "Latitude", ""),
                ("decimal", "Longitude", ""),
                ("string", "Status", ""),
                ("datetime", "Created_At", "")
            ]
        },
        "ISSUE_REPORT": {
            "x": 60, "y": 960,
            "fields": [
                ("int", "Issue_ID", "PK"),
                ("int", "Resident_ID", "FK"),
                ("string", "Category", ""),
                ("string", "Title", ""),
                ("text", "Description", ""),
                ("decimal", "Latitude", ""),
                ("decimal", "Longitude", ""),
                ("string", "Status", ""),
                ("datetime", "Created_At", ""),
                ("datetime", "Updated_At", "")
            ]
        },
        "RECOMMENDATION": {
            "x": 420, "y": 1020,
            "fields": [
                ("int", "Recommendation_ID", "PK"),
                ("int", "Resident_ID", "FK"),
                ("string", "Title", ""),
                ("text", "Description", ""),
                ("string", "Status", ""),
                ("datetime", "Created_At", "")
            ]
        },
        "FINANCIAL_RECORD": {
            "x": 770, "y": 980,
            "fields": [
                ("int", "Record_ID", "PK"),
                ("int", "Resident_ID", "FK"),
                ("int", "Admin_ID", "FK"),
                ("string", "Record_Type", ""),
                ("string", "Description", ""),
                ("decimal", "Amount", ""),
                ("datetime", "Record_Date", ""),
                ("datetime", "Created_At", "")
            ]
        },
        "ISSUE_RESPONSE": {
            "x": 1170, "y": 980,
            "fields": [
                ("int", "Response_ID", "PK"),
                ("int", "Issue_ID", "FK"),
                ("int", "Officer_ID", "FK"),
                ("int", "Admin_ID", "FK"),
                ("string", "Responder_Type", ""),
                ("text", "Response_Content", ""),
                ("datetime", "Created_At", "")
            ]
        },
        "MESSAGE": {
            "x": 1680, "y": 960,
            "fields": [
                ("int", "Message_ID", "PK"),
                ("int", "Conversation_ID", "FK"),
                ("int", "Admin_ID", "FK"),
                ("int", "Officer_ID", "FK"),
                ("int", "Resident_ID", "FK"),
                ("string", "Sender_Type", ""),
                ("text", "Content", ""),
                ("datetime", "Created_At", "")
            ]
        }
    }

    # Precalculate table height & row positions
    for tname, tinfo in tables.items():
        tinfo["h"] = HEADER_H + len(tinfo["fields"]) * ROW_H
        tinfo["w"] = TABLE_W
        tinfo["row_y"] = []
        for i in range(len(tinfo["fields"])):
            tinfo["row_y"].append(tinfo["y"] + HEADER_H + i * ROW_H)

    # Helper to draw crow's foot (many side) and double bar (one side)
    def render_one_marker(x, y, orientation):
        # orientation: 'left', 'right', 'up', 'down' (direction of line leaving table)
        lines = []
        bar_len = 6
        if orientation in ('left', 'right'):
            dx = -1 if orientation == 'left' else 1
            x1 = x + dx * 4
            x2 = x + dx * 8
            lines.append(f'<line x1="{x1}" y1="{y - bar_len}" x2="{x1}" y2="{y + bar_len}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x2}" y1="{y - bar_len}" x2="{x2}" y2="{y + bar_len}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
        else:
            dy = -1 if orientation == 'up' else 1
            y1 = y + dy * 4
            y2 = y + dy * 8
            lines.append(f'<line x1="{x - bar_len}" y1="{y1}" x2="{x + bar_len}" y2="{y1}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x - bar_len}" y1="{y2}" x2="{x + bar_len}" y2="{y2}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
        return "".join(lines)

    def render_many_marker(x, y, orientation):
        # orientation: entering target table ('left', 'right', 'up', 'down')
        # e.g., if entering target from right (moving leftward into table right edge): orientation='right'
        lines = []
        spread = 6
        depth = 9
        if orientation == 'left': # enters left edge of table (coming from left to right)
            # tip is at (x, y), prongs reach back to x - depth
            x_back = x - depth
            lines.append(f'<line x1="{x}" y1="{y}" x2="{x_back}" y2="{y - spread}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x}" y1="{y}" x2="{x_back}" y2="{y + spread}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x - depth - 2}" y1="{y - spread}" x2="{x - depth - 2}" y2="{y + spread}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
        elif orientation == 'right': # enters right edge of table (coming from right to left)
            x_back = x + depth
            lines.append(f'<line x1="{x}" y1="{y}" x2="{x_back}" y2="{y - spread}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x}" y1="{y}" x2="{x_back}" y2="{y + spread}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x + depth + 2}" y1="{y - spread}" x2="{x + depth + 2}" y2="{y + spread}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
        elif orientation == 'up': # enters top edge of table (coming from top to bottom)
            y_back = y - depth
            lines.append(f'<line x1="{x}" y1="{y}" x2="{x - spread}" y2="{y_back}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x}" y1="{y}" x2="{x + spread}" y2="{y_back}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x - spread}" y1="{y - depth - 2}" x2="{x + spread}" y2="{y - depth - 2}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
        elif orientation == 'down': # enters bottom edge of table (coming from bottom to top)
            y_back = y + depth
            lines.append(f'<line x1="{x}" y1="{y}" x2="{x - spread}" y2="{y_back}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x}" y1="{y}" x2="{x + spread}" y2="{y_back}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
            lines.append(f'<line x1="{x - spread}" y1="{y + depth + 2}" x2="{x + spread}" y2="{y + depth + 2}" stroke="{LINE_COLOR}" stroke-width="{LINE_WIDTH}"/>')
        return "".join(lines)

    # Build SVG content
    svg = []
    svg.append(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {width} {height}" width="{width}" height="{height}">')
    svg.append('<defs>')
    svg.append('<style>')
    svg.append("""
        text { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        .tbl-title { font-size: 13px; font-weight: 700; letter-spacing: 0.5px; fill: #0F172A; }
        .type-txt { font-size: 11px; fill: #64748B; }
        .name-txt { font-size: 11.5px; font-weight: 500; fill: #1E293B; }
        .pk-txt { font-size: 10.5px; font-weight: 700; fill: #0284C7; }
        .fk-txt { font-size: 10.5px; font-weight: 600; fill: #475569; }
        .erd-line { fill: none; stroke: #334155; stroke-width: 1.4px; stroke-linejoin: round; stroke-linecap: round; }
    """)
    svg.append('</style>')
    svg.append('</defs>')

    # Background
    svg.append(f'<rect width="{width}" height="{height}" fill="{BG_COLOR}"/>')

    # Draw diagram title & legend
    svg.append(f'<text x="60" y="55" font-size="22" font-weight="800" fill="#0F172A">SubSync — Entity Relationship Diagram (ERD)</text>')
    svg.append(f'<text x="60" y="80" font-size="13" fill="#64748B">Core System Database Architecture • Independent Attribute Connection Ports (Unbundled Relations)</text>')

    # Legend at top right
    svg.append(f'<g transform="translate(1920, 20)">')
    svg.append(f'<rect width="400" height="32" rx="4" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1"/>')
    svg.append(f'<line x1="20" y1="16" x2="60" y2="16" stroke="{LINE_COLOR}" stroke-width="1.4"/>')
    svg.append(render_one_marker(20, 16, 'right'))
    svg.append(f'<text x="70" y="20" font-size="11" font-weight="600" fill="#334155">One (1)</text>')
    svg.append(f'<line x1="140" y1="16" x2="180" y2="16" stroke="{LINE_COLOR}" stroke-width="1.4"/>')
    svg.append(render_many_marker(180, 16, 'left'))
    svg.append(f'<text x="190" y="20" font-size="11" font-weight="600" fill="#334155">Many (N)</text>')
    svg.append(f'<rect x="270" y="8" width="40" height="16" rx="2" fill="#E0F2FE"/>')
    svg.append(f'<text x="290" y="20" font-size="10" font-weight="700" fill="#0284C7" text-anchor="middle">PK</text>')
    svg.append(f'<rect x="325" y="8" width="40" height="16" rx="2" fill="#F1F5F9"/>')
    svg.append(f'<text x="345" y="20" font-size="10" font-weight="700" fill="#475569" text-anchor="middle">FK</text>')
    svg.append('</g>')

    # -------------------------------------------------------------
    # 22 INDEPENDENT CONNECTIONS
    # Each connection has its own start anchor, separate line, and end anchor.
    # Connections sharing the same ID row block are placed side-by-side
    # along the block edge (never merged into a single arrow or shared trunk).
    # -------------------------------------------------------------

    connections = []

    # Helper for path string
    def make_path(points):
        return "M " + " L ".join(f"{pt[0]},{pt[1]}" for pt in points)

    # 1. HOUSEHOLD.House_ID (row 0) -> RESIDENT.House_ID (row 1)
    # Exits HOUSEHOLD right edge at y = 202 + 6 = 208
    p1 = [(tables["HOUSEHOLD"]["x"] + TABLE_W, 208),
          (tables["HOUSEHOLD"]["x"] + TABLE_W + 18, 208),
          (tables["HOUSEHOLD"]["x"] + TABLE_W + 18, 430),
          (310, 430),
          (310, tables["RESIDENT"]["row_y"][1] + 12),
          (tables["RESIDENT"]["x"], tables["RESIDENT"]["row_y"][1] + 12)]
    connections.append((p1, 'right', 'left'))

    # 2. HOUSEHOLD.House_ID (row 0) -> HOUSEHOLD_MEMBER.House_ID (row 1)
    # Exits HOUSEHOLD right edge at y = 202 + 16 = 218 (placed next to p1 on the House_ID row block!)
    p2 = [(tables["HOUSEHOLD"]["x"] + TABLE_W, 218),
          (tables["HOUSEHOLD"]["x"] + TABLE_W + 30, 218),
          (tables["HOUSEHOLD"]["x"] + TABLE_W + 30, tables["HOUSEHOLD_MEMBER"]["row_y"][1] + 12),
          (tables["HOUSEHOLD_MEMBER"]["x"], tables["HOUSEHOLD_MEMBER"]["row_y"][1] + 12)]
    connections.append((p2, 'right', 'left'))

    # 3. ADMIN.Admin_ID (row 0) -> OFFICER.Admin_ID (row 1)
    # Admin_ID row: y = 390 + 32 = 422..446.
    # Line a: exits right edge at y = 428
    p3 = [(tables["ADMIN"]["x"] + TABLE_W, 428),
          (1370, 428),
          (1370, tables["OFFICER"]["row_y"][1] + 12),
          (tables["OFFICER"]["x"], tables["OFFICER"]["row_y"][1] + 12)]
    connections.append((p3, 'right', 'left'))

    # 4. ADMIN.Admin_ID (row 0) -> FACILITY.Admin_ID (row 1)
    # Line b: exits right edge at y = 436 (side-by-side with p3 on Admin_ID block!)
    p4 = [(tables["ADMIN"]["x"] + TABLE_W, 436),
          (1390, 436),
          (1390, 370),
          (2180, 370),
          (2180, tables["FACILITY"]["row_y"][1] + 12),
          (tables["FACILITY"]["x"], tables["FACILITY"]["row_y"][1] + 12)]
    connections.append((p4, 'right', 'left'))

    # 5. ADMIN.Admin_ID (row 0) -> MESSAGE.Admin_ID (row 2)
    # Line c: exits right edge at y = 444 (side-by-side with p3 & p4 on Admin_ID block!)
    p5 = [(tables["ADMIN"]["x"] + TABLE_W, 444),
          (1410, 444),
          (1410, 920),
          (1650, 920),
          (1650, tables["MESSAGE"]["row_y"][2] + 12),
          (tables["MESSAGE"]["x"], tables["MESSAGE"]["row_y"][2] + 12)]
    connections.append((p5, 'right', 'left'))

    # 6. ADMIN.Admin_ID (row 0) -> ANNOUNCEMENT.Admin_ID (row 1)
    # Exits top edge of Admin_ID at x = 1130
    p6 = [(1130, tables["ADMIN"]["y"]),
          (1130, 310),
          (1350, 310),
          (1350, tables["ANNOUNCEMENT"]["row_y"][1] + 12),
          (tables["ANNOUNCEMENT"]["x"], tables["ANNOUNCEMENT"]["row_y"][1] + 12)]
    connections.append((p6, 'up', 'left'))

    # 7. ADMIN.Admin_ID (row 0) -> MEDIA.Admin_ID (row 1)
    # Exits top edge of Admin_ID at x = 1180 (side-by-side with p6 on top border!)
    p7 = [(1180, tables["ADMIN"]["y"]),
          (1180, 290),
          (1840, 290),
          (1840, tables["MEDIA"]["row_y"][1] + 12),
          (tables["MEDIA"]["x"], tables["MEDIA"]["row_y"][1] + 12)]
    connections.append((p7, 'up', 'left'))

    # 8. ADMIN.Admin_ID (row 0) -> ISSUE_RESPONSE.Admin_ID (row 3)
    # Exits bottom edge of ADMIN at x = 1120
    p8 = [(1120, tables["ADMIN"]["y"] + tables["ADMIN"]["h"]),
          (1120, 880),
          (1140, 880),
          (1140, tables["ISSUE_RESPONSE"]["row_y"][3] + 12),
          (tables["ISSUE_RESPONSE"]["x"], tables["ISSUE_RESPONSE"]["row_y"][3] + 12)]
    connections.append((p8, 'down', 'left'))

    # 9. ADMIN.Admin_ID (row 0) -> FINANCIAL_RECORD.Admin_ID (row 2)
    # Exits bottom edge of ADMIN at x = 1080 (side-by-side with p8 on bottom border!)
    p9 = [(1080, tables["ADMIN"]["y"] + tables["ADMIN"]["h"]),
          (1080, 895),
          (1040, 895),
          (1040, tables["FINANCIAL_RECORD"]["row_y"][2] + 12),
          (tables["FINANCIAL_RECORD"]["x"] + TABLE_W, tables["FINANCIAL_RECORD"]["row_y"][2] + 12)]
    connections.append((p9, 'down', 'right'))

    # 10. OFFICER.Officer_ID (row 0) -> ANNOUNCEMENT.Officer_ID (row 2)
    # Exits top edge of OFFICER at x = 1520
    p10 = [(1520, tables["OFFICER"]["y"]),
           (1520, 280),
           (1330, 280),
           (1330, tables["ANNOUNCEMENT"]["row_y"][2] + 12),
           (tables["ANNOUNCEMENT"]["x"], tables["ANNOUNCEMENT"]["row_y"][2] + 12)]
    connections.append((p10, 'up', 'left'))

    # 11. OFFICER.Officer_ID (row 0) -> MEDIA.Officer_ID (row 2)
    # Exits right edge of OFFICER at y = 432 + 12 = 444
    p11 = [(tables["OFFICER"]["x"] + TABLE_W, 444),
           (1800, 444),
           (1800, tables["MEDIA"]["row_y"][2] + 12),
           (tables["MEDIA"]["x"], tables["MEDIA"]["row_y"][2] + 12)]
    connections.append((p11, 'right', 'left'))

    # 12. OFFICER.Officer_ID (row 0) -> ISSUE_RESPONSE.Officer_ID (row 2)
    # Exits bottom edge of OFFICER at x = 1490
    p12 = [(1490, tables["OFFICER"]["y"] + tables["OFFICER"]["h"]),
           (1490, 870),
           (1440, 870),
           (1440, tables["ISSUE_RESPONSE"]["row_y"][2] + 12),
           (tables["ISSUE_RESPONSE"]["x"] + TABLE_W, tables["ISSUE_RESPONSE"]["row_y"][2] + 12)]
    connections.append((p12, 'down', 'right'))

    # 13. OFFICER.Officer_ID (row 0) -> MESSAGE.Officer_ID (row 3)
    # Exits bottom edge of OFFICER at x = 1550 (side-by-side with p12 on bottom border!)
    p13 = [(1550, tables["OFFICER"]["y"] + tables["OFFICER"]["h"]),
           (1550, 890),
           (1630, 890),
           (1630, tables["MESSAGE"]["row_y"][3] + 12),
           (tables["MESSAGE"]["x"], tables["MESSAGE"]["row_y"][3] + 12)]
    connections.append((p13, 'down', 'left'))

    # 14. RESIDENT.Resident_ID (row 0) -> ANNOUNCEMENT_VIEW.Resident_ID (row 1)
    # Resident_ID row: y = 480 + 32 = 512..536.
    # Exits left edge of Resident_ID at y = 516
    p14 = [(tables["RESIDENT"]["x"], 516),
           (240, 516),
           (240, 78),
           (910, 78),
           (910, tables["ANNOUNCEMENT_VIEW"]["row_y"][1] + 12),
           (tables["ANNOUNCEMENT_VIEW"]["x"], tables["ANNOUNCEMENT_VIEW"]["row_y"][1] + 12)]
    connections.append((p14, 'left', 'left'))

    # 15. RESIDENT.Resident_ID (row 0) -> ISSUE_REPORT.Resident_ID (row 1)
    # Exits left edge of Resident_ID at y = 524 (side-by-side with p14 on Resident_ID row!)
    p15 = [(tables["RESIDENT"]["x"], 524),
           (260, 524),
           (260, 920),
           (40, 920),
           (40, tables["ISSUE_REPORT"]["row_y"][1] + 12),
           (tables["ISSUE_REPORT"]["x"], tables["ISSUE_REPORT"]["row_y"][1] + 12)]
    connections.append((p15, 'left', 'left'))

    # 16. RESIDENT.Resident_ID (row 0) -> RECOMMENDATION.Resident_ID (row 1)
    # Exits left edge of Resident_ID at y = 532 (side-by-side with p14 & p15 on Resident_ID row!)
    p16 = [(tables["RESIDENT"]["x"], 532),
           (280, 532),
           (280, 890),
           (390, 890),
           (390, tables["RECOMMENDATION"]["row_y"][1] + 12),
           (tables["RECOMMENDATION"]["x"], tables["RECOMMENDATION"]["row_y"][1] + 12)]
    connections.append((p16, 'left', 'left'))

    # 17. RESIDENT.Resident_ID (row 0) -> FINANCIAL_RECORD.Resident_ID (row 1)
    # Exits bottom edge of RESIDENT at x = 480
    p17 = [(480, tables["RESIDENT"]["y"] + tables["RESIDENT"]["h"]),
           (480, 860),
           (740, 860),
           (740, tables["FINANCIAL_RECORD"]["row_y"][1] + 12),
           (tables["FINANCIAL_RECORD"]["x"], tables["FINANCIAL_RECORD"]["row_y"][1] + 12)]
    connections.append((p17, 'down', 'left'))

    # 18. RESIDENT.Resident_ID (row 0) -> MEDIA.Resident_ID (row 3)
    # Exits right edge of Resident_ID at y = 520
    p18 = [(tables["RESIDENT"]["x"] + TABLE_W, 520),
           (615, 520),
           (615, 800),
           (1820, 800),
           (1820, tables["MEDIA"]["row_y"][3] + 12),
           (tables["MEDIA"]["x"], tables["MEDIA"]["row_y"][3] + 12)]
    connections.append((p18, 'right', 'left'))

    # 19. RESIDENT.Resident_ID (row 0) -> MESSAGE.Resident_ID (row 4)
    # Exits bottom edge of RESIDENT at x = 530 (side-by-side with p17 on bottom border!)
    p19 = [(530, tables["RESIDENT"]["y"] + tables["RESIDENT"]["h"]),
           (530, 840),
           (1610, 840),
           (1610, tables["MESSAGE"]["row_y"][4] + 12),
           (tables["MESSAGE"]["x"], tables["MESSAGE"]["row_y"][4] + 12)]
    connections.append((p19, 'down', 'left'))

    # 20. ANNOUNCEMENT.Announcement_ID (row 0) -> ANNOUNCEMENT_VIEW.Announcement_ID (row 2)
    # Exits left of ANNOUNCEMENT at y = 40 + 32 + 12 = 84
    p20 = [(tables["ANNOUNCEMENT"]["x"], 84),
           (1240, 84),
           (1240, tables["ANNOUNCEMENT_VIEW"]["row_y"][2] + 12),
           (tables["ANNOUNCEMENT_VIEW"]["x"] + TABLE_W, tables["ANNOUNCEMENT_VIEW"]["row_y"][2] + 12)]
    connections.append((p20, 'left', 'right'))

    # 21. ISSUE_REPORT.Issue_ID (row 0) -> ISSUE_RESPONSE.Issue_ID (row 1)
    # Exits right of ISSUE_REPORT at y = 960 + 32 + 12 = 1004
    p21 = [(tables["ISSUE_REPORT"]["x"] + TABLE_W, 1004),
           (360, 1004),
           (360, 940),
           (1130, 940),
           (1130, tables["ISSUE_RESPONSE"]["row_y"][1] + 12),
           (tables["ISSUE_RESPONSE"]["x"], tables["ISSUE_RESPONSE"]["row_y"][1] + 12)]
    connections.append((p21, 'right', 'left'))

    # 22. CONVERSATION.Conversation_ID (row 0) -> MESSAGE.Conversation_ID (row 1)
    # Exits left of CONVERSATION at y = 430 + 32 + 12 = 474
    p22 = [(tables["CONVERSATION"]["x"], 474),
           (2140, 474),
           (2140, 905),
           (1960, 905),
           (1960, tables["MESSAGE"]["row_y"][1] + 12),
           (tables["MESSAGE"]["x"] + TABLE_W, tables["MESSAGE"]["row_y"][1] + 12)]
    connections.append((p22, 'left', 'right'))

    # Render lines & markers
    for points, src_orient, dst_orient in connections:
        path_str = make_path(points)
        svg.append(f'<path d="{path_str}" class="erd-line"/>')
        # Render one-side marker at start
        start_pt = points[0]
        svg.append(render_one_marker(start_pt[0], start_pt[1], src_orient))
        # Render many-side marker at end
        end_pt = points[-1]
        svg.append(render_many_marker(end_pt[0], end_pt[1], dst_orient))

    # -------------------------------------------------------------
    # RENDER TABLES
    # -------------------------------------------------------------
    for tname, tinfo in tables.items():
        tx = tinfo["x"]
        ty = tinfo["y"]
        tw = tinfo["w"]
        th = tinfo["h"]

        # Table container group
        svg.append(f'<g id="table_{tname}" transform="translate({tx}, {ty})">')

        # Shadow & Base rectangle
        svg.append(f'<rect x="0" y="0" width="{tw}" height="{th}" rx="3" fill="{ROW_BG}" stroke="{TABLE_BORDER}" stroke-width="1.5"/>')

        # Header rectangle
        svg.append(f'<rect x="0" y="0" width="{tw}" height="{HEADER_H}" rx="3" fill="{HEADER_BG}" stroke="{TABLE_BORDER}" stroke-width="1.5"/>')
        # Crisp square corners at bottom of header
        svg.append(f'<rect x="0" y="{HEADER_H - 3}" width="{tw}" height="3" fill="{HEADER_BG}"/>')
        svg.append(f'<line x1="0" y1="{HEADER_H}" x2="{tw}" y2="{HEADER_H}" stroke="{TABLE_BORDER}" stroke-width="1.5"/>')

        # Header Title
        svg.append(f'<text x="{tw / 2}" y="21" class="tbl-title" text-anchor="middle">{tname}</text>')

        # Rows
        for i, (dtype, dname, dkey) in enumerate(tinfo["fields"]):
            ry = HEADER_H + i * ROW_H

            # Horizontal divider
            if i > 0:
                svg.append(f'<line x1="0" y1="{ry}" x2="{tw}" y2="{ry}" stroke="{ROW_LINE}" stroke-width="1"/>')

            # Vertical dividers between columns
            svg.append(f'<line x1="{TYPE_W}" y1="{ry}" x2="{TYPE_W}" y2="{ry + ROW_H}" stroke="{COL_LINE}" stroke-width="1"/>')
            svg.append(f'<line x1="{TYPE_W + NAME_W}" y1="{ry}" x2="{TYPE_W + NAME_W}" y2="{ry + ROW_H}" stroke="{COL_LINE}" stroke-width="1"/>')

            # Text content
            # Type column
            svg.append(f'<text x="8" y="{ry + 17}" class="type-txt">{dtype}</text>')

            # Name column
            svg.append(f'<text x="{TYPE_W + 8}" y="{ry + 17}" class="name-txt">{dname}</text>')

            # Key column
            if dkey == "PK":
                svg.append(f'<text x="{TYPE_W + NAME_W + KEY_W / 2}" y="{ry + 17}" class="pk-txt" text-anchor="middle">PK</text>')
            elif dkey == "FK":
                svg.append(f'<text x="{TYPE_W + NAME_W + KEY_W / 2}" y="{ry + 17}" class="fk-txt" text-anchor="middle">FK</text>')

        svg.append('</g>')

    svg.append('</svg>')
    return "\n".join(svg)

if __name__ == "__main__":
    svg_data = build_erd_svg()
    
    # Save SVG
    svg_path = "docs/subsync_erd.svg"
    with open(svg_path, "w", encoding="utf-8") as f:
        f.write(svg_data)
    print(f"Saved SVG to {svg_path}")

    # Render PNG using resvg_py
    png_data = resvg_py.svg_to_bytes(svg_data)
    png_path = "docs/subsync_erd.png"
    with open(png_path, "wb") as f:
        f.write(png_data)
    print(f"Saved PNG to {png_path} ({len(png_data)} bytes)")

    # Also copy to brain artifact directory
    artifact_dir = r"C:\Users\user\.gemini\antigravity\brain\a49eb04e-228b-4b2d-b6f6-bff29368aef1"
    artifact_png = os.path.join(artifact_dir, "subsync_erd.png")
    with open(artifact_png, "wb") as f:
        f.write(png_data)
    print(f"Saved PNG to artifact: {artifact_png}")
