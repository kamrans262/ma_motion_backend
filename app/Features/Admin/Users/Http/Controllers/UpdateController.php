<?php

namespace App\Features\Admin\Users\Http\Controllers;

use App\Features\Admin\Users\Actions\UpdateUserAction;
use App\Features\Admin\Users\Http\Requests\UpdateUserRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class UpdateController extends Controller
{
    public function __invoke(UpdateUserRequest $request, User $user, UpdateUserAction $action): RedirectResponse
    {
        $action->execute($request->user(), $user, $request->validated());

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'User account updated successfully.');
    }
}
