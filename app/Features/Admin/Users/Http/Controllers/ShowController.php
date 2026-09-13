<?php

namespace App\Features\Admin\Users\Http\Controllers;

use App\Features\Admin\Users\Services\UserManagementService;
use App\Features\Auth\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

final class ShowController extends Controller
{
    public function __invoke(User $user, UserManagementService $users): View
    {
        $user->loadMissing('makerProfile');

        if ($user->role === UserRole::Appreciator) {
            $user->loadMissing([
                'appreciatorProfile',
                'savedArtworks.maker',
            ]);
        }

        return view('admin.users.show', [
            'user' => $user,
            'statuses' => $users->statuses(),
        ]);
    }
}
