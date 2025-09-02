<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getUserInfo(Request $request)
    {
        $user = $request->user();
        return response()->json($user);
    }
}
