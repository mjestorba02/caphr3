@extends('layouts.app')

@section('title', 'Time Tracking & Attendance Record')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Time Tracking & Attendance Record</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Add record form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('timetracking.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->id }} - {{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Time In</label>
                            <input type="time" name="time_in" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Time Out</label>
                            <input type="time" name="time_out" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </form>
            </div>
        </div>

        {{-- Records table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3">Daily Records</h5>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Total Hours</th>
                            <th>Undertime</th>
                            <th>Overtime</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $rec)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $rec->employee_id }}</td>
                                <td>{{ $rec->employee->name }}</td>
                                <td>{{ $rec->date }}</td>
                                <td>{{ $rec->time_in }}</td>
                                <td>{{ $rec->time_out }}</td>
                                <td>{{ number_format($rec->total_hours, 2) }} hrs</td>
                                <td>
                                    {{ number_format($rec->undertime, 2) }} hrs
                                </td>
                                <td>
                                    {{ number_format($rec->overtime, 2) }} hrs
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($rec->status) {
                                            'Late'        => 'warning',
                                            'No Schedule' => 'secondary',
                                            default       => 'success',
                                        };
                                    @endphp

                                    <span class="badge badge-{{ $badgeClass }}">
                                        {{ $rec->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection