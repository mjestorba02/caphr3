<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Claim;
use App\Models\LeaveType;
use App\Models\Timesheet;
use App\Models\TimeTracking;

class DashboardController extends Controller
{
    public function index()
    {
        // Summary numbers
        $totalEmployees   = User::count();
        $totalAttendance  = TimeTracking::count();
        $totalTimesheets  = Timesheet::count();
        $pendingClaims    = Claim::where('status', 'Pending')->count();
        $totalClaims      = Claim::count();

        // Recent data for snapshots
        $recentClaims = Claim::with('user')->latest()->take(5)->get();
        $leaveTypes   = LeaveType::all();

        // Data for charts
        $claimsByStatus = Claim::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $attendanceTrends = TimeTracking::selectRaw('DATE(date) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->take(7)
            ->get()
            ->reverse(); // show oldest → newest

        return view('dashboard', compact(
            'totalEmployees',
            'totalAttendance',
            'totalTimesheets',
            'pendingClaims',
            'recentClaims',
            'leaveTypes',
            'claimsByStatus',
            'attendanceTrends',
            'totalClaims'
        ));
    }
    public function search(\Illuminate\Http\Request $request)
    {
        $query = $request->input('q');
        $users = User::where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhere('department', 'like', "%$query%")
            ->orWhere('position', 'like', "%$query%")
            ->get();
        return view('search-results', compact('query', 'users'));
    }
}