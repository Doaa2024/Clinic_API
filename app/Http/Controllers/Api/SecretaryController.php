<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Resources\SecretaryResource;
use App\Http\Resources\UserResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use \App\Models\User;
use Illuminate\Support\Facades\Gate;

class SecretaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);
        $secretaries = User::where('role', 'secretary');
        if ($request->has('search')) {
            $search = $request->search;
            $secretaries = $secretaries->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', $search);
            });
        }
        return SecretaryResource::collection($secretaries->get());
    }

    /**
     * Store a newly created resource in storage.
     */ public function store(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'name' => 'required|string|max:50',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|in:secretary,doctor',
                'password' => 'required|min:6',
            ]);

            // Hash the password before storing
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Create the user
            $secretary = User::create($validatedData);

            // Return the new resource
            return new SecretaryResource($secretary);
        } catch (ValidationException $e) {
            // Return validation error messages as JSON
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */    public function show(User $secretary)
    {
        if ($secretary->role == 'secretary') {
            return new SecretaryResource($secretary);
        }
        return response()->json(['message' => 'Secretary not founded'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $secretary)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|unique:users,email,' . $secretary->id,
            'role' => 'sometimes|in:secretary,doctor',
            'password' => 'sometimes|min:6',
        ]);
        // Hash the password before storing
        $updatedData = $secretary->update($validatedData);
        return new SecretaryResource($validatedData);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $secretary)
    {
        $secretary->delete();
        return response()->json(status: '204');
    }
}
