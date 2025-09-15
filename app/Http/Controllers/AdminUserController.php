<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VacationRequest;
use Carbon\Carbon;
use App\Models\CompanyHoliday;

class AdminUserController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear  = Carbon::now()->year;

        $users = User::with(['vacationRequests' => function($query) use ($currentMonth, $currentYear) {
            $query->where('status', 'approved')
                ->where(function($q) use ($currentMonth, $currentYear) {
                    $q->where(function($q2) use ($currentMonth, $currentYear) {
                        $q2->whereNotNull('start_date')
                        ->whereYear('start_date', $currentYear)
                        ->whereMonth('start_date', $currentMonth);
                    })
                    ->orWhere(function($q3) use ($currentMonth, $currentYear) {
                        $q3->whereNull('start_date')
                        ->whereYear('date', $currentYear)
                        ->whereMonth('date', $currentMonth);
                    });
                });
        }])->get();
        
        $holidays = CompanyHoliday::pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString()) 
            ->toArray();

        foreach ($users as $user) {
            $totalHours = 0;

            foreach ($user->vacationRequests as $request) {
                if (is_null($request->start_time) && is_null($request->end_time)) {
                    $start = Carbon::parse($request->start_date ?? $request->date);
                    $end   = Carbon::parse($request->end_date ?? $request->date);

                    $days = 0;
                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        $currentDate = $date->toDateString();

                        if (!$date->isWeekend() && !in_array($currentDate, $holidays)) {
                            $days++;
                        }
                    }

                    $totalHours += $days * 8;
                } else {
                    $startTime = Carbon::parse($request->start_time);
                    $endTime   = Carbon::parse($request->end_time);
                    $totalHours += $endTime->diffInHours($startTime);
                }
            }

            $user->approved_hours = $totalHours;
            $user->is_over_limit  = $totalHours > 10;
        }

        return view('users', compact('users'));
    }

    public function updateVacation(Request $request, User $user)
    {
        $request->validate([
            'vacation_extra' => 'integer|min:0',
        ]);

        $user->vacation_extra = $request->vacation_extra;
        $user->save();

        return redirect()->back()->with('success', 'User vacation overrides updated successfully!');
    }
}

