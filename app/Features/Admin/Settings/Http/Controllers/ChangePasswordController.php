<?php

namespace App\Features\Admin\Settings\Http\Controllers;

use App\Features\Admin\Settings\Actions\ChangeAdminPasswordAction;
use App\Features\Admin\Settings\Http\Requests\ChangeAdminPasswordRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class ChangePasswordController extends Controller
{
    public function __invoke(ChangeAdminPasswordRequest $request, ChangeAdminPasswordAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return back()->with('status', 'Password changed successfully. API sessions were revoked.');
    }
}
