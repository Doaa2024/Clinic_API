<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrescriptionsResource;
use App\Models\Prescriptions;
use Illuminate\Http\Request;

class PrescriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prescriptions = Prescriptions::latest()->with(['doctor.user', 'appointment.patient']);
        return PrescriptionsResource::collection($prescriptions->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'appointment_id' => 'required|exists:appointments,id|unique:prescriptions,appointment_id',
            'doctor_id' => 'required|exists:doctors,id',
            'notes' => 'nullable|text',
            'medication' => 'required|json'
        ]);
        $prescription = Prescriptions::create($validatedData);
        return new PrescriptionsResource($prescription);
    }

    /**
     * Display the specified resource.
     */
    public function show(Prescriptions $prescription)
    {
        return new PrescriptionsResource($prescription);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prescriptions $prescription)
    {
        $validatedData = $request->validate([
            'appointment_id' => 'sometimes|exists:appointments,id|unique:prescriptions,appointment_id,' . $prescription->id,
            'doctor_id' => 'sometimes|exists:doctors,id',
            'notes' => 'nullable',
            'medication' => 'sometimes|json'
        ]);
        $prescription->update($validatedData);
        return new PrescriptionsResource($validatedData);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prescriptions $prescription)
    {
        $prescription->delete();
        return response()->json(status: 204);
    }
}
