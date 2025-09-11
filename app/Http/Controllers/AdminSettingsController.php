<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VacationLimit;
use App\Models\CompanyHoliday;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $limits = VacationLimit::first();
        $holidays = CompanyHoliday::orderBy('date')->get();
        return view('settings', compact('limits',  'holidays'));
    }

    public function updateLimits(Request $request)
    {
        $request->validate([
            'yearly_limit' => 'required|integer|min:0',
            'monthly_limit' => 'required|integer|min:0',
        ]);

        $limits = VacationLimit::first();
        if (!$limits) {
            $limits = new VacationLimit();
        }

        $limits->yearly_limit = $request->yearly_limit;
        $limits->monthly_limit = $request->monthly_limit;
        $limits->save();

        return redirect()->route('settings')->with('success', 'Vacation limits updated successfully!');
    }

    public function storeHoliday(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:company,national',
        ]);

        CompanyHoliday::create($request->only(['name', 'date', 'type']));

        return redirect()->back()->with('success', 'Holiday added successfully!');
    }

    public function destroyHoliday($id)
    {
        CompanyHoliday::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Holiday removed successfully!');
    }
}
