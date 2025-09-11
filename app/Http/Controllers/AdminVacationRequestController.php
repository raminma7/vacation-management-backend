<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VacationRequest;

class AdminVacationRequestController extends Controller
{
    public function index()
    {
        $requests = VacationRequest::with('user')->get();
        return view('requests', compact('requests'));
    }

    public function update(Request $request, VacationRequest $requestModel)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string',
        ]);

        $requestModel->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
        ]);

        if ($request->status === 'approved' && $requestModel->extra_hours_used > 0) {
            $user = $requestModel->user;
            $user->vacation_extra = max(($user->vacation_extra - $requestModel->extra_hours_used), 0);
            $user->save();
        }

        return redirect()->back()->with('success', 'Request updated successfully.');
    }

}
