<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->getAllExceptAuthUser();
        return response()->json(UserResource::collection($users));
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());
        return response()->json(new UserResource($user), 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return response()->json(new UserResource($user));
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $updated = $this->userService->update($user, $request->validated());
        return response()->json(new UserResource($updated));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user);
        return response()->json(['message' => 'User deleted successfully']);
    }
}