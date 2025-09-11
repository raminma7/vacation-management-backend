<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\VacationRequestCreated;
use App\Models\User;
use App\Models\CompanyHoliday;
use App\Models\VacationLimit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class VacationRequestController extends Controller
{
    public function createRequest(Request $request)
    {
        $fields = $request->validate([
            "start_date" => "nullable|date",
            "end_date"   => "nullable|date|after_or_equal:start_date",
            "date"       => "nullable|date",
            "start_time" => "nullable|date_format:H:i",
            "end_time"   => "nullable|date_format:H:i|after:start_time",
            "note"       => "nullable|string",
        ]);

        $user = $request->user();

        $vacationLimits = VacationLimit::first();
        if (!$vacationLimits) {
            return response()->json(['message' => 'Vacation limits not set'], 404);
        }
        $monthlyLimit = $vacationLimits->monthly_limit;

        $holidays = CompanyHoliday::pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        $approvedRequests = $user->vacationRequests()
            ->where('status', 'approved')
            ->get();

        $usedHoursThisMonth = 0;

        foreach ($approvedRequests as $req) {
            if (!empty($req->date) && !empty($req->start_time) && !empty($req->end_time)) {
                $date = Carbon::parse($req->date);
                if ($date->year === $currentYear && $date->month === $currentMonth ) {
                    $usedHoursThisMonth += ceil(Carbon::parse($req->end_time)->floatDiffInHours(Carbon::parse($req->start_time)));
                    if ($req->extra_hours_used) {
                        $usedHoursThisMonth -= $req->extra_hours_used;
                    }
                }
            } elseif (!empty($req->start_date) && !empty($req->end_date)) {
                $startDate = Carbon::parse($req->start_date);
                $endDate   = Carbon::parse($req->end_date);
                $period = CarbonPeriod::create($startDate, $endDate);

                foreach ($period as $day) {
                    if (!in_array($day->format('Y-m-d'), $holidays) && !$day->isWeekend()) {
                        if ($day->year === $currentYear && $day->month === $currentMonth) {
                            $usedHoursThisMonth += 8;
                            if ($req->extra_hours_used) {
                                $usedHoursThisMonth -= $req->extra_hours_used;
                            }
                        }
                    }
                }
            }
        }

        $requestedHours = 0;
        if (!empty($fields['date']) && !empty($fields['start_time']) && !empty($fields['end_time'])) {
            $requestedHours = abs(ceil(Carbon::parse($fields['end_time'])->floatDiffInHours(Carbon::parse($fields['start_time']))));
        } elseif (!empty($fields['start_date']) && !empty($fields['end_date'])) {
            $startDate = Carbon::parse($fields['start_date']);
            $endDate   = Carbon::parse($fields['end_date']);
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $day) {
                if (!in_array($day->format('Y-m-d'), $holidays) && !$day->isWeekend()) {
                    $requestedHours += 8;
                }
            }
        }

        $extraUsed = 0;
        $totalHours = $usedHoursThisMonth + $requestedHours;

        if ($totalHours > $monthlyLimit) {
            $excessHours = $totalHours - $monthlyLimit;
            $extraAvailable = $user->vacation_extra;
            if ($excessHours > $extraAvailable) {
                return response()->json(['message' => 'You are exceeding your monthly limit'], 400);
            }

            $extraUsed = $excessHours;
        }

        $vacationRequest = $user->vacationRequests()->create(array_merge(
            $fields,
            ['extra_hours_used' => $extraUsed]
        ));

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new VacationRequestCreated($vacationRequest));
        }

        return response()->json($vacationRequest, 201);
    }



    public function getUserRequests(Request $request)
    {
        $user = $request->user();

        $requests = $user->vacationRequests()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests, 200);
    }


    public function getCompanyHolidays(Request $request)
    {
        $holidays = CompanyHoliday::orderBy('date', 'asc')->pluck('date');

        return response()->json($holidays, 200);
    }
    

    public function cancelRequest($id, Request $request)
    {
        $vacation = $request->user()->vacationRequests()->findOrFail($id);

        if ($vacation->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be cancelled'], 400);
        }

        $vacation->update([
            'status' => 'cancelled',
        ]);

        return response()->json(['message' => 'Request cancelled successfully']);
    }

    public function getVacationBalance(Request $request)
    {
        $user = $request->user();

        $vacationLimits = VacationLimit::first();
        if (!$vacationLimits) {
            return response()->json(['message' => 'Vacation limits not set'], 404);
        }

        $monthlyLimit = $vacationLimits->monthly_limit;
        $yearlyLimit  = $vacationLimits->yearly_limit;

        $holidays = CompanyHoliday::pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

        $approvedRequests = $user->vacationRequests()
            ->where('status', 'approved')
            ->get();

        $usedMonthly = 0;
        $usedYearly  = 0;

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        foreach ($approvedRequests as $req) {
            if (!empty($req->date) && !empty($req->start_time) && !empty($req->end_time)) {
                $date = Carbon::parse($req->date);
                $hours = ceil(Carbon::parse($req->end_time)->floatDiffInHours(Carbon::parse($req->start_time)));

                if ($date->year === $currentYear) {
                    $usedYearly += $hours;
                    if ($date->month === $currentMonth) {
                        $usedMonthly += $hours;
                    }
                }

            } elseif (!empty($req->start_date) && !empty($req->end_date)) {
                $startDate = Carbon::parse($req->start_date);
                $endDate   = Carbon::parse($req->end_date);

                $period = CarbonPeriod::create($startDate, $endDate);

                foreach ($period as $day) {
                    if (!in_array($day->format('Y-m-d'), $holidays) && !$day->isWeekend()) {
                        if ($day->year === $currentYear) {
                            $usedYearly += 8;
                            if ($day->month === $currentMonth) {
                                $usedMonthly += 8;
                            }
                        }
                    }
                }
            }
        }

        $extraHoursUsed = 0;

        foreach ($approvedRequests as $req){
            if($req->extra_hours_used > 0){
                $extraHoursUsed += $req->extra_hours_used;
            }
        }

        $remainingMonthly = max($monthlyLimit - $usedMonthly, 0);
        $remainingYearly  = max($yearlyLimit - $usedYearly, 0);

        return response()->json([
            'monthly_limit'   => $monthlyLimit,
            'used_monthly'    => $usedMonthly - $extraHoursUsed,
            'remaining_monthly' => $remainingMonthly,
            'yearly_limit'    => $yearlyLimit,
            'used_yearly'     => $usedYearly - $extraHoursUsed,
            'remaining_yearly'=> $remainingYearly,
            'extra_hours_used' => $extraHoursUsed,
            'vacation_extra' => $user->vacation_extra
        ], 200);
    }

}
