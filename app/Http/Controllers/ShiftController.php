<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftLibrary;
use App\Models\User;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with('employee', 'shiftLibrary')->get();
        $employees = User::all();
        $shiftLibraries = ShiftLibrary::all();

        return view('hr.shift', compact('shifts', 'employees', 'shiftLibraries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'shift_library_id' => 'required|exists:shift_libraries,id',
            'days'        => 'required|array',
        ]);

        Shift::create([
            'employee_id' => $request->employee_id,
            'shift_library_id' => $request->shift_library_id,
            'days'        => $request->days,
        ]);

        return redirect()->route('shifts.index')->with('success', 'Employee shift assigned successfully.');
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'shift_library_id' => 'required|exists:shift_libraries,id',
            'days'        => 'required|array',
        ]);

        $shift->update([
            'employee_id' => $request->employee_id,
            'shift_library_id' => $request->shift_library_id,
            'days'        => $request->days,
        ]);

        return response()->json($shift);
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return response()->json(['success' => true]);
    }
}