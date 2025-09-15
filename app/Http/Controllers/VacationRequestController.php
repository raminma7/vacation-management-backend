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

        $approvedRequests = $user->vacationRequests()
            ->where('status', 'approved')
            ->where('extra_hours_used', 0)
            ->get();

        $sumHoursForMonth = function($requests, $year, $month) use ($holidays) {
            $totalHours = 0;

            foreach ($requests as $req) {
                if ($req->start_date && $req->end_date) {
                    $start = Carbon::parse($req->start_date);
                    $end = Carbon::parse($req->end_date);
                    $period = CarbonPeriod::create($start, $end);

                    foreach ($period as $date) {
                        if ($date->year == $year && $date->month == $month) {
                            if (!$date->isWeekend() && !in_array($date->toDateString(), $holidays)) {
                                $totalHours += 8;
                            }
                        }
                    }
                } elseif ($req->date && $req->start_time && $req->end_time) {
                    $date = Carbon::parse($req->date);
                    if ($date->year == $year && $date->month == $month) {
                        if (!$date->isWeekend() && !in_array($date->toDateString(), $holidays)) {
                            $start = Carbon::parse($req->date . ' ' . $req->start_time);
                            $end = Carbon::parse($req->date . ' ' . $req->end_time);
                            $sixPm = Carbon::parse($req->date . ' 18:00:00');
                            if ($end > $sixPm) $end = $sixPm;
                            if ($end > $start) {
                                $duration = $start->diffInMinutes($end) / 60;
                                $totalHours += $duration;
                            }
                        }
                    }
                }
            }

            return $totalHours;
        };

        $requestedHoursPerMonth = [];

        if (isset($fields["start_date"]) && isset($fields["end_date"])) {
            $start = Carbon::parse($fields["start_date"]);
            $end = Carbon::parse($fields["end_date"]);
            $period = CarbonPeriod::create($start, $end);

            foreach ($period as $date) {
                if (!$date->isWeekend() && !in_array($date->toDateString(), $holidays)) {
                    $key = $date->format('Y-m');
                    $requestedHoursPerMonth[$key] = ($requestedHoursPerMonth[$key] ?? 0) + 8;
                }
            }

        } elseif ($fields["date"] && $fields["start_time"] && $fields["end_time"]) {
            $date = Carbon::parse($fields["date"]);
            if ($date->isWeekend() || in_array($date->toDateString(), $holidays)) {
                return response()->json(['message' => 'Requested day is not a working day'], 400);
            }

            $start = Carbon::parse($fields["date"] . ' ' . $fields["start_time"]);
            $end = Carbon::parse($fields["date"] . ' ' . $fields["end_time"]);

            $sixPm = Carbon::parse($fields["date"] . ' 18:00:00');

            if ($end  > $sixPm) {
                return response()->json(['message' => 'Requested time is outside working hours.'], 400);
            }

            $durationInMinutes = $start->diffInMinutes($end);
            $hours = $durationInMinutes / 60;
            $key = $date->format('Y-m');
            $requestedHoursPerMonth[$key] = ($requestedHoursPerMonth[$key] ?? 0) + $hours;
        } else {
            return response()->json(['message' => 'Invalid request. Provide either start_date & end_date, or date with start_time & end_time.'], 400);
        }

        $vacationExtra = $user->vacation_extra;

        foreach ($requestedHoursPerMonth as $monthKey => $requestedHoursInMonth) {
            [$year, $month] = explode('-', $monthKey);
            $approvedHoursInMonth = $sumHoursForMonth($approvedRequests, $year, $month);

            if (($approvedHoursInMonth + $requestedHoursInMonth) > $monthlyLimit) {
                if(($approvedHoursInMonth + $requestedHoursInMonth) <= $monthlyLimit + $vacationExtra){
                    $fields["extra_hours_used"] = $approvedHoursInMonth + $requestedHoursInMonth - $monthlyLimit;
                }else{
                    return response()->json([
                        'message' => "Monthly limit exceeded for {$monthKey}. Limit: {$monthlyLimit} hours."
                    ], 422);
                }

            }
        }

        $vacationRequest = $user->vacationRequests()->create($fields);

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

        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year); 
        if ($month < 1 || $month > 12) {
            return response()->json(['message' => 'Invalid month value. Must be between 1 and 12.'], 400);
        }

        $vacationLimits = VacationLimit::first();
        if (!$vacationLimits) {
            return response()->json(['message' => 'Vacation limits not set'], 404);
        }

        $monthlyLimit = $vacationLimits->monthly_limit;
        $yearlyLimit  = $vacationLimits->yearly_limit;

        $holidays = CompanyHoliday::pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

        $approvedRequests = $user->vacationRequests()
            ->where('status', 'approved')
            ->where("extra_hours_used", 0)
            ->get();

        $usedMonthly = 0;
        $usedYearly  = 0;
        $extraHoursUsed = 0;

        foreach ($approvedRequests as $req) {
            if (!empty($req->date) && !empty($req->start_time) && !empty($req->end_time)) {
                $date = Carbon::parse($req->date);
                $hours = ceil(Carbon::parse($req->end_time)->floatDiffInHours(Carbon::parse($req->start_time)));

                if ($date->year === $year) {
                    $usedYearly += $hours;
                    if ($date->month === $month) {
                        $usedMonthly += $hours;
                        $extraHoursUsed += $req->extra_hours_used ?? 0;
                    }
                }

            } elseif (!empty($req->start_date) && !empty($req->end_date)) {
                $startDate = Carbon::parse($req->start_date);
                $endDate   = Carbon::parse($req->end_date);
                $period = CarbonPeriod::create($startDate, $endDate);

                foreach ($period as $day) {
                    if (!in_array($day->format('Y-m-d'), $holidays) && !$day->isWeekend()) {
                        if ($day->year === $year) {
                            $usedYearly += 8;
                            if ($day->month === $month) {
                                $usedMonthly += 8;
                            }
                        }
                    }
                }

                if ($startDate->year == $year && $startDate->month == $month && $req->extra_hours_used) {
                    $extraHoursUsed += $req->extra_hours_used;
                }
            }
        }

        $remainingMonthly = max($monthlyLimit - ($usedMonthly - $extraHoursUsed), 0);
        $remainingYearly  = max($yearlyLimit - ($usedYearly - $extraHoursUsed), 0);

        return response()->json([
            'month'             => $month,
            'year'              => $year,
            'monthly_limit'     => $monthlyLimit,
            'used_monthly'      => $usedMonthly - $extraHoursUsed,
            'remaining_monthly' => $remainingMonthly,
            'yearly_limit'      => $yearlyLimit,
            'used_yearly'       => $usedYearly - $extraHoursUsed,
            'remaining_yearly'  => $remainingYearly,
            'extra_hours_used'  => $extraHoursUsed,
            'vacation_extra'    => $user->vacation_extra
        ], 200);
    }


} 