# System Architecture Diagram

## Data Flow & Relationships

```
┌─────────────────────────────────────────────────────────────────────┐
│                          HR SYSTEM                                   │
└─────────────────────────────────────────────────────────────────────┘

                              TOP LEVEL
                              
    ┌──────────────────────────────────────────────────────────────┐
    │                    SHIFT MANAGEMENT                          │
    └──────────────────────────────────────────────────────────────┘
                          ▼                    ▼
            ┌─────────────────────┐  ┌──────────────────────┐
            │ SHIFT LIBRARY       │  │ EMPLOYEE SHIFTS      │
            │ (Master List)       │  │ (Assignments)        │
            ├─────────────────────┤  ├──────────────────────┤
            │ • Morning Shift     │  │ Employee: John       │
            │ • Afternoon Shift   │  │ Shift: Morning       │
            │ • Night Shift       │  │ Days: Mon-Fri        │
            │                     │  │                      │
            │ 9-3 PM ✓            │  │ Employee: Sarah      │
            │ 3-11 PM ✓           │  │ Shift: Afternoon     │
            │ 11-7 AM ✓           │  │ Days: Tue-Sat        │
            └─────────────────────┘  └──────────────────────┘
                       ▲                       ▲
                       └───────────────────────┘
                        Foreign Key Link
                      shift_library_id


                        TIMESHEET SYSTEM
                              ▼
    ┌──────────────────────────────────────────────────────────────┐
    │              TIMESHEET RECORDS WITH FILTERS                  │
    ├──────────────────────────────────────────────────────────────┤
    │ Employee Filter  │ Month Filter │ Year Filter │ APPLY        │
    │ ─ Select all ─   │ ─ All Months │ ─ All Years │              │
    │ • John (1)       │ • January    │ • 2023      │ [FILTER] ✓   │
    │ • Sarah (2)      │ • February   │ • 2024      │              │
    │ • Mike (3)       │ • ...        │ • 2025      │ [RESET]      │
    │                  │ • October ✓  │ • 2026      │              │
    └──────────────────────────────────────────────────────────────┘
                       │
                       ▼
    ┌──────────────────────────────────────────────────────────────┐
    │        FILTERED RESULTS (John, October 2025)                 │
    ├──────────────────────────────────────────────────────────────┤
    │ Employee | From Date  | To Date    | Hours | Notes           │
    │ John     | 2025-10-01 | 2025-10-31 | 160   | October work   │
    │ John     | 2025-10-15 | 2025-10-20 | 40    | Project Alpha  │
    └──────────────────────────────────────────────────────────────┘
```

---

## Database Schema

```
┌────────────────────┐           ┌────────────────────┐
│      USERS         │           │  SHIFT_LIBRARIES   │
├────────────────────┤           ├────────────────────┤
│ id (PK)      ◄─────────┐       │ id (PK)            │
│ name                   │       │ shift_name (UNIQUE)│
│ email                  │       │ start_time         │
│ position               │       │ end_time           │
│ department             │       │ break_time         │
│ password               │       │ description        │
└────────────────────┘           │ timestamps         │
         ▲                        └────────────────────┘
         │                                 ▲
         │                                 │
         │                    ┌────────────────────┐
         │                    │      SHIFTS        │
         │                    ├────────────────────┤
         ├────────────────────┤ id (PK)            │
         │ PK (FK)            │ employee_id (FK)──►│
         │ employee_id        │ shift_library_id──►│
         │                    │ days (JSON)        │
         │                    │ timestamps         │
         │                    └────────────────────┘
         │
         │
         ├──────────────────────┐
         │                      │
    ┌────────────────────┐  ┌──────────────────┐
    │   TIMESHEETS       │  │  TIME_TRACKINGS  │
    ├────────────────────┤  ├──────────────────┤
    │ id (PK)            │  │ id (PK)          │
    │ employee_id (FK)──►│  │ employee_id (FK)►│
    │ from_date          │  │ date             │
    │ to_date            │  │ start_time       │
    │ hours_worked       │  │ end_time         │
    │ overtime           │  │ total_hours      │
    │ position           │  │ overtime         │
    │ notes              │  │ undertime        │
    │ timestamps         │  │ timestamps       │
    └────────────────────┘  └──────────────────┘
```

