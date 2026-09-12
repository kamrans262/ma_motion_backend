<?php

namespace App\Features\Admin\Makers\Http\Controllers;

use App\Features\Account\Actions\DeleteMakerProfileImageAction;
use App\Features\Auth\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;

final class DeleteProfileImageController extends Controller
{
    public function __invoke(User $maker, DeleteMakerProfileImageAction $action): RedirectResponse
    {
        if (! $maker->hasRole(UserRole::Maker)) {
            throw (new ModelNotFoundException())->setModel(User::class, [$maker->getKey()]);
        }

        $action->execute($maker);

        return redirect()
            ->route('admin.makers.show', $maker)
            ->with('status', 'Maker salon image removed successfully.');
    }
}
