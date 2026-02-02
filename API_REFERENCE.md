# API Routes & Endpoints Reference

## Routes Added to `routes/web.php`

### Shift Library Routes
```php
// View all shifts in library
GET /hr/shift-library                          → shift-library.index
    Controllers: ShiftLibraryController@index

// Create new shift
POST /hr/shift-library                         → shift-library.store
    Controllers: ShiftLibraryController@store
    Parameters: shift_name, start_time, end_time, break_time (optional), description (optional)

// Update existing shift
PUT /hr/shift-library/{shiftLibrary}          → shift-library.update
    Controllers: ShiftLibraryController@update
    Parameters: shift_name, start_time, end_time, break_time (optional), description (optional)

// Delete shift
DELETE /hr/shift-library/{shiftLibrary}       → shift-library.destroy
    Controllers: ShiftLibraryController@destroy

// Get all shifts as JSON (for dropdowns)
GET /hr/shift-library/api/all                 → shift-library.getAll
    Controllers: ShiftLibraryController@getAll
    Returns: JSON array of all shifts
```

---

## Updated Routes

### Shift Assignment Routes (Enhanced)
```php
// View assigned shifts
GET /hr/shifts                                 → shifts.index
    Controllers: ShiftController@index

// Assign employee to shift
POST /hr/shifts                                → shifts.store
    Controllers: ShiftController@store
    Parameters: employee_id, shift_library_id, days[]

// Update employee shift assignment
PUT /hr/shifts/{shift}                         → shifts.update
    Controllers: ShiftController@update
    Parameters: employee_id, shift_library_id, days[]

// Remove employee shift assignment
DELETE /hr/shifts/{shift}                      → shifts.destroy
    Controllers: ShiftController@destroy
```

### Timesheet Routes (Enhanced with Filtering)
```php
// View timesheet with optional filters
GET /hr/timesheet                              → timesheet.index
    Controllers: TimesheetController@index
    Query Parameters (optional):
        - employee_id: Int (ID of employee)
        - month: Int (1-12)
        - year: Int (YYYY)
    
    Example: /hr/timesheet?employee_id=1&month=10&year=2025

// Create new timesheet
POST /hr/timesheet                             → timesheet.store
    Controllers: TimesheetController@store
    Parameters: employee_id, date_from, date_to, notes (optional)

// Update timesheet
PUT /hr/timesheet/{timesheet}                  → timesheet.update
    Controllers: TimesheetController@update

// Delete timesheet
DELETE /hr/timesheet/{timesheet}               → timesheet.destroy
    Controllers: TimesheetController@destroy
```

---

## Controller Methods

### ShiftLibraryController

#### index()
```
GET /hr/shift-library
Description: Display all shifts in the library
Returns: Blade view (hr.shift-library) with $shifts variable
```

#### store()
```
POST /hr/shift-library
Validation:
  - shift_name: required|string|unique:shift_libraries,shift_name
  - start_time: required|date_format:H:i
  - end_time: required|date_format:H:i|after:start_time
  - break_time: nullable|string
  - description: nullable|string
Returns: Redirect to shift-library.index with success message
```

#### update()
```
PUT /hr/shift-library/{shiftLibrary}
Parameters: Same as store() validation
Returns: Redirect to shift-library.index with success message
```

#### destroy()
```
DELETE /hr/shift-library/{shiftLibrary}
Checks: If employees assigned to this shift
Returns: 
  - Redirect with error if employees assigned
  - Redirect with success if deleted
```

#### getAll()
```
GET /hr/shift-library/api/all
Returns: JSON array
[
  {
    "id": 1,
    "shift_name": "Morning Shift",
    "start_time": "09:00",
    "end_time": "15:00",
    "break_time": "1h"
  },
  ...
]
```

### ShiftController

#### index()
```
GET /hr/shifts
Description: View all employee shift assignments with library details
Returns: Blade view (hr.shift) with:
  - $shifts: All assignments with relationships loaded
  - $employees: All available employees
  - $shiftLibraries: All available shifts
```

#### store()
```
POST /hr/shifts
Validation:
  - employee_id: required|exists:users,id
  - shift_library_id: required|exists:shift_libraries,id
  - days: required|array (e.g., ["Monday", "Tuesday", ...])
Returns: Redirect with success message
```

