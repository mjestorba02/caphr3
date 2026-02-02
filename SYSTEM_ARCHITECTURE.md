# HR System Architecture & Timesheet Generation Flow

## System Overview

Your HR system is built on **Laravel 11** with a modular structure. The core modules are:

1. **Time Tracking** - Records daily attendance (check-in/check-out)
2. **Timesheet** - Aggregates attendance into summaries for payroll
3. **Shift Management** - Defines employee work schedules
4. **Leave Management** - Manages employee time-off
5. **Overtime** - Tracks and approves additional work hours
6. **Claims** - Handles employee expense claims

---

## Data Architecture

### The Data Flow Chain

```
[Time Tracking Entry] → [Timesheet Generation] → [Reports/Payroll]
   (Daily logs)         (Period aggregation)      (Analysis)
```

### Key Tables & Models

| Model | Table | Purpose |
|-------|-------|---------|
| `User` | `users` | Employee records (id, name, email, position, department) |
| `TimeTracking` | `time_trackings` | Daily attendance logs (employee_id, date, time_in, time_out, total_hours, overtime, undertime, status) |
| `Timesheet` | `timesheets` | Period summaries (employee_id, from_date, to_date, hours_worked, overtime, notes) |
| `Shift` | `shifts` | Employee schedules (employee_id, shift_library_id, days, start_time, end_time) |
| `LeaveType` | `leave_types` | Types of leave (name, description) |
| `EmployeeLeave` | `employee_leaves` | Employee leave balances (employee_id, leave_type_id, credits, balance) |
| `OvertimeRequest` | `overtime_requests` | Overtime approval requests |

---

## TIMESHEET GENERATION PROCESS (DETAILED)

### Step 1: User Submits Form
**Location:** `resources/views/hr/timesheet.blade.php` (lines 60-88)

```
Form submits → POST /hr/timesheet
Fields:
- employee_id (required)
- date_from (required)
- date_to (required)
- notes (optional)
```

### Step 2: Controller Validation
**Location:** `app/Http/Controllers/TimesheetController.php::store()` (lines 47-76)

```php
$request->validate([
    'employee_id' => 'required|exists:users,id',
    'date_from'   => 'required|date',
    'date_to'     => 'required|date|after_or_equal:date_from',
    'notes'       => 'nullable|string',
]);
```

✅ **Validation checks:**
- Employee exists in users table
- Dates are valid and from_date ≤ to_date
- Notes are optional

❌ **If validation fails:** Returns with error message, form shows validation errors

### Step 3: Build Timesheet from Attendance
**Location:** `TimesheetController::buildFromAttendance()` (lines 78-117)

This is the **CRITICAL FUNCTION** that aggregates time tracking data.

```php
// 1. Query attendance records for the date range
$attendance = TimeTracking::where('employee_id', $employeeId)
    ->whereBetween('date', [$dateFrom, $dateTo])
    ->get();

// 2. If NO records found → Return NULL (ERROR)
if ($attendance->isEmpty()) {
    return null; // ← TRIGGERS ERROR IN STORE()
}

// 3. Get employee data (for position)
$employee = User::find($employeeId);

// 4. Calculate totals
$totalHours = 0;
$totalOvertime = 0;

foreach ($attendance as $log) {
    $hours = (float) ($log->total_hours ?? 0);
    $totalHours += $hours;
    
    if ($hours > 8) {
        $totalOvertime += ($hours - 8);
    }
}

// 5. Return formatted array
return [
    'employee_id'  => $employeeId,
    'from_date'    => $dateFrom,
    'to_date'      => $dateTo,
    'hours_worked' => round($totalHours, 2),
    'overtime'     => round($totalOvertime, 2),
    'position'     => $employee->position,
    'notes'        => $notes,
    'day'          => null,
];
```

**Key Logic:**
- Sums `total_hours` from each TimeTracking record
- Calculates overtime as `hours - 8` if daily hours exceed 8
- Returns NULL if no attendance records exist

### Step 4: Create Timesheet Record
**Location:** `TimesheetController::store()` (lines 67-76)

```php
if (!$data) {
    return back()->withErrors([
        'date_from' => 'No time & attendance records found in the selected range.'
    ]);
}

Timesheet::create($data); // ← Creates DB record with aggregated data
```

### Step 5: Display Success
- Redirects to timesheet list view
- Shows "Timesheet generated successfully" message
- New timesheet appears in table with from_date, to_date, hours_worked, overtime

---

## DEPENDENCY MAPPING

### What Timesheet Generation Depends On:

```
timesheets (generated)
    ↓ depends on ↓
time_trackings (must have records in date range)
    ↓ depends on ↓
users (employee must exist)
    ↓ depends on ↓
shifts (OPTIONAL - only used if you want to check status like "Late")
```

### What Uses Timesheet Data:

```
timesheets (source)
    ↓ used by ↓
Timesheet::details() → for viewing attendance breakdown
Timesheet::report() → for analytics by position
Timesheet::download() → for PDF payroll documents
```

---

## THE PROBLEM: Why Your Timesheet Generation Fails

### Root Cause: No Time Tracking Records Exist

When you try to generate a timesheet:

```
Employee: 2
From: 2026-02-01
To: 2026-02-10
```

The system queries:
```sql
SELECT * FROM time_trackings 
WHERE employee_id = 2 
  AND date BETWEEN '2026-02-01' AND '2026-02-10'
```

**Result:** 0 rows returned (because no one created those time tracking records)

**Therefore:** `buildFromAttendance()` returns `null` → Error displayed

