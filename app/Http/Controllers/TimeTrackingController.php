<?php

namespace App\Http\Controllers;

use App\Models\TimeTracking;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeTrackingController extends Controller
{
    public function index()
    {
        $records = TimeTracking::with('employee')->latest()->get();
        $employees = User::all();

        return view('hr.time-tracking', compact('records','employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date'        => 'required|date',
            'time_in'     => 'required|date_format:H:i',
            'time_out'    => 'required|date_format:H:i|after:time_in',
        ]);

        $timeIn  = Carbon::parse($request->date . ' ' . $request->time_in);
        $timeOut = Carbon::parse($request->date . ' ' . $request->time_out);
        $dayName = Carbon::parse($request->date)->format('l');

        $status    = 'Present';
        $overtime  = 0;
        $undertime = 0;
        $hours     = abs(round($timeOut->diffInMinutes($timeIn) / 60, 2)); // ✅ always positive

        // Find the employee's shift for that day
        $shift = \App\Models\Shift::where('employee_id', $request->employee_id)
            ->whereJsonContains('days', $dayName)
            ->first();

        if ($shift) {
            $shiftStart = Carbon::parse($request->date . ' ' . $shift->start_time);
            $shiftEnd   = Carbon::parse($request->date . ' ' . $shift->end_time);

            // Late check
            if ($timeIn->gt($shiftStart)) {
                $status = 'Late';
            }

            // Overtime check
            if ($timeOut->gt($shiftEnd)) {
                $overtime = abs(round($timeOut->diffInMinutes($shiftEnd) / 60, 2));
            }

            // Undertime check
            if ($timeOut->lt($shiftEnd)) {
                $undertime = abs(round($shiftEnd->diffInMinutes($timeOut) / 60, 2));
                $status = 'Undertime';
            }
        } else {
            $status = 'No Schedule';
        }

        TimeTracking::create([
            'employee_id' => $request->employee_id,
            'date'        => $request->date,
            'time_in'     => $request->time_in,
            'time_out'    => $request->time_out,
            'total_hours' => $hours,
            'overtime'    => $overtime,
            'undertime'   => $undertime, // ✅ new column
            'status'      => $status,
        ]);

        return redirect()->route('timetracking.index')
                        ->with('success', 'Time record saved successfully.');
    }
}