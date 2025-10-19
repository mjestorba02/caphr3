@extends('layouts.app')

@section('title', 'Manual Leave Request')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Manual Leave Request</h4>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Add Leave Request Button --}}
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            + New Leave Request
        </button>

        {{-- Leave Requests Table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5>Leave Requests</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $req->employee->name }}</td>
                                <td>{{ $req->leaveType->name }}</td>
                                <td>{{ $req->start_date }}</td>
                                <td>{{ $req->end_date }}</td>
                                <td>{{ $req->reason }}</td>
                                <td>
                                    <span class="badge 
                                        @if($req->status == 'Approved') bg-success 
                                        @elseif($req->status == 'Rejected') bg-danger 
                                        @else bg-warning text-dark @endif">
                                        {{ $req->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $req->id }}">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $req->id }}">
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            {{-- Edit Modal --}}
                            <div class="modal fade" id="editModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('leave.manual.update', $req->id) }}">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-white">
                                                <h5 class="modal-title">Edit Leave Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Leave Type</label>
                                                    <select name="leave_type_id" class="form-control">
                                                        @foreach($leaveTypes as $type)
                                                            <option value="{{ $type->id }}" {{ $req->leave_type_id == $type->id ? 'selected' : '' }}>
                                                                {{ $type->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group mt-2">
                                                    <label>Start Date</label>
                                                    <input type="date" name="start_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ $req->start_date }}" required>
                                                </div>

                                                <div class="form-group mt-2">
                                                    <label>End Date</label>
                                                    <input type="date" name="end_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ $req->end_date }}" required>
                                                </div>

                                                <div class="form-group mt-2">
                                                    <label>Reason</label>
                                                    <textarea name="reason" class="form-control" required>{{ $req->reason }}</textarea>
                                                </div>

                                                <div class="form-group mt-2">
                                                    <label>Status</label>
                                                    <select name="status" class="form-control">
                                                        <option {{ $req->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                        <option {{ $req->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                                                        <option {{ $req->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">Save Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Delete Modal --}}
                            <div class="modal fade" id="deleteModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <form method="POST" action="{{ route('leave.manual.delete', $req->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p>Are you sure you want to delete this request?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No leave requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('leave.manual.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-2">
                        <label>Leave Type</label>
                        <select name="leave_type_id" class="form-control" required>
                            <option value="">-- Select Leave Type --</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-2">
                        <label>Start Date</label>
                        <input type="date" name="start_date" min="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>

                    <div class="form-group mt-2">
                        <label>End Date</label>
                        <input type="date" name="end_date" min="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>

                    <div class="form-group mt-2">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection