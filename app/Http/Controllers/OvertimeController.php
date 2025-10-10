<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OvertimeRequest;

class OvertimeController extends Controller
{
    public function index()
    {
        $employees = User::select('id', 'name')->get();
        $requests = OvertimeRequest::with('employee')->latest()->get();

        return view('hr.overtime', compact('employees', 'requests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date'        => 'required|date',
            'start_time'  => 'required',
            'end_time'    => 'required|after:start_time',
            'reason'      => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'Pending';

        OvertimeRequest::create($validated);

        return redirect()->back()->with('success', 'Overtime request submitted successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Approved,Denied',
        ]);

        $ot = OvertimeRequest::findOrFail($id);
        $ot->status = $validated['status'];
        $ot->save();

        return response()->json(['success' => true, 'message' => 'Overtime request ' . strtolower($validated['status']) . ' successfully.']);
    }

    public function destroy($id)
    {
        try {
            $overtime = OvertimeRequest::findOrFail($id);
            $overtime->delete();

            return response()->json(['success' => true, 'message' => 'Overtime request deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete request.']);
        }
    }
}