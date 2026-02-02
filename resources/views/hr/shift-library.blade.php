@extends('layouts.app')

@section('title', 'Shift Library')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Shift Library Management</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Add Shift Library Form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5>Create New Shift</h5>
                <form method="POST" action="{{ route('shift-library.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Shift Name</label>
                            <input type="text" name="shift_name" class="form-control" placeholder="e.g., Morning Shift" required>
                            @error('shift_name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-2">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                            @error('start_time')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-2">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
                            @error('end_time')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-2">
                            <label>Break Time</label>
                            <input type="text" name="break_time" class="form-control" placeholder="e.g., 1h or 30m">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Optional notes">
                        </div>
                        <div class="form-group col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Shift Library Table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3">Available Shifts</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Shift Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Break Time</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $shift->shift_name }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
                                    <td>{{ $shift->break_time ?? '—' }}</td>
                                    <td>{{ $shift->description ?? '—' }}</td>
                                    <td>
                                        <button type="button" 
                                            class="btn btn-sm btn-warning"
                                            data-toggle="modal"
                                            data-target="#editShiftModal"
                                            onclick="editShift({{ $shift }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <form action="{{ route('shift-library.destroy', $shift->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this shift?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No shifts created yet. Create one to get started!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Edit Shift Modal --}}
<div class="modal fade" id="editShiftModal" tabindex="-1" role="dialog" aria-labelledby="editShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editShiftModalLabel">Edit Shift</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editShiftForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Shift Name</label>
                        <input type="text" name="shift_name" id="edit_shift_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Break Time</label>
                        <input type="text" name="break_time" id="edit_break_time" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editShift(shift) {
    document.getElementById('edit_shift_name').value = shift.shift_name;
    document.getElementById('edit_start_time').value = shift.start_time;
    document.getElementById('edit_end_time').value = shift.end_time;
    document.getElementById('edit_break_time').value = shift.break_time || '';
    document.getElementById('edit_description').value = shift.description || '';
    document.getElementById('editShiftForm').action = '/hr/shift-library/' + shift.id;
}
</script>

@endsection
