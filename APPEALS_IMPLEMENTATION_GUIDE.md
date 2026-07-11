# Disciplinary Appeals System - Implementation Guide

## Overview
Adds complete appeal workflow: clubs submit appeals → committee reviews → decision tracked

## Files Created

### 1. **appeal_cases.sql** — Database Schema
```sql
CREATE TABLE appeal_cases (
  appeal_id INT PRIMARY KEY AUTO_INCREMENT,
  discipline_case_id INT,
  team_id INT,
  appeal_reason TEXT,
  appeal_date TIMESTAMP,
  status ENUM('pending', 'approved', 'rejected'),
  decision_date DATETIME,
  decision_reason TEXT,
  hearing_date DATETIME,
  hearing_notes TEXT
)
```

**Installation:**
```bash
mysql -u root -p fa_db < appeal_cases.sql
```

---

## 2. **teams_appeals.php** — Club Dashboard

**Path:** `/teams/appeals.php`

**Features:**
- View open sanctions ready for appeal
- Submit appeal with grounds/reasoning
- Track all submitted appeals (pending/approved/rejected)
- Status badges & timestamps

**Access:**
- Login as club official
- URL: `http://localhost/teams/appeals.php`

**Key Sections:**

| Tab | Shows |
|---|---|
| Open Sanctions | All active sanctions against club |
| My Appeals | History of submitted appeals |

---

## 3. **committee_appeals.php** — Committee Review Panel

**Path:** `/fa_user/appeals.php`

**Features:**
- Queue of pending appeals (sorted by date)
- Full appeal context (offense, sanction, grounds)
- Modal form to approve/reject
- Decision reasoning (mandatory)
- Optional hearing scheduling

**Access:**
- Login as FA Admin/Committee member
- URL: `http://localhost/fa_user/appeals.php`

**Workflow:**
1. Committee reviews appeal grounds
2. Selects APPROVE (overturn) or REJECT (uphold)
3. Writes binding decision reason
4. Optional: records hearing date/notes
5. System auto-updates original case to 'overturned' if approved

---

## Integration Steps

### Step 1: Create Database Table
```bash
cd /path/to/smart-football
mysql -u root -p fa_db < appeal_cases.sql
```

### Step 2: Add Navigation Links

**In `/teams/header.php`, add:**
```php
<li class="nav-item">
  <a class="nav-link" href="appeals.php">
    <i class="fas fa-gavel"></i> Appeals
  </a>
</li>
```

**In `/fa_user/header.php`, add:**
```php
<li class="nav-item">
  <a class="nav-link" href="appeals.php">
    <i class="fas fa-gavel"></i> Manage Appeals
  </a>
</li>
```

### Step 3: Copy Files
```bash
cp teams_appeals.php /path/to/teams/appeals.php
cp committee_appeals.php /path/to/fa_user/appeals.php
```

### Step 4: Link to Notifications

**Update /app/database.php** to include function:
```php
function recordAppealDecision($appeal_id, $decision, $pdo) {
    $stmt = $pdo->prepare("
        SELECT team_id, appeal_id FROM appeal_cases WHERE appeal_id = ?
    ");
    $stmt->execute([$appeal_id]);
    $appeal = $stmt->fetch();
    
    // Send email notification to club
    // TODO: Connect to PHPMailer
}
```

---

## Key Features

✅ **Club Features:**
- Submit appeals with detailed reasoning
- Track appeal status in real-time
- View committee decisions & reasoning
- Appeal history with timestamps

✅ **Committee Features:**
- Review pending appeals in FIFO order
- Approve (overturn) or reject sanctions
- Record binding decision reasoning
- Schedule and log appeal hearings
- Auto-update case status on approval

✅ **Database Tracking:**
- Full audit trail of all appeals
- Linked to original discipline cases
- Committee decision records
- Hearing documentation

---

## API Endpoints (For Future SMS/Email Integration)

```php
// Get pending appeals count
GET /api/appeals/count

// Submit appeal (club)
POST /api/appeals/submit
  {case_id, appeal_reason}

// Get appeal details (committee)
GET /api/appeals/{appeal_id}

// Record decision (committee)
POST /api/appeals/{appeal_id}/decide
  {decision, decision_reason, hearing_date}
```

---

## Testing Checklist

- [ ] Database table created successfully
- [ ] Club can submit appeal from open case
- [ ] Committee sees pending appeal in queue
- [ ] Committee can approve/reject appeal
- [ ] Original case marked 'overturned' when approved
- [ ] Decision reason recorded
- [ ] Appeal status updates in club dashboard
- [ ] Date/time tracking accurate

---

## Next Steps

1. **Email Notifications** — Send decisions via PHPMailer
2. **SMS Alerts** — Notify clubs of appeal decisions
3. **Appeal Deadlines** — Auto-expire appeals after 7 days
4. **Hearing Scheduler** — Calendar integration for hearings
5. **Document Upload** — Clubs attach evidence files
6. **AI Advisory** — Claude API rates appeal likelihood

---

## Troubleshooting

**Issue:** Appeal form not showing
- Check `appeal_cases` table exists: `SHOW TABLES;`
- Verify club_id in session: `print_r($_SESSION);`

**Issue:** Committee sees no appeals
- Check appeals status = 'pending': `SELECT * FROM appeal_cases WHERE status='pending';`
- Verify committee_id in session

**Issue:** Decision not saving
- Check database constraints (foreign keys)
- Verify decision value in ['approved', 'rejected']

---

## Files Summary

| File | Purpose | Access |
|---|---|---|
| appeal_cases.sql | Database schema | MySQL |
| teams_appeals.php | Club submission UI | Club login |
| committee_appeals.php | Committee review panel | FA Admin |