### Current Data in Your System:

```
time_trackings table has:
- 6 records
- ALL with employee_id = 1
- ALL with dates from 2025-10-08 to 2025-10-18
- No records for employee 2 or Feb 2026
```

---

## HOW TO CREATE TIME TRACKING RECORDS

### Option 1: Via UI (Manual Entry)
1. Go to **HR → Time Tracking**
2. Click "Add Time Tracking Record"
3. Fill in:
   - Employee: Select
   - Date: 2026-02-01
   - Time In: 08:00
   - Time Out: 17:00
4. Submit
5. System calculates:
   - total_hours = 9.0
   - overtime = 1.0 (if > 8 hrs)
   - status = Present (or Late based on shift)

**Location:** `TimeTrackingController::store()` (lines 20-84)

### Option 2: Via Seed Route (Quick Testing)
1. Visit: `http://yourapp/hr/timesheet/seed-test/2`
2. Creates 10 records automatically for employee 2, Feb 1-10, 2026
3. Each record: 8:00 AM - 5:00 PM (9 hours, 1 hour overtime)

**Location:** `TimesheetController::seedTestAttendance()` (added recently)

### Option 3: Via Database Migration/Seeder
Create a database seeder to populate test data during development.

---

## COMPLETE WORKFLOW DIAGRAM

```
START
  ↓
USER VISITS /hr/timesheet (View list)
  ↓
USER FILLS FORM:
  - Select Employee (dropdown from users table)
  - Select From Date (date picker)
  - Select To Date (date picker)
  - Optional: Notes (textarea)
  ↓
USER CLICKS "Generate Timesheet"
  ↓
POST /hr/timesheet
  ↓
TimesheetController::store()
  ├─ Validate Input
  │   ├─ employee_id exists? ✓ or ✗
  │   ├─ date_from valid? ✓ or ✗
  │   └─ date_to >= date_from? ✓ or ✗
  │
  ├─ Call buildFromAttendance()
  │   ├─ Query time_trackings WHERE employee_id AND date BETWEEN
  │   │   ├─ Found records? ✓ Continue
  │   │   └─ No records? ✗ Return NULL
  │   │
  │   ├─ Sum total_hours from all records
  │   ├─ Calculate overtime (hours > 8)
  │   └─ Fetch employee position
  │
  ├─ Check if $data is NULL
  │   ├─ NULL → Show error "No attendance found"
  │   └─ Data exists → Continue
  │
  ├─ Create Timesheet record
  │   ├─ INSERT INTO timesheets (employee_id, from_date, to_date, hours_worked, overtime, position, notes)
  │   └─ VALUES (submitted data)
  │
  └─ Redirect to timesheet.index with success message

RESULT
  ✓ Timesheet appears in table
  ✓ Can be edited/viewed/deleted
```

---

## MODULE CONNECTIONS

### How Shifts Influence Timesheet Generation

When a **TimeTracking** record is created:

```php
// TimeTrackingController::store()
$shift = Shift::where('employee_id', $request->employee_id)
    ->whereJsonContains('days', $dayName)
    ->first();

if ($shift) {
    // Compare check-in/check-out against shift hours
    // Set status = "Late", "Undertime", etc.
    // Calculate overtime against shift end time
}
```

**Impact on Timesheet:** The `overtime` and `undertime` values in TimeTracking are calculated based on shift schedules. If no shift exists, status = "No Schedule".

### How Leave Management Relates (Currently Not Integrated)

The system has `EmployeeLeave` and `LeaveType` models but:
- ❌ Timesheet generation does NOT check leave balances
- ❌ Timesheet generation does NOT deduct leave days
- ⚠️ Future enhancement: Should check if date is marked as leave and exclude from hours

### How Overtime Requests Relate (Currently Not Integrated)

The system has `OvertimeRequest` model but:
- ❌ Timesheet does NOT cross-reference with overtime requests
- ⚠️ Future enhancement: Could validate generated overtime against approved requests

---

## Testing the System

### Manual Test Path:

1. **Create Time Tracking Records**
   ```
   Go to: /hr/time-tracking
   Add record: Employee 2, 2026-02-01, 08:00-17:00
   Add record: Employee 2, 2026-02-02, 08:00-17:00
   ... (repeat for more dates)
   ```

2. **Generate Timesheet**
   ```
   Go to: /hr/timesheet
   Employee: 2
   From: 2026-02-01
   To: 2026-02-10
   Notes: (leave blank)
   Submit
   ```

3. **Expected Result**
   ```
   ✓ Timesheet record created
   ✓ Appears in table with hours_worked and overtime sums
   ✓ Success message shown
   ```

4. **Edit Timesheet**
   ```
   Click Edit on generated record
   Change notes
   Submit
   ✓ Updates in database
   ```

5. **View Details**
   ```
   Click View on record
   Modal opens showing daily breakdown
   ```

6. **Delete Timesheet**
   ```
   Click Delete
   Confirm
   ✓ Record removed from database
   ```

### Quick Test Route:
```
GET /hr/timesheet/seed-test/2
→ Creates 10 test records for employee 2 automatically
→ Then can generate timesheet for Feb 1-10, 2026
```

---

## Summary: The Missing Piece

Your system is **architecturally sound** but **data-empty**. 

The timesheet feature **works perfectly** when:
- ✅ Time tracking records exist
- ✅ For the right employee
- ✅ Within the requested date range

If generation fails, it's because **no time tracking records exist for that employee/date combo**.

**Solution:** Populate time_trackings table first → then generate timesheets.
