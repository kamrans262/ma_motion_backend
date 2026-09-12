<?php

namespace App\Features\Admin\Makers\Http\Controllers;

use App\Features\Account\Actions\UpdateMakerProfileImageAction;
use App\Features\Account\Http\Requests\UpdateProfileImageRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class UpdateProfileImageController extends Controller
{
    public function __invoke(
        UpdateProfileImageRequest $request,
        User $maker,
        UpdateMakerProfileImageAction $action,
    ): RedirectResponse {
        $action->execute($maker, $request->file('image'));

        return redirect()
            ->route('admin.makers.show', $maker)
            ->with('status', 'Maker salon image updated successfully.');
    }
}
