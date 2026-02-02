@extends('layouts.app')

@section('title', 'Employee Shifts & Schedule')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Employee Shifts & Schedule</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Shift Library Link --}}
        <div class="mb-3">
            <a href="{{ route('shift-library.index') }}" class="btn btn-info">
                <i class="fas fa-cog"></i> Manage Shift Library
            </a>
        </div>

        {{-- Assign Employee Shift Form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5>Assign Employee to Shift</h5>
                <form method="POST" action="{{ route('shifts.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->id }} - {{ $emp->name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label>Select Shift <span class="text-danger">*</span></label>
                            <select name="shift_library_id" class="form-control" required>
                                <option value="">-- Select Shift --</option>
                                @foreach($shiftLibraries as $lib)
                                    <option value="{{ $lib->id }}">
                                        {{ $lib->shift_name }} ({{ \Carbon\Carbon::parse($lib->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($lib->end_time)->format('h:i A') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('shift_library_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label class="d-block mb-2">Select Working Days <span class="text-danger">*</span></label>
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
                            @error('days')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Assign Shift
                    </button>
                </form>
            </div>
        </div>

        {{-- Employee Shifts Table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3">Assigned Employee Shifts</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Shift Name</th>
                                <th>Time Range</th>
                                <th>Working Days</th>
                                <th>Break</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $shift->employee_id }}</td>
                                    <td><strong>{{ $shift->employee->name ?? 'N/A' }}</strong></td>
                                    <td>{{ $shift->shiftLibrary->shift_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($shift->shiftLibrary)
                                            {{ \Carbon\Carbon::parse($shift->shiftLibrary->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->shiftLibrary->end_time)->format('h:i A') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ implode(', ', $shift->days ?? []) }}</td>
                                    <td>{{ $shift->shiftLibrary->break_time ?? '—' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                            onclick="editShift({{ $shift->id }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        
                                        <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Remove this assignment?')">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No shifts assigned yet. Create shifts in the Shift Library first!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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

    function editShift(shiftId) {
        alert('Edit shift functionality coming soon!');
    }
</script>
@endsection