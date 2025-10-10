<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shift;
use App\Models\TimeTracking;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendancePortalController extends Controller
{
    public function index()
    {
        return view('attendance.portal');
    }

    public function checkName(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $user = User::where('name', $request->name)->first();

        if (!$user) {
            return response()->json(['exists' => false]);
        }

        $date = now('Asia/Manila')->toDateString();

        // Get the latest record for today
        $latestRecord = \App\Models\TimeTracking::where('employee_id', $user->id)
            ->whereDate('date', $date)
            ->latest('id')
            ->first();

        // CASE 1: No record today → should time in
        if (!$latestRecord) {
            return response()->json([
                'exists' => true,
                'employee_id' => $user->id,
                'hasTimeIn' => false, // means Time In mode
            ]);
        }

        // CASE 2: Has time_in but no time_out → should time out
        if ($latestRecord->time_in && !$latestRecord->time_out) {
            return response()->json([
                'exists' => true,
                'employee_id' => $user->id,
                'hasTimeIn' => true, // means Time Out mode
                'time_in' => $latestRecord->time_in,
                'date' => $latestRecord->date,
            ]);
        }

        // CASE 3: Has both time_in and time_out → next action is Time In again
        return response()->json([
            'exists' => true,
            'employee_id' => $user->id,
            'hasTimeIn' => false, // Time In mode again
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('name', $request->name)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid password.']);
        }

        $now = Carbon::now('Asia/Manila');
        $date = $now->toDateString();

        // Find today's latest record
        $latestRecord = TimeTracking::where('employee_id', $user->id)
            ->whereDate('date', $date)
            ->latest('id')
            ->first();

        // CASE 1: No record yet → Time In
        if (!$latestRecord) {
            TimeTracking::create([
                'employee_id' => $user->id,
                'date'        => $date,
                'time_in'     => $now->format('Y-m-d H:i:s'),
                'status'      => 'Present',
                'total_hours' => 0,
                'overtime'    => 0,
                'undertime'   => 0,
            ]);

            return response()->json(['success' => true, 'mode' => 'time_in', 'message' => 'Time-in recorded successfully.']);
        }

        // CASE 2: Has time_in but no time_out → Time Out
        if ($latestRecord && !$latestRecord->time_out) {
            $timeIn = Carbon::parse($latestRecord->time_in, 'Asia/Manila');
            $timeOut = $now;

            if ($timeOut->lte($timeIn)) {
                $timeOut->addDay();
            }

            $totalHours = abs(round($timeOut->diffInSeconds($timeIn) / 3600, 2));
            $dayName = Carbon::parse($date)->format('l');
            $status = 'Present';
            $overtime = 0;
            $undertime = 0;

            $shift = Shift::where('employee_id', $user->id)
                ->whereJsonContains('days', $dayName)
                ->first();

            if ($shift) {
                $shiftStart = Carbon::parse("{$date} {$shift->start_time}", 'Asia/Manila');
                $shiftEnd = Carbon::parse("{$date} {$shift->end_time}", 'Asia/Manila');

                if ($shiftEnd->lte($shiftStart)) {
                    $shiftEnd->addDay();
                }

                if ($timeIn->gt($shiftStart)) {
                    $status = 'Late';
                }

                if ($timeOut->gt($shiftEnd)) {
                    $overtime = abs(round($timeOut->diffInSeconds($shiftEnd) / 3600, 2));
                }

                if ($timeOut->lt($shiftEnd)) {
                    $undertime = abs(round($shiftEnd->diffInSeconds($timeOut) / 3600, 2));
                    $status = ($status === 'Late') ? 'Late & Undertime' : 'Undertime';
                }
            } else {
                $status = 'No Schedule';
            }

            $latestRecord->update([
                'time_out'    => $timeOut->format('Y-m-d H:i:s'),
                'total_hours' => $totalHours,
                'overtime'    => $overtime,
                'undertime'   => $undertime,
                'status'      => $status,
            ]);

            return response()->json(['success' => true, 'mode' => 'time_out', 'message' => 'Time-out recorded successfully.']);
        }

        // CASE 3: Has both time_in and time_out → Create new Time In record
        if ($latestRecord && $latestRecord->time_in && $latestRecord->time_out) {
            TimeTracking::create([
                'employee_id' => $user->id,
                'date'        => $date,
                'time_in'     => $now->format('Y-m-d H:i:s'),
                'status'      => 'Present',
                'total_hours' => 0,
                'overtime'    => 0,
                'undertime'   => 0,
            ]);

            return response()->json(['success' => true, 'mode' => 'time_in', 'message' => 'New time-in recorded for another session.']);
        }

        // Fallback
        return response()->json(['success' => false, 'message' => 'Unexpected error.']);
    }
}