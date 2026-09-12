<?php

namespace App\Features\Admin\Makers\Http\Controllers;

use App\Features\Makers\Actions\SaveMakerProfileContentAction;
use App\Features\Makers\Http\Requests\SaveMakerProfileContentRequest;
use App\Features\Auth\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;

final class SaveContentController extends Controller
{
    public function __invoke(
        SaveMakerProfileContentRequest $request,
        User $maker,
        int $slot,
        SaveMakerProfileContentAction $action,
    ): RedirectResponse {
        if (! $maker->hasRole(UserRole::Maker)) {
            throw (new ModelNotFoundException())->setModel(User::class, [$maker->getKey()]);
        }

        $action->execute(
            $maker,
            $slot,
            $request->file('media'),
            $request->validated('caption'),
        );

        return redirect()
            ->route('admin.makers.show', $maker)
            ->with('status', 'Maker content '.$slot.' saved successfully.');
    }
}
