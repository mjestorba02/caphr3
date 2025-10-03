@extends('layouts.app')

@section('title', 'Employee Shifts & Schedule')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Employee Shifts & Schedule</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Add Shift Form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('shifts.store') }}">
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
                            <label>Shift Type</label>
                            <select name="shift_type" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="morning">Morning</option>
                                <option value="afternoon">Afternoon</option>
                                <option value="night">Night</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="d-block mb-2">Select Days</label>
                            <div class="btn-group btn-group-toggle d-flex flex-wrap" data-toggle="buttons">
                                @php
                                    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                                @endphp
                                @foreach($days as $day)
                                    <label class="btn btn-outline-primary m-1">
                                        <input type="checkbox" name="days[]" value="{{ $day }}" autocomplete="off"> {{ $day }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        <div class="form-group col-md-1">
                            <label>Break</label>
                            <input type="text" name="break_time" class="form-control" placeholder="e.g. 1h">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Shift</button>
                </form>
            </div>
        </div>

        {{-- Shifts Table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3">Scheduled Shifts</h5>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Shift Type</th>
                            <th>Day</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Break</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shifts as $shift)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $shift->employee_id }}</td>
                                <td>{{ $shift->employee->name ?? 'N/A' }}</td>
                                <td class="text-capitalize">{{ $shift->shift_type }}</td>
                                <td>{{ implode(', ', $shift->days ?? []) }}</td>
                                <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
                                <td>{{ $shift->break_time ?? '—' }}</td>
                            </tr>
                        @endforeach
                        @if($shifts->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center text-muted">No shifts scheduled yet</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[name="days[]"]').forEach(cb => {
            cb.addEventListener('change', function() {
                this.closest('label').classList.toggle('active', this.checked);
            });
        });
    });
</script>
@endsection