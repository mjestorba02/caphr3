<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\TimeTracking;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;
use PDF;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $query = Timesheet::with('employee');

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by month and year
        if ($request->filled('month') && $request->filled('year')) {
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
            $year = $request->year;
            
            $query->whereYear('from_date', $year)
                  ->whereMonth('from_date', $month);
        }

        $timesheets = $query->latest()->get();
        $employees = User::all();

        // Get current month and year for default filter display
        $currentMonth = $request->month ?? Carbon::now()->month;
        $currentYear = $request->year ?? Carbon::now()->year;
        $selectedEmployee = $request->employee_id ?? null;

        return view('hr.timesheet', compact('timesheets', 'employees', 'currentMonth', 'currentYear', 'selectedEmployee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after_or_equal:date_from',
            'notes'       => 'nullable|string',
        ]);

        $data = $this->buildFromAttendance(
            $request->employee_id,
            $request->notes,
            $request->date_from,
            $request->date_to
        );

        if (! $data) {
            return back()->withErrors([
                'date_from' => 'No time & attendance records found in the selected range.'
            ]);
        }

        Timesheet::create($data);

        return redirect()->route('timesheet.index')
                        ->with('success', 'Timesheet generated successfully.');
    }

    protected function buildFromAttendance($employeeId, $notes, $dateFrom, $dateTo)
    {
        $attendance = TimeTracking::where('employee_id', $employeeId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        if ($attendance->isEmpty()) {
            return null;
        }

        $employee = User::find($employeeId);

        // Compute totals from attendance records
        $totalHours = 0;
        $totalOvertime = 0;

        foreach ($attendance as $log) {
            $hours = (float) ($log->total_hours ?? 0);
            $totalHours += $hours;

            if ($hours > 8) {
                $totalOvertime += ($hours - 8);
            }
        }

        return [
            'employee_id' => $employeeId,
            'from_date'   => $dateFrom,
            'to_date'     => $dateTo,
            'hours_worked' => round($totalHours, 2),
            'overtime'     => round($totalOvertime, 2),
            'position'     => $employee->position ?? null,
            'notes'        => $notes,
            'day'          => null,
        ];
    }

    public function update(Request $request, Timesheet $timesheet)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after_or_equal:date_from',
            'notes'       => 'nullable|string',
        ]);

        $data = $this->buildFromAttendance(
            $request->employee_id,
            $request->notes,
            $request->date_from,
            $request->date_to
        );

        if (! $data) {
            return back()->withErrors(['date_from' => 'No attendance record found for the selected range.']);
        }

        $timesheet->update($data);

        return redirect()->route('timesheet.index')
                        ->with('success', 'Timesheet updated successfully.');
    }

    public function destroy(Timesheet $timesheet)
    {
        $timesheet->delete();

        return redirect()->route('timesheet.index')
                         ->with('success', 'Timesheet record deleted.');
    }

    public function details(Request $request, $employeeId)
    {
        $from = $request->date_from ?? $request->from;
        $to   = $request->date_to   ?? $request->to;

        $employeeRecords = \App\Models\TimeTracking::with('employee')
            ->where('employee_id', $employeeId)
            ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
            ->orderBy('date', 'asc')
            ->get();

        if ($employeeRecords->isEmpty()) {
            return response()->json([
                'message' => 'No records found.',
                'records' => []
            ]);
        }

        $employee = $employeeRecords->first()->employee;

        $records = $employeeRecords->map(function ($r) {
            $start = $r->time_in ? Carbon::parse($r->time_in) : null;
            $end   = $r->time_out ? Carbon::parse($r->time_out) : null;

            return [
                'date'         => $r->date,
                'start_time'   => $start ? $start->format('H:i') : '-',
                'end_time'     => $end ? $end->format('H:i') : '-',
                'hours_worked' => $r->total_hours ?? 0,
                'overtime'     => $r->overtime ?? 0,
                'status'       => $r->status ?? '-',
            ];
        });

        // Totals from DB values
        $totals = [
            'hours_worked' => $records->sum('hours_worked'),
            'overtime'     => $records->sum('overtime'),
        ];

        return response()->json([
            'employee_id' => $employee->id,
            'employee'   => $employee->name ?? 'N/A',
            'department' => $employee->department ?? 'N/A',
            'position'   => $employee->position ?? 'N/A',
            'date_from'  => $from,
            'date_to'    => $to,
            'records'    => $records,
            'totals'     => $totals,
        ]);
    }

    public function report(Request $request)
    {
        $positions = \App\Models\User::select('position')->distinct()->pluck('position');

        $from = $request->from;
        $to = $request->to;
        $position = $request->position;

        $reportData = [];

        if ($from && $to) {
            // Get all time tracking records joined with users
            $query = \App\Models\TimeTracking::with('employee')
                ->whereBetween('date', [$from, $to]);

            if ($position) {
                $query->whereHas('employee', function ($q) use ($position) {
                    $q->where('position', $position);
                });
            }

            $records = $query->get();

            // Group by employee
            $grouped = $records->groupBy('employee_id');

            foreach ($grouped as $empId => $logs) {
                $employee = $logs->first()->employee;

                $totalHours = $logs->sum('total_hours');
                $overtime = $logs->sum('overtime');
                $undertime = $logs->sum('undertime');

                $reportData[] = [
                    'employee_id' => $employee->id,
                    'employee' => $employee->name,
                    'position' => $employee->position ?? 'N/A',
                    'department' => $employee->department ?? 'N/A',
                    'total_hours' => round($totalHours, 2),
                    'overtime' => round($overtime, 2),
                    'undertime' => round($undertime, 2),
                ];
            }
        }

        return view('hr.timesheet_report', compact('positions', 'reportData', 'from', 'to', 'position'));
    }

    public function getEmployees($position)
    {
        $employees = \App\Models\User::where('position', $position)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($employees);
    }

    public function download($employee, Request $request)
    {
        // Filter the data for that specific employee
        $from = $request->query('from');
        $to = $request->query('to');

        $records = \App\Models\TimeTracking::whereHas('employee', function($q) use ($employee) {
                $q->where('name', $employee);
            })
            ->whereBetween('date', [$from, $to])
            ->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'No timesheet data found for this employee.');
        }

        $employeeInfo = $records->first()->employee;

        // Total calculations
        $totalHours = $records->sum('total_hours');
        $totalOvertime = $records->sum('overtime');
        $totalUndertime = $records->sum('undertime');

        $pdf = PDF::loadView('timesheet.pdf', [
            'employee' => $employeeInfo,
            'records' => $records,
            'from' => $from,
            'to' => $to,
            'totalHours' => $totalHours,
            'totalOvertime' => $totalOvertime,
            'totalUndertime' => $totalUndertime,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Timesheet_{$employee}_{$from}_to_{$to}.pdf");
    }

    public function seedTestAttendance($employeeId = null)
    {
        $employeeId = $employeeId ?? 2;

        // Create 10 test attendance records for Feb 2026
        $records = [];
        for ($i = 1; $i <= 10; $i++) {
            $date = Carbon::createFromFormat('Y-m-d', sprintf('2026-02-%02d', $i));

            TimeTracking::create([
                'employee_id' => $employeeId,
                'date' => $date,
                'time_in' => '08:00:00',
                'time_out' => '17:00:00',
                'total_hours' => 9.0,
                'overtime' => 1.0,
                'undertime' => 0,
                'status' => 'Present',
            ]);

            $records[] = "Feb {$i}, 2026 - 9 hours";
        }

        return response()->json([
            'success' => true,
            'message' => 'Test data seeded successfully',
            'employee_id' => $employeeId,
            'records_created' => count($records),
            'date_range' => '2026-02-01 to 2026-02-10',
            'details' => $records,
        ]);
    }
}