---

## Workflow Diagrams

### Workflow 1: Create and Assign Shift

```
START
  │
  ├─ Admin goes to /hr/shift-library
  │
  ├─ Fills shift form:
  │  ├─ Shift Name: "Morning Shift"
  │  ├─ Start Time: 09:00
  │  ├─ End Time: 15:00
  │  └─ Break Time: 1h
  │
  ├─ Clicks [Add]
  │
  ├─ ShiftLibraryController@store():
  │  ├─ Validates input
  │  └─ Creates ShiftLibrary record
  │
  ├─ Success message displayed
  │
  ├─ Admin goes to /hr/shifts
  │
  ├─ Fills assignment form:
  │  ├─ Employee: John
  │  ├─ Shift: Morning Shift (dropdown ← from shift_libraries)
  │  └─ Days: Mon-Fri
  │
  ├─ Clicks [Assign Shift]
  │
  ├─ ShiftController@store():
  │  ├─ Validates data
  │  ├─ Creates Shift record with:
  │  │  ├─ employee_id = 1 (John)
  │  │  ├─ shift_library_id = 1 (Morning Shift)
  │  │  └─ days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
  │  └─ Redirects to shifts.index
  │
  ├─ Assignment appears in table
  └─ END (John now assigned to Morning Shift)
```

### Workflow 2: Filter Timesheet Records

```
START
  │
  ├─ HR opens /hr/timesheet
  │
  ├─ Sees filter section:
  │  ├─ Employee dropdown
  │  ├─ Month dropdown
  │  └─ Year dropdown
  │
  ├─ HR selects:
  │  ├─ Employee: "John"
  │  ├─ Month: "October"
  │  └─ Year: "2025"
  │
  ├─ Clicks [Filter]
  │
  ├─ GET request: /hr/timesheet?employee_id=1&month=10&year=2025
  │
  ├─ TimesheetController@index():
  │  ├─ Builds query: WHERE employee_id = 1
  │  ├─ Adds: WHERE YEAR(from_date) = 2025 AND MONTH(from_date) = 10
  │  ├─ Orders by: latest
  │  └─ Returns results
  │
  ├─ Table displays filtered results
  │
  ├─ Header shows: "Filtered by John, October 2025"
  │
  ├─ HR can now:
  │  ├─ View details
  │  ├─ Edit records
  │  └─ Delete records
  │
  ├─ OR Click [Reset] to clear filters
  └─ END (Viewing only John's October timesheet)
```

### Workflow 3: Update Shift (Edit)

```
START
  │
  ├─ Admin in /hr/shift-library
  │
  ├─ Sees shift: "Morning Shift (9:00 AM - 3:00 PM)"
  │
  ├─ Clicks [Edit] button
  │
  ├─ Modal opens with current data:
  │  ├─ shift_name: Morning Shift
  │  ├─ start_time: 09:00
  │  ├─ end_time: 15:00
  │  └─ break_time: 1h
  │
  ├─ Admin changes:
  │  └─ end_time: 16:00 (now 4 PM instead of 3 PM)
  │
  ├─ Clicks [Update Shift]
  │
  ├─ PUT request: /hr/shift-library/1
  │
  ├─ ShiftLibraryController@update():
  │  ├─ Validates input
  │  ├─ Updates ShiftLibrary record
  │  └─ Redirects to shift-library.index
  │
  ├─ Success message: "Shift library updated successfully"
  │
  ├─ Important: ALL employees assigned to this shift
  │  ├─ Will see the new time: 9:00 AM - 4:00 PM
  │  ├─ Their assignment automatically reflects change
  │  └─ No need to reassign!
  │
  └─ END (Shift updated, all assignments reflect new time)
```

---

## Filter Logic Flow