#### update()
```
PUT /hr/shifts/{shift}
Same validation as store()
Returns: JSON response with updated shift
```

#### destroy()
```
DELETE /hr/shifts/{shift}
Returns: JSON {"success": true}
```

### TimesheetController

#### index()
```
GET /hr/timesheet
Query Parameters (optional):
  - employee_id: Filter by employee
  - month: Filter by month (1-12)
  - year: Filter by year

Logic:
  1. Build query from Timesheet model
  2. Add where clauses if filters provided
  3. Filter by date range for month/year
  4. Return results ordered by latest first

Returns: Blade view (hr.timesheet) with:
  - $timesheets: Filtered results
  - $employees: All employees
  - $currentMonth: Currently selected month
  - $currentYear: Currently selected year
  - $selectedEmployee: Currently selected employee ID
```

---

## Query Examples

### Filter timesheet for Employee 1, October 2025
```url
GET /hr/timesheet?employee_id=1&month=10&year=2025
```

### Filter timesheet for specific month only
```url
GET /hr/timesheet?month=3&year=2025
```

### Reset filters (show all)
```url
GET /hr/timesheet
```

### Assign employee to shift
```
POST /hr/shifts
Form Data:
  - employee_id: 1
  - shift_library_id: 2
  - days[]: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
```

### Create new shift in library
```
POST /hr/shift-library
Form Data:
  - shift_name: "Afternoon Shift"
  - start_time: 15:00
  - end_time: 23:00
  - break_time: "1h"
  - description: "3 PM to 11 PM shift"
```

---

## Model Relationships

### ShiftLibrary
```php
// Has many employee shift assignments
$shiftLibrary->employeeShifts()  // Returns Shift collection
```

### Shift
```php
// Belongs to an employee
$shift->employee()  // Returns User

// Belongs to a shift library
$shift->shiftLibrary()  // Returns ShiftLibrary
```

### User
```php
// Has many shift assignments
$user->shifts()  // Returns Shift collection
```

---

## Response Examples

### Shift Library JSON
```json
[
  {
    "id": 1,
    "shift_name": "Morning Shift",
    "start_time": "09:00:00",
    "end_time": "15:00:00",
    "break_time": "1h",
    "description": "Morning duty",
    "created_at": "2025-02-02T12:00:00Z",
    "updated_at": "2025-02-02T12:00:00Z"
  }
]
```

### Shift Assignment JSON
```json
{
  "id": 5,
  "employee_id": 1,
  "shift_library_id": 2,
  "days": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
  "created_at": "2025-02-02T12:30:00Z",
  "updated_at": "2025-02-02T12:30:00Z"
}
```

### Timesheet Record JSON
```json
{
  "id": 10,
  "employee_id": 1,
  "from_date": "2025-10-01",
  "to_date": "2025-10-31",
  "hours_worked": 160,
  "overtime": 8,
  "position": "Manager",
  "notes": "October record",
  "created_at": "2025-02-02T12:00:00Z"
}
```

---

## Error Responses

### 404 Not Found
```json
{
  "message": "No query results for model [App\\Models\\ShiftLibrary]"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "shift_name": ["The shift name has already been taken."],
    "end_time": ["The end time must be after start time."]
  }
}
```

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success (GET) |
| 201 | Created (POST) |
| 204 | No Content (DELETE) |
| 302 | Redirect (after POST/PUT/DELETE) |
| 404 | Not Found |
| 422 | Unprocessable Entity (Validation Error) |
| 500 | Server Error |

---

## Testing in Postman/Curl

### Get all shifts in library
```bash
curl -X GET http://127.0.0.1:8000/hr/shift-library/api/all
```

### Create a shift
```bash
curl -X POST http://127.0.0.1:8000/hr/shift-library \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "shift_name=Evening+Shift&start_time=18:00&end_time=23:00&break_time=1h&_token=YOUR_CSRF_TOKEN"
```

### Filter timesheet
```bash
curl -X GET "http://127.0.0.1:8000/hr/timesheet?employee_id=1&month=10&year=2025"
```

---

**Document Version:** 1.0
**Last Updated:** February 2, 2026
