<?php

namespace App\Features\Admin\Settings\Http\Controllers;

use App\Features\Admin\Settings\Actions\UpdateAdminProfileAction;
use App\Features\Admin\Settings\Http\Requests\UpdateAdminProfileRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class UpdateProfileController extends Controller
{
    public function __invoke(UpdateAdminProfileRequest $request, UpdateAdminProfileAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return back()->with('status', 'Admin profile updated successfully.');
    }
}
