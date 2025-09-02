<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        $vacationRequest = $request->user()->vacationRequests()->create($fields);
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
}
