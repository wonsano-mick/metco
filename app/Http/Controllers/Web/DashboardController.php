<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // User dashboard
    public function index()
    {
        return view('dashboard');
    }

    // Admin dashboard
    public function adminDashboard()
    {
        // Get dashboard statistics for admin
        $stats = [
            'total_users' => \App\Models\User::count(),
            'active_users' => \App\Models\User::where('is_active', true)->count(),
            'total_accounts' => \App\Models\Eloquent\Account::count(),
            'active_accounts' => \App\Models\Eloquent\Account::where('status', 'active')->count(),
            'total_transactions' => \App\Models\Eloquent\Transaction::count(),
            'today_transactions' => \App\Models\Eloquent\Transaction::whereDate('created_at', today())->count(),
            'pending_transactions' => \App\Models\Eloquent\Transaction::where('status', 'pending')->count(),
            'total_balance' => \App\Models\Eloquent\Account::sum('current_balance'),
        ];

        // Recent activities
        $recentActivities = \App\Models\Eloquent\AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent users
        $recentUsers = \App\Models\User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivities', 'recentUsers'));
    }

    // Reports
    public function reports()
    {
        return view('reports.index');
    }

    public function monthlyReport()
    {
        // Generate monthly report data
        return view('reports.monthly');
    }

    public function yearlyReport()
    {
        // Generate yearly report data
        return view('reports.yearly');
    }
}
