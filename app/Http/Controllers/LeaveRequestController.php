<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\LeaveType;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $employees = User::all();
        $leaveTypes = LeaveType::all();
        $requests = LeaveRequest::with(['employee', 'leaveType'])->latest()->get();

        return view('leave.manual-request', compact('employees', 'leaveTypes', 'requests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
        ]);

        LeaveRequest::create($request->all());

        return back()->with('success', 'Leave request created successfully.');
    }

    public function update(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update($request->all());

        return back()->with('success', 'Leave request updated successfully.');
    }

    public function destroy($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->delete();

        return back()->with('success', 'Leave request deleted successfully.');
    }
}