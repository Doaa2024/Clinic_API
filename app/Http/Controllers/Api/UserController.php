<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Doctors;
use Illuminate\Http\Request;
use \App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::where('role', 'doctor')->with('doctors');
        if ($request->has('search')) {
            $search = $request->search;
            $users = $users->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')->orWhere('id', $search);
            });
        }
        return UserResource::collection($users->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', User::class);
        $validatedData = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:secretary,doctor',
            'password' => 'required|min:6',
            'specialty' => 'required_if:role,doctor|string|max:255',
            'availability' => 'required_if:role,doctor|json',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
            'password' => bcrypt($validatedData['password']),
        ]);

        if ($validatedData['role'] === 'doctor') {
            Doctors::create([
                'doctor_id' => $user->id,
                'specialty' => $validatedData['specialty'],
                'availability' => $validatedData['availability'] ?? null,
            ]);
        }
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    { {
            if ($user->role == 'doctor') {
                return new UserResource($user->load('doctors'));
                //use load instead of with in show, cuz u have already a fetched user
            }
            return response()->json(['message' => 'Doctor not founded'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('update', User::class);
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:50',
            'password' => 'sometimes|min:6',
            'email' => 'sometimes|email|unique:users,email,' . $user->id, //this is used add the id of the user in the validation
            'role' => 'sometimes|in:secretary,doctor',
            'specialty' => 'sometimes|required_if:role,doctor|string|max:255', //sometimes is used to ensure that we do not want always to update all the fields
            'availability' => 'sometimes|nullable|required_if:role,doctor|json'
        ]);

        // Update user details
        if (isset($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        $user->update($validatedData);

        // Update doctor's info if the user is a doctor
        if ($user->role === 'doctor' && $user->doctors) {
            $user->doctors->update([
                'specialty' => $validatedData['specialty'] ?? $user->doctors->specialty, // this is used to set the value if the sepcaility is not included in the request so that it is not null
                'availability' => $validatedData['availability'] ?? $user->doctors->availability,
            ]);
        }

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', User::class);
        $user->delete();
        return response()->json(status: '204');
    }
}
