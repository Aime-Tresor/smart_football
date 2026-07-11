# Appeal System - Quick Start (10 min setup)

## 1️⃣ Database Setup (2 min)
```bash
mysql -u root -p fa_db < appeals_full_integration.sql
```

## 2️⃣ Copy Files (2 min)
```bash
cp teams_appeals.php /path/to/smart-football/teams/appeals.php
cp committee_appeals.php /path/to/smart-football/fa_user/appeals.php
```

## 3️⃣ Add Menu Links (3 min)

**In `/teams/header.php`** after main nav items:
```php
<li class="nav-item">
  <a class="nav-link" href="appeals.php">
    <i class="fas fa-gavel"></i> Disciplinary Appeals
  </a>
</li>
```

**In `/fa_user/header.php`** after main nav items:
```php
<li class="nav-item">
  <a class="nav-link" href="appeals.php">
    <i class="fas fa-gavel"></i> Appeal Review Board
  </a>
</li>
```

## 4️⃣ Test (3 min)

### As Club Official:
1. Login to teams
2. Click **Disciplinary Appeals**
3. See "Open Sanctions" tab
4. Click **Submit Appeal** button
5. Fill form & submit

### As Committee:
1. Login as FA Admin
2. Click **Appeal Review Board**
3. See pending appeals queue
4. Click **Review & Decide**
5. Choose approve/reject + write reasoning
6. Click **Record Decision**

---

## What's Included

✅ **Club Side:**
- View active sanctions
- Submit appeal with grounds
- Track appeal status (pending/approved/rejected)
- View committee decisions

✅ **Committee Side:**
- Review queue of pending appeals
- Modal decision form
- Approve (overturn) or reject
- Record hearing info
- Auto-update case status

✅ **Database:**
- `appeal_cases` table
- `appeal_history` audit table
- Views for dashboards
- Stored procedures for auto-expire

---

## Database Tables

| Table | Purpose |
|---|---|
| `appeal_cases` | Main appeals storage |
| `appeal_history` | Audit log of status changes |
| `v_pending_appeals` | VIEW: Committee dashboard |
| `v_team_appeals` | VIEW: Club dashboard |

---

## File Mapping

| File | Location | Access |
|---|---|---|
| `teams_appeals.php` | `/teams/appeals.php` | Club officials |
| `committee_appeals.php` | `/fa_user/appeals.php` | FA Admin/Committee |
| `appeals_full_integration.sql` | MySQL import | Database |

---

## Key Columns (appeal_cases)

| Column | Type | Notes |
|---|---|---|
| `appeal_id` | INT | Primary key |
| `discipline_case_id` | INT | Links to original sanction |
| `team_id` | INT | Club appealing |
| `appeal_reason` | TEXT | Grounds for appeal |
| `appeal_date` | TIMESTAMP | When submitted |
| `status` | ENUM | pending/approved/rejected |
| `decision_reason` | TEXT | Committee reasoning |
| `hearing_date` | DATETIME | Optional hearing scheduled |

---

## Common Queries

**Count pending appeals:**
```sql
SELECT COUNT(*) FROM appeal_cases WHERE status='pending';
```

**View club's appeals:**
```sql
SELECT * FROM v_team_appeals WHERE team_id=4;
```

**Get approved appeals (overturned sanctions):**
```sql
SELECT * FROM appeal_cases WHERE status='approved';
```

**Auto-close expired appeals (30+ days):**
```sql
CALL sp_close_expired_appeals();
```

---

## Styling

- Bootstrap 5 cards & modals
- Purple/blue gradient headers
- Badge colors: 
  - 🟡 Pending (yellow)
  - 🟢 Approved (green)
  - 🔴 Rejected (red)

---

## Future Enhancements

1. **Email notifications** — Send decisions via PHPMailer
2. **SMS alerts** — Notify clubs immediately
3. **Document upload** — Clubs attach evidence
4. **Hearing calendar** — Schedule & track hearings
5. **AI advisory** — Claude API rates appeal strength
6. **Appeal deadlines** — Auto-expire after 7 days
7. **PDF export** — Generate decision letters

---

## Support

| Issue | Solution |
|---|---|
| Table doesn't exist | Run `appeals_full_integration.sql` |
| Forms not showing | Check PHP session variables |
| Data not saving | Verify database user has INSERT/UPDATE permissions |
| Views blank | Check `ai_discipline_cases` table exists |

---

## One-Line Install
```bash
mysql -u root -p fa_db < appeals_full_integration.sql && cp teams_appeals.php /teams/ && cp committee_appeals.php /fa_user/
```

**Done! 🎉** Appeals system ready.
