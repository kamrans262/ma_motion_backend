<?php

namespace App\Features\Admin\Makers\Http\Controllers;

use App\Features\Admin\Makers\Actions\UpdateMakerAction;
use App\Features\Admin\Makers\Http\Requests\UpdateMakerRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class UpdateController extends Controller
{
    public function __invoke(UpdateMakerRequest $request, User $maker, UpdateMakerAction $action): RedirectResponse
    {
        $action->execute($maker, $request->validated());

        return redirect()
            ->route('admin.makers.show', $maker)
            ->with('status', 'Maker profile updated successfully.');
    }
}
