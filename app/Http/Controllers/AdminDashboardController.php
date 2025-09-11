<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VacationRequest;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalRequests = VacationRequest::count();
        $pendingRequests = VacationRequest::where('status', 'pending')->count();
        $approvedRequests = VacationRequest::where('status', 'approved')->count();
        $rejectedRequests = VacationRequest::where('status', 'rejected')->count();
        $notifications = auth()->user()->unreadNotifications;

        return view('dashboard', compact(
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'notifications'
        ));
    }
}
