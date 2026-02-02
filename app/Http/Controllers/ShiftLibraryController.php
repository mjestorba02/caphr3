<?php

namespace App\Http\Controllers;

use App\Models\ShiftLibrary;
use Illuminate\Http\Request;

class ShiftLibraryController extends Controller
{
    /**
     * Display all shift libraries
     */
    public function index()
    {
        $shifts = ShiftLibrary::latest()->get();
        return view('hr.shift-library', compact('shifts'));
    }

    /**
     * Store a new shift library
     */
    public function store(Request $request)
    {
        $request->validate([
            'shift_name' => 'required|string|unique:shift_libraries,shift_name',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'break_time' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        ShiftLibrary::create([
            'shift_name'  => $request->shift_name,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'break_time'  => $request->break_time,
            'description' => $request->description,
        ]);

        return redirect()->route('shift-library.index')
                        ->with('success', 'Shift library created successfully.');
    }

    /**
     * Update a shift library
     */
    public function update(Request $request, ShiftLibrary $shiftLibrary)
    {
        $request->validate([
            'shift_name' => 'required|string|unique:shift_libraries,shift_name,' . $shiftLibrary->id,
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'break_time' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $shiftLibrary->update([
            'shift_name'  => $request->shift_name,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'break_time'  => $request->break_time,
            'description' => $request->description,
        ]);

        return redirect()->route('shift-library.index')
                        ->with('success', 'Shift library updated successfully.');
    }

    /**
     * Delete a shift library
     */
    public function destroy(ShiftLibrary $shiftLibrary)
    {
        // Check if any employees are using this shift
        if ($shiftLibrary->employeeShifts()->exists()) {
            return back()->with('error', 'Cannot delete this shift. Employees are currently assigned to it.');
        }

        $shiftLibrary->delete();

        return redirect()->route('shift-library.index')
                        ->with('success', 'Shift library deleted successfully.');
    }

    /**
     * Get all shifts for dropdown (API endpoint)
     */
    public function getAll()
    {
        $shifts = ShiftLibrary::select('id', 'shift_name', 'start_time', 'end_time', 'break_time')->get();
        return response()->json($shifts);
    }
}
