@extends('layouts.app')

@section('title', 'HR Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">HR Dashboard</h4>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Total Employees</h6>
                    <h3>{{ $totalEmployees }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Attendance Records</h6>
                    <h3>{{ $totalAttendance }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Timesheets</h6>
                    <h3>{{ $totalTimesheets }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Pending Claims</h6>
                    <h3>{{ $pendingClaims }}</h3>
                </div>
            </div>
        </div>

        {{-- Claims Snapshot --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5 class="mb-3">Recent Claims & Reimbursements</h5>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Claim ID</th>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Claim Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentClaims as $claim)
                            <tr>
                                <td>{{ $claim->claim_id }}</td>
                                <td>{{ $claim->user->name }}</td>
                                <td>{{ $claim->user->position }}</td>
                                <td>{{ $claim->claim_date }}</td>
                                <td>₱{{ number_format($claim->claim_amount, 2) }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($claim->status) {
                                            'Pending' => 'warning',
                                            'Denied'  => 'danger',
                                            default   => 'success',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">
                                        {{ $claim->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No recent claims</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <a href="{{ route('claims.index') }}" class="btn btn-primary btn-sm">View All Claims</a>
            </div>
        </div>

        {{-- Leave Types Snapshot --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5 class="mb-3">Leave Types & Default Credits</h5>
                <div class="row">
                    @foreach($leaveTypes as $leave)
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 shadow-sm p-3 text-center">
                                <h6 class="mb-1">{{ $leave->name }}</h6>
                                <p class="text-muted mb-0">Default Credits: 
                                    <strong>{{ $leave->default_credits }}</strong>
                                </p>
                            </div>
                        </div>
                    @endforeach
                    @if($leaveTypes->isEmpty())
                        <p class="text-muted">No leave types defined.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Visual Summary --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h5>Claims by Status</h5>
                        @foreach($claimsByStatus as $status => $count)
                            <div class="mb-2">
                                <span class="badge badge-{{ $status == 'Pending' ? 'warning' : ($status == 'Denied' ? 'danger' : 'success') }}">{{ $status }}</span>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $status == 'Pending' ? 'warning' : ($status == 'Denied' ? 'danger' : 'success') }}" role="progressbar" style="width: {{ $totalClaims > 0 ? round(($count/$totalClaims)*100,2) : 0 }}%" aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $totalClaims }}">
                                        {{ $count }} ({{ $totalClaims > 0 ? round(($count/$totalClaims)*100,2) : 0 }}%)
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($claimsByStatus->isEmpty())
                            <p class="text-muted">No claims data available.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h5>Attendance (Last 7 Days)</h5>
                        <ul class="list-group">
                            @foreach($attendanceTrends as $trend)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $trend->day }}</span>
                                    <span class="badge badge-primary badge-pill">{{ $trend->count }}</span>
                                </li>
                            @endforeach
                            @if($attendanceTrends->isEmpty())
                                <li class="list-group-item text-muted">No attendance data available.</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Chart) {
            // Claims by status (Pie Chart)
            var claimsLabels = {!! json_encode($claimsByStatus->keys()) !!};
            var claimsData = {!! json_encode($claimsByStatus->values()) !!};
            var claimsChartElem = document.getElementById('claimsChart');
            if (claimsChartElem && claimsLabels.length && claimsData.length) {
                var claimsCtx = claimsChartElem.getContext('2d');
                new Chart(claimsCtx, {
                    type: 'pie',
                    data: {
                        labels: claimsLabels,
                        datasets: [{
                            data: claimsData,
                            backgroundColor: ['#ffc107','#dc3545','#28a745']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            } else {
                console.warn('Claims chart: No data or element found.');
            }

            // Attendance Trends (Bar Chart)
            var attendanceLabels = {!! json_encode($attendanceTrends->pluck('day')) !!};
            var attendanceData = {!! json_encode($attendanceTrends->pluck('count')) !!};
            var attendanceChartElem = document.getElementById('attendanceChart');
            if (attendanceChartElem && attendanceLabels.length && attendanceData.length) {
                var attendanceCtx = attendanceChartElem.getContext('2d');
                new Chart(attendanceCtx, {
                    type: 'bar',
                    data: {
                        labels: attendanceLabels,
                        datasets: [{
                            label: 'Attendance Records',
                            data: attendanceData,
                            backgroundColor: '#007bff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Day' } },
                            y: { title: { display: true, text: 'Records' }, beginAtZero: true }
                        }
                    }
                });
            } else {
                console.warn('Attendance chart: No data or element found.');
            }
        } else {
            console.error('Chart.js is not loaded.');
        }
    });
</script>
@endpush