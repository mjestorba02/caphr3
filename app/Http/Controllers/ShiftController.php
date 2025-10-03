<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with('employee')->get();
        $employees = User::all();

        return view('hr.shift', compact('shifts', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'shift_type'  => 'required|string',
            'days'        => 'required|array',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
        ]);

        Shift::create([
            'employee_id' => $request->employee_id,
            'shift_type'  => $request->shift_type,
            'days'        => $request->days, // ✅ let Laravel handle it
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'break_time'  => $request->break_time,
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift saved successfully.');
    }

     public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'shift_type'  => 'required|string',
            'days'        => 'required|array',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
        ]);

        $shift->update([
            'employee_id' => $request->employee_id,
            'shift_type'  => $request->shift_type,
            'days'        => $request->days,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'break_time'  => $request->break_time,
        ]);

        return response()->json($shift);
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return response()->json(['success' => true]);
    }
}