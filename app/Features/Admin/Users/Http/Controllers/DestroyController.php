<?php

namespace App\Features\Admin\Users\Http\Controllers;

use App\Features\Admin\Users\Actions\DeleteUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class DestroyController extends Controller
{
    public function __invoke(User $user, DeleteUserAction $action): RedirectResponse
    {
        $action->execute($user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User account deleted successfully.');
    }
}