```
User visits: /hr/timesheet?employee_id=1&month=10&year=2025

                         │
                         ▼
        TimesheetController@index(Request $request)
                         │
                         ├─ $query = Timesheet::with('employee')
                         │
                         ├─ if $request->filled('employee_id')
                         │  └─ $query->where('employee_id', 1)
                         │
                         ├─ if $request->filled('month') && $request->filled('year')
                         │  ├─ $month = "10"
                         │  ├─ $year = "2025"
                         │  └─ $query->whereYear('from_date', 2025)
                         │            ->whereMonth('from_date', 10)
                         │
                         ├─ $timesheets = $query->latest()->get()
                         │
                         └─ return view('hr.timesheet', compact(...))
                                        │
                                        ▼
                            Blade Template renders table
                                        │
                                        ├─ Shows filtered results
                                        ├─ Shows filter indicators
                                        └─ Provides Reset button
```

---

## Component Interaction Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                       WEB BROWSER                            │
│                                                              │
│  /hr/shift-library ──────► View All Shifts ◄──── Add Shift  │
│       │                         │                    │       │
│       │ (Edit) ───────┐        │         ┌─────────┘       │
│       │               │        │         │                  │
│       ▼               ▼        ▼         ▼                  │
│    Update Shift   Modal Form   Table   Create Form          │
│       │               │        │         │                  │
│       └───────────────┴────────┴─────────┘                  │
│               │                                             │
└───────────────┼─────────────────────────────────────────────┘
                │ HTTP Requests (POST, PUT, DELETE)
                ▼
┌─────────────────────────────────────────────────────────────┐
│                    LARAVEL BACKEND                           │
│                                                              │
│  routes/web.php                                             │
│       │                                                     │
│       ├─ POST /shift-library ──────► ShiftLibraryController│
│       │                              │ @store()            │
│       │                              ▼                     │
│       ├─ PUT /shift-library/{id} ───► Validate Input       │
│       │                              ▼                     │
│       ├─ DELETE /shift-library/{id}► ShiftLibrary Model    │
│       │                              │                     │
│       └─ GET /shift-library/api/all  ├─ Create/Update/Delete
│                                      │                     │
│                                      ▼                     │
│                                  Database                  │
└─────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
                        ┌──────────────────────────┐
                        │   shift_libraries TABLE  │
                        │  (Shift definitions)     │
                        │                          │
                        │ - Morning Shift 9-3 PM   │
                        │ - Afternoon Shift 3-11PM │
                        │ - Night Shift 11-7 AM    │
                        └──────────────────────────┘
```

---

## Security & Data Flow

```
User Input
   │
   ├─ Validation (Laravel)
   │  ├─ Check required fields
   │  ├─ Check unique values
   │  ├─ Check data types
   │  └─ Check relationships exist
   │
   ├─ If Invalid ──► Show Error Message
   │
   ├─ If Valid ──────► Database Query
   │  ├─ Prepared Statements (Protection from SQL Injection)
   │  ├─ Foreign Key Constraints (Data Integrity)
   │  ├─ Unique Constraints (Prevent Duplicates)
   │  └─ Cascading Rules (Delete Protection)
   │
   └─ Response to User
      ├─ Success: Redirect with message
      └─ Error: Show validation errors
```

---

## API Call Sequence

```
CLIENT REQUEST
    │
    ▼
GET /hr/timesheet?employee_id=1&month=10&year=2025
    │
    ▼
Route Matching (routes/web.php)
    │
    ▼
TimesheetController::index(Request)
    │
    ├─ Extract query params
    ├─ Build SQL query conditionally
    ├─ Execute query on database
    └─ Compile results
    │
    ▼
View Compilation (timesheet.blade.php)
    │
    ├─ Render filter section
    ├─ Render table rows
    ├─ Render filter indicators
    └─ Generate final HTML
    │
    ▼
HTTP Response (200 OK)
    │
    ▼
Browser Renders HTML
    │
    ▼
USER SEES FILTERED TIMESHEET
```

---

**This architecture ensures:**
✅ Clean separation of concerns  
✅ Data integrity  
✅ Security  
✅ Scalability  
✅ Easy maintenance  

---

Document Version: 1.0  
Last Updated: February 2, 2026
