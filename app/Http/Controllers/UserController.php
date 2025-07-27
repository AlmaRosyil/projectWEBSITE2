<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;


class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /** GET /api/users */
    public function index()
    {
        $fields = ['id', 'name', 'email', 'photo', 'phone'];
        $users  = $this->userService->getAll($fields ?: ['*']);

        return response()->json(UserResource::collection($users));
    }

    /** GET /api/users/{id} */
    public function show(int $id)
    {
        $fields = ['id', 'name', 'email', 'photo', 'phone'];
        $user   = $this->userService->getById($id, $fields ?: ['*']);

        return response()->json(new UserResource($user));
    }

    /** POST /api/users */
    public function store(UserRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return response()->json(new UserResource($user), 201);
    }

    /** PUT/PATCH /api/users/{id} */
    public function update(UserRequest $request, int $id)
    {
        $user = $this->userService->update($id, $request->validated());

        return response()->json(new UserResource($user));
    }

    /** DELETE /api/users/{id} */
    public function destroy(int $id)
    {
        $this->userService->delete($id);

        return response()->json(['message' => 'User deleted successfully.']);
    }

    /* ------------------------------------------------------------------
     |  ASSIGN ROLE (Spatie Laravel‑Permission)
     |------------------------------------------------------------------*/

    /** POST /api/users/{user}/roles */
    public function assignRole(Request $request, User $user)   // ← tipe User
    {
        $data = $request->validate([
            'role' => ['required','string', Rule::exists('roles','name')],
        ]);

        $user->assignRole($data['role']);          // Spatie method

        return response()->json([
            'message' => 'Role assigned successfully.',
            'data'    => new UserResource($user->load('roles')),
        ], 201);
    }
}
