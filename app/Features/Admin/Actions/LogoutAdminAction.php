<?php

namespace App\Features\Admin\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LogoutAdminAction
{
    public function execute(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
