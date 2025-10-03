<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\EmployeeLeave;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveManagementController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::all();
        $employees = User::all();
        $employeeLeaves = EmployeeLeave::with(['employee', 'leaveType'])->get();

        return view('hr.leave-management', compact('leaveTypes', 'employees', 'employeeLeaves'));
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'default_credits' => 'required|integer|min:0',
        ]);

        LeaveType::create($request->only('name', 'default_credits'));

        return redirect()->back()->with('success', 'Leave type added successfully.');
    }

    public function storeEmployeeLeave(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'credits' => 'required|integer|min:0',
            'balance' => 'required|integer|min:0',
        ]);

        EmployeeLeave::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
            ],
            [
                'credits' => $request->credits,
                'balance' => $request->balance,
            ]
        );

        return redirect()->back()->with('success', 'Employee leave updated successfully.');
    }
}