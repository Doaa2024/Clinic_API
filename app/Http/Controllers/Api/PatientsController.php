<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientsResource;
use App\Models\Patients;
use Illuminate\Http\Request;

class PatientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patients = Patients::latest()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', $search);
            })
            ->get();

        return PatientsResource::collection($patients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'medical_history' => 'required',
            'dob' => 'required|date_format:Y-m-d',
            'phone' => 'required'
        ]);
        $patient = Patients::create($validatedData);
        return new PatientsResource($patient);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patients $patient)
    {
        return new PatientsResource($patient);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patients $patient)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:100',
            'medical_history' => 'sometimes',
            'dob' => 'sometimes|date_format:Y-m-d',
            'phone' => 'sometimes'
        ]);
        $patient->update($validatedData);
        return new PatientsResource($patient);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patients $patient)
    {
        $patient->delete();
        return response()->json(status: 204);
    }
}
