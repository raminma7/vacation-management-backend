<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::all();
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

