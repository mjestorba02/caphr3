@extends('layouts.app')

@section('title', 'Leave Management')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Leave Management</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Leave Type Form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5>Add Leave Type</h5>
                <form method="POST" action="{{ route('leave.storeType') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Leave Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Default Credits</label>
                            <input type="number" name="default_credits" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Leave Types Table --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5>Leave Types</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Leave Name</th>
                            <th>Default Credits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveTypes as $type)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $type->name }}</td>
                                <td>{{ $type->default_credits }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Employee Leave Assignment --}}
        <!-- <div class="card shadow mb-4">
            <div class="card-body">
                <h5>Assign Leave to Employee</h5>
                <form method="POST" action="{{ route('leave.storeEmployeeLeave') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Leave Type</label>
                            <select name="leave_type_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Credits</label>
                            <input type="number" name="credits" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Balance</label>
                            <input type="number" name="balance" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Assign</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Employee Leave Balances --}}
        <div class="card shadow">
            <div class="card-body">
                <h5>Employee Leave Balances</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Credits</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employeeLeaves as $leave)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $leave->employee->name }}</td>
                                <td>{{ $leave->leaveType->name }}</td>
                                <td>{{ $leave->credits }}</td>
                                <td>{{ $leave->balance }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div> -->

    </div>
</div>
@endsection