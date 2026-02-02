# Step-by-Step Demonstration: How Timesheet Generation Works

## Scenario: Generate Timesheet for Employee 2, Feb 1-10, 2026

### PREREQUISITE: Time Tracking Records Must Exist

Let me show you what the database looks like at each stage:

---

## STAGE 1: Empty State (Current Situation)

### Step 1.1: Visit /hr/timesheet
User sees the form:
```
┌─────────────────────────────────────────────────┐
│         GENERATE TIMESHEET                      │
├─────────────────────────────────────────────────┤
│ Employee: [Employee 2 ▼]                       │
│ From: [2026-02-01]                             │
│ To: [2026-02-10]                               │
│ Notes: [Optional comments...]                  │
│                                                 │
│        [Generate Timesheet] [Cancel]            │
└─────────────────────────────────────────────────┘
```

### Step 1.2: Form Submission
```
POST /hr/timesheet
{
  "employee_id": "2",
  "date_from": "2026-02-01",
  "date_to": "2026-02-10",
  "notes": ""
}
```

### Step 1.3: Backend Processing
```
TimesheetController::store()
├─ Validate data ✓
├─ Call buildFromAttendance(2, "", "2026-02-01", "2026-02-10")
│   │
│   ├─ Query: SELECT * FROM time_trackings 
│   │         WHERE employee_id = 2 
│   │         AND date BETWEEN '2026-02-01' AND '2026-02-10'
│   │
│   └─ Database Response: 0 rows
│       (No records exist for employee 2 in Feb 2026)
│
├─ buildFromAttendance() returns NULL
│
└─ Error: "No time & attendance records found in the selected range."
```

### Result:
```
❌ ERROR MESSAGE DISPLAYED
┌─────────────────────────────────────────────────┐
│ No time & attendance records found in the      │
│ selected range.                                 │
└─────────────────────────────────────────────────┘
```

---

## STAGE 2: After Creating Time Tracking Records

### Step 2.1: Populate Time Tracking (via seed route or manual entry)

**Option A: Use Seed Route**
```
GET /hr/timesheet/seed-test/2
→ TimesheetController::seedTestAttendance(2)
→ Creates 10 records
```

**Database inserts:**
```sql
INSERT INTO time_trackings (employee_id, date, time_in, time_out, total_hours, overtime, status) VALUES
(2, '2026-02-01', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-02', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-03', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-04', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-05', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-06', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-07', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-08', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-09', '08:00:00', '17:00:00', 9.00, 1.00, 'Present'),
(2, '2026-02-10', '08:00:00', '17:00:00', 9.00, 1.00, 'Present');
```

**Response:**
```json
{
  "success": true,
  "message": "Test data seeded successfully",
  "employee_id": 2,
  "records_created": 10,
  "date_range": "2026-02-01 to 2026-02-10",
  "details": [
    "Feb 1, 2026 - 9 hours",
    "Feb 2, 2026 - 9 hours",
    ...
  ]
}
```

### Step 2.2: Now Database Contains Data

```sql
SELECT employee_id, date, total_hours, overtime 
FROM time_trackings 
WHERE employee_id = 2 
AND date BETWEEN '2026-02-01' AND '2026-02-10'
ORDER BY date;
```

**Result:**
```
┌─────────────┬────────────┬──────────────┬──────────┐
│ employee_id │ date       │ total_hours  │ overtime │
├─────────────┼────────────┼──────────────┼──────────┤
│ 2           │ 2026-02-01 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-02 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-03 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-04 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-05 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-06 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-07 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-08 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-09 │ 9.00         │ 1.00     │
│ 2           │ 2026-02-10 │ 9.00         │ 1.00     │
└─────────────┴────────────┴──────────────┴──────────┘
```

---

## STAGE 3: Successful Timesheet Generation

### Step 3.1: User Submits Form Again

```
POST /hr/timesheet
{
  "employee_id": "2",
  "date_from": "2026-02-01",
  "date_to": "2026-02-10",
  "notes": ""
}
```

### Step 3.2: Backend Processing (With Data)

```
TimesheetController::store()
├─ Validate data ✓
├─ Call buildFromAttendance(2, "", "2026-02-01", "2026-02-10")
│   │
│   ├─ Query: SELECT * FROM time_trackings 
│   │         WHERE employee_id = 2 
│   │         AND date BETWEEN '2026-02-01' AND '2026-02-10'
│   │
│   ├─ Database Response: 10 rows ✓
│   │
│   ├─ Loop through records:
│   │   totalHours += 9.00 (repeat 10 times) = 90.00
│   │   totalOvertime += 1.00 (repeat 10 times) = 10.00
│   │
│   ├─ Get Employee Position: "Manager"
│   │
│   └─ Return array:
│       {
│         "employee_id": 2,
│         "from_date": "2026-02-01",
│         "to_date": "2026-02-10",
│         "hours_worked": 90.00,
│         "overtime": 10.00,
│         "position": "Manager",
│         "notes": "",
│         "day": null
│       }
│
├─ $data is NOT null ✓
│
├─ Create Timesheet:
│   INSERT INTO timesheets (employee_id, from_date, to_date, hours_worked, 
│                           overtime, position, notes, created_at, updated_at)
│   VALUES (2, '2026-02-01', '2026-02-10', 90.00, 10.00, 'Manager', '', NOW(), NOW())
│
└─ Redirect with success message
```

### Step 3.3: User Sees Success

```
✓ SUCCESS: "Timesheet generated successfully."

Timesheet Records Table:
┌────┬────────────────┬──────────────┬──────────────┬──────────┬────────┬────────────┐
│ #  │ Employee       │ From         │ To           │ Position │ Notes  │ Actions    │
├────┼────────────────┼──────────────┼──────────────┼──────────┼────────┼────────────┤
│ 1  │ Employee 2     │ 2026-02-01   │ 2026-02-10   │ Manager  │        │ Edit View  │
│    │                │              │              │          │        │ Delete     │
└────┴────────────────┴──────────────┴──────────────┴──────────┴────────┴────────────┘
```

---

## STAGE 4: Edit Generated Timesheet

### Step 4.1: User Clicks Edit

```
HTML Button:
<button class="btn btn-warning editTimesheet"
  data-id="1"
  data-employee-id="2"
  data-from="2026-02-01"
  data-to="2026-02-10"
  data-notes="">
  Edit
</button>
```

**JavaScript Handler:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.editTimesheet').forEach(btn => {
    btn.addEventListener('click', function() {
      editTimesheet({
        id: this.dataset.id,
        employee_id: this.dataset.employeeId,
        from_date: this.dataset.from,
        to_date: this.dataset.to,
        notes: this.dataset.notes
      });
    });
  });
});

function editTimesheet(sheet) {
  document.getElementById('timesheet_id').value = sheet.id;
  document.getElementById('employee_id').value = sheet.employee_id;
  document.getElementById('date_from').value = sheet.from_date;
  document.getElementById('date_to').value = sheet.to_date;
  document.getElementById('notes').value = sheet.notes;
  
  // Change form action to PUT endpoint
  document.getElementById('timesheetForm').action = `/hr/timesheet/${sheet.id}`;
  document.getElementById('timesheetForm').method = 'POST'; // HTML form, uses _method=PUT
  
  // Add hidden method field
  let methodInput = document.createElement('input');
  methodInput.type = 'hidden';
  methodInput.name = '_method';
  methodInput.value = 'PUT';
  document.getElementById('timesheetForm').appendChild(methodInput);
  
  // Update button text
  document.getElementById('formSubmitBtn').textContent = 'Update Timesheet';
  document.getElementById('cancelEditBtn').classList.remove('d-none');
}
```

### Step 4.2: Form Now Shows Edit State

```
┌──────────────────────────────────────────────────────┐
│         EDIT TIMESHEET (Pre-filled)                  │
├──────────────────────────────────────────────────────┤
│ Employee: [2 ▼]                                     │
│ From: [2026-02-01]                                  │
│ To: [2026-02-10]                                    │
│ Notes: [Leave blank]                                │
│                                                      │
│        [Update Timesheet] [Cancel]                   │
└──────────────────────────────────────────────────────┘
```

### Step 4.3: User Changes Notes and Submits

```
User types: "Approved by Manager"

Form submits:
PUT /hr/timesheet/1
{
  "_method": "PUT",
  "employee_id": "2",
  "date_from": "2026-02-01",
  "date_to": "2026-02-10",
  "notes": "Approved by Manager"
}
```

### Step 4.4: Backend Updates Record

```
TimesheetController::update()
├─ Validate data ✓
├─ Call buildFromAttendance(2, "Approved by Manager", "2026-02-01", "2026-02-10")
│   (Same process as before - queries and aggregates)
│
├─ $timesheet->update($data)
│   UPDATE timesheets 
│   SET notes = 'Approved by Manager',
│       hours_worked = 90.00,
│       overtime = 10.00
│   WHERE id = 1
│
└─ Redirect with success message
```

### Result:
```
✓ SUCCESS: "Timesheet updated successfully."

Table now shows:
│ 1  │ Employee 2     │ 2026-02-01   │ 2026-02-10   │ Manager  │ Approved │ Edit View  │
```

---

## STAGE 5: View Details

### Step 5.1: User Clicks View

```
AJAX Request:
GET /hr/timesheet/employee/2/details?date_from=2026-02-01&date_to=2026-02-10
```

### Step 5.2: Backend Queries Details

```
TimesheetController::details()
├─ Query TimeTracking records:
│  SELECT * FROM time_trackings
│  WHERE employee_id = 2
│  AND date BETWEEN '2026-02-01' AND '2026-02-10'
│  ORDER BY date ASC
│
├─ Get 10 records ✓
│
├─ Loop through and format:
│  [
│    { date: "2026-02-01", start_time: "08:00", end_time: "17:00", hours_worked: 9.00, overtime: 1.00, status: "Present" },
│    { date: "2026-02-02", start_time: "08:00", end_time: "17:00", hours_worked: 9.00, overtime: 1.00, status: "Present" },
│    ...
│  ]
│
├─ Calculate totals:
│  hours_worked_total: 90.00
│  overtime_total: 10.00
│
└─ Return JSON
```

### Step 5.3: Modal Opens Showing Breakdown

```
┌─────────────────────────────────────────────────────────┐
│ TIMESHEET DETAILS - Employee 2 (Feb 1-10, 2026)        │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ Daily Breakdown:                                        │
│ ┌──────────┬──────────┬──────────┬─────────┬──────────┐ │
│ │ Date     │ In       │ Out      │ Hours   │ Overtime │ │
│ ├──────────┼──────────┼──────────┼─────────┼──────────┤ │
│ │ Feb 01   │ 08:00    │ 17:00    │ 9.00    │ 1.00     │ │
│ │ Feb 02   │ 08:00    │ 17:00    │ 9.00    │ 1.00     │ │
│ │ Feb 03   │ 08:00    │ 17:00    │ 9.00    │ 1.00     │ │
│ │ ...      │          │          │         │          │ │
│ │ Feb 10   │ 08:00    │ 17:00    │ 9.00    │ 1.00     │ │
│ ├──────────┴──────────┴──────────┼─────────┼──────────┤ │
│ │ TOTALS:                        │ 90.00   │ 10.00    │ │
│ └────────────────────────────────┴─────────┴──────────┘ │
│                                                         │
│                                    [Close]             │
└─────────────────────────────────────────────────────────┘
```

---

## STAGE 6: Delete Timesheet

### Step 6.1: User Clicks Delete

```
Confirmation: "Delete this record?"
User confirms: Yes
```

### Step 6.2: Backend Deletes

```
TimesheetController::destroy()
├─ Find record: Timesheet::find(1)
├─ Delete: DELETE FROM timesheets WHERE id = 1
└─ Redirect with success
```

### Result:
```
✓ SUCCESS: "Timesheet record deleted."

Table is now empty:
│ No timesheet records found for the selected filters │
```

---

## COMPLETE DATA FLOW SUMMARY

```
┌──────────────────────────────┐
│  TIME TRACKING RECORDS       │
│  (time_trackings table)      │
│  10 rows for employee 2      │
│  Feb 1-10, 2026             │
│  9 hours/day + 1 overtime    │
└──────────┬───────────────────┘
           │
           │ buildFromAttendance()
           │ SUM(total_hours) = 90
           │ SUM(overtime) = 10
           │
           ▼
┌──────────────────────────────┐
│  TIMESHEET RECORD            │
│  (timesheets table)          │
│  1 row aggregating period    │
│  hours_worked: 90.00         │
│  overtime: 10.00             │
│  from_date: 2026-02-01       │
│  to_date: 2026-02-10         │
└──────────┬───────────────────┘
           │
           │ Can be edited/viewed/deleted
           │
           ▼
┌──────────────────────────────┐
│  REPORTS & PAYROLL           │
│  (future use)                │
│  PDF download                │
│  Payroll processing          │
│  Analytics                   │
└──────────────────────────────┘
```

---

## The Key Insight

**Timesheet Generation = Data Aggregation**

It's NOT a calculation. It's a **roll-up** of existing data:

```
Time Tracking (daily): 10 records × 9 hours each = 90 hours total
                                ↓
                        Timesheet (period): 1 record with 90 hours

                       (Nothing magical happens)
```

If the time tracking records don't exist → no data to aggregate → generation fails.

**That's it.** That's the entire system.
