<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beneficiary;

class BeneficiaryController extends Controller
{
    public function index()
    {
        $beneficiaries = Beneficiary::with(['facility', 'program'])
            ->latest()
            ->paginate(20);
        return view('admin.beneficiaries.index', compact('beneficiaries'));
    }

    public function create()
    {
        return view('admin.beneficiaries.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'nin' => 'required|string|size:11|unique:beneficiaries',
            'gender' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string',
            'facility_id' => 'required|exists:facilities,id',
            'program_id' => 'required|exists:programs,id'
        ]);

        Beneficiary::create($request->all());

        return redirect()->route('admin.beneficiaries.index')
            ->with('success', 'Beneficiary created successfully.');
    }

    public function show(Beneficiary $beneficiary)
    {
        $beneficiary->load(['facility', 'program', 'spouse', 'children']);
        return view('admin.beneficiaries.show', compact('beneficiary'));
    }

    public function edit(Beneficiary $beneficiary)
    {
        return view('admin.beneficiaries.edit', compact('beneficiary'));
    }

    public function update(Request $request, Beneficiary $beneficiary)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string'
        ]);

        $beneficiary->update($request->all());

        return redirect()->route('admin.beneficiaries.show', $beneficiary)
            ->with('success', 'Beneficiary updated successfully.');
    }

    public function destroy(Beneficiary $beneficiary)
    {
        $beneficiary->delete();
        return redirect()->route('admin.beneficiaries.index')
            ->with('success', 'Beneficiary deleted successfully.');
    }
}
