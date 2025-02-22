<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentsResource;
use App\Models\Appointments;
use App\Models\Billing;
use App\Models\Doctors;
use App\Models\Prescriptions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $appointments = Appointments::with(['billing', 'prescription', 'patient', 'doctor.user']);
        if ($request->has('filter')) {
            $filter = $request->filter;
            $appointments = $appointments->where('status', $filter);
        }
        if ($request->has('Date')) {
            $date = $request->Date;
            $appointments = $appointments->where('date', $date);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $appointments = $appointments->where(function ($query) use ($search) {
                $query->where('patient_id', $search)->orWhere('id', $search)->orWhere('doctor_id', $search)->orWhere('date', $search);
            });
            if ($request->has('filter')) {
                $filter = $request->filter;
                $appointments = $appointments->where('status', $filter);
            }
        }
        return AppointmentsResource::collection($appointments->latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'status' => 'required|in:scheduled,completed,cancelled'
        ]);
        $validatedData['created_by'] = Auth::user()->id;
        $doctorInfo = Doctors::find($validatedData['doctor_id']);
        $availability = json_decode($doctorInfo->availability, true);
        if (!$this->isAvailable($availability, $validatedData['date'], $validatedData['time'])) {
            return response()->json(['error' => 'Doctor is not available at the requested time.'], 400);
        }
        $isTimeConflict = Appointments::where('doctor_id', $validatedData['doctor_id'])->where('date', $validatedData['date'])->where('time', $validatedData['time'])->exists();
        if ($isTimeConflict) {
            return response()->json(['error' => 'Doctor already has an appointment at the requested time.'], 400);
        }
        $isTimeConflictForSamePatient = Appointments::where('patient_id', $validatedData['patient_id'])->where('date', $validatedData['date'])->where('time', $validatedData['time'])->exists();
        if ($isTimeConflictForSamePatient) {
            return response()->json(['error' => 'Patient already has another appointment at the requested time.'], 400);
        }
        $data = Appointments::create($validatedData);
        $billing = Billing::create([
            "appointment_id" => $data->id,
            "amount" => 0
        ]);
        $prescription = Prescriptions::create([
            "appointment_id" => $data->id,
            "doctor_id" => $data->doctor_id,
            "medication" => [
                ["name" => "", "dose" => "", "frequency" => ""],
            ],
            "notes" => "No Available Notes"
        ]);
        return new AppointmentsResource($data);
    }
    private function isAvailable($availability, $date, $time)
    {
        // Check if the doctor is available for the given date and time
        $date = Carbon::parse($date);
        $dayOfWeek = $date->format('l'); // Get the full name of the day (e.g., "Monday", "Tuesday")

        if (isset($availability[$dayOfWeek])) {
            // Get the time range string for the day (e.g., "09:33 AM - 09:54 AM")
            $timeSlot = $availability[$dayOfWeek];

            // Split the time range into start and end times
            list($startTime, $endTime) = explode(' - ', $timeSlot);

            // Remove all alphabetic characters (AM/PM) from the start and end time
            $cleanedStartTime = preg_replace('/[a-zA-Z]/', '', $startTime);
            $cleanedEndTime = preg_replace('/[a-zA-Z]/', '', $endTime);

            // Trim any leading/trailing spaces (optional)
            $cleanedStartTime = trim($cleanedStartTime);
            $cleanedEndTime = trim($cleanedEndTime);

            // Convert the cleaned start, end, and requested time into 24-hour format
            $startTimeCarbon = Carbon::createFromFormat('H:i', $cleanedStartTime);
            $endTimeCarbon = Carbon::createFromFormat('H:i', $cleanedEndTime);
            $requestedTimeCarbon = Carbon::createFromFormat('H:i', $time);

            // Check if the requested appointment time is between the start and end times
            return $requestedTimeCarbon->between($startTimeCarbon, $endTimeCarbon, true); // Include end time in the check
        }

        return false; // Return false if no availability is found for the given day
    }



    /**
     * Display the specified resource.
     */
    public function show(Appointments $appointment)
    {
        return new AppointmentsResource($appointment->load(['billing', 'prescription']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointments $appointment)
    {
        $validatedData = $request->validate([
            'doctor_id' => 'sometimes|required|exists:doctors,id',
            'patient_id' => 'sometimes|required|exists:patients,id',
            'date' => 'sometimes|required|date_format:Y-m-d|after_or_equal:today',
            'time' => 'sometimes|required|date_format:H:i',
            'status' => 'sometimes|in:scheduled,completed,cancelled'
        ]);
        if (!empty($validatedData['time'])) {
            // Remove any unexpected characters (e.g., AM/PM, spaces)
            $cleanTime = preg_replace('/[^0-9:]/', '', $validatedData['time']);
            $validatedData['time'] = Carbon::createFromFormat('H:i', trim($cleanTime))->format('H:i');
        } else {
            // If time is not provided, use the existing appointment time
            $cleanTime = preg_replace('/[^0-9:]/', '', $appointment->time);
            $appointmentTime = Carbon::createFromFormat('H:i:s', trim($cleanTime))->format('H:i'); // Adjusted to handle SQL time format
        }


        if (isset($validatedData['doctor_id'])) {
            $doctorInfo = Doctors::find($validatedData['doctor_id']);
            $availability = json_decode($doctorInfo->availability, true);
            if (!$this->isAvailable($availability, $validatedData['date'] ?? $appointment->date, $validatedData['time'] ?? $appointmentTime)) {
                return response()->json(['error' => 'Doctor is not available at the requested time.'], 400);
            }
        }
        if (isset($validatedData['doctor_id'])) {
            $isTimeConflict = Appointments::where('doctor_id', $validatedData['doctor_id'] ?? $appointment->doctor_id)->where('date', $validatedData['date'] ?? $appointment->date)->where('time', $validatedData['time'] ?? $appointmentTime)->where('id', '!=', $appointment->id) // Exclude the current appointment
                ->exists();

            if ($isTimeConflict) {
                return response()->json(['error' => 'Doctor already has an appointment at the requested time.'], 400);
            }
        }
        if (isset($validatedData['patient_id'])) {
            $isTimeConflictForSamePatient = Appointments::where('patient_id', $validatedData['patient_id'])->where('date', $validatedData['date'] ?? $appointment->date)->where('time', $validatedData['time'] ?? $appointmentTime)->where('id', '!=', $appointment->id)->exists();
            if ($isTimeConflictForSamePatient) {
                return response()->json(['error' => 'Patient already has another appointment at the requested time.'], 400);
            }
        }
        $appointment->update($validatedData);
        return new AppointmentsResource($validatedData);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointments $appointment)
    {
        $appointment->delete();
        return response()->json(status: 204);
    }
    public function getDoctorsAppointment(Request $request)
    {
        $doctor_id = Auth::user()->id;

        $appointments = Appointments::where('doctor_id', $doctor_id)
            ->where('date', $request->date)
            ->with(['patient', 'doctor.user'])
            ->latest()
            ->get();

        return AppointmentsResource::collection($appointments);
    }
}
