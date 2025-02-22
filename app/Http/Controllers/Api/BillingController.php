<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillingResource;
use App\Models\Billing;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $billings = Billing::with(['appointment', 'appointment.patient']);
        if ($request->has('filter')) {
            $billings = $billings->where('payment_status', $request->filter);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $billings = $billings->where(function ($query) use ($search) {
                $query->where('id', $search)->orWhere('appointment_id', $search)->orWhere('payment_status', $search);
            });
        }
        return BillingResource::collection($billings->latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'appointment_id' => 'required|exists:appointments,id|unique:billings,appointment_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_status' => 'sometimes|in:paid,pending,overdue'
        ]);
        $billing = Billing::create($validatedData);
        return new BillingResource($billing);
    }

    /**
     * Display the specified resource.
     */
    public function show(Billing $billing)
    {
        return new BillingResource($billing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Billing $billing)
    {
        $validatedData = $request->validate([
            'appointment_id' => 'sometimes|exists:appointments,id|unique:billings,appointment_id,' . $billing->id,
            'amount' => 'sometimes|numeric|min:0.00',
            'payment_status' => 'sometimes|in:paid,pending,overdue'
        ]);
        $billing->update($validatedData);
        return new BillingResource($validatedData);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Billing $billing)
    {
        $billing->delete();
        return response()->json(status: 204);
    }
}
