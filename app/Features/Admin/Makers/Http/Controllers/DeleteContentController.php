<?php

namespace App\Features\Admin\Makers\Http\Controllers;

use App\Features\Makers\Actions\DeleteMakerProfileContentAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class DeleteContentController extends Controller
{
    public function __invoke(User $maker, int $slot, DeleteMakerProfileContentAction $action): RedirectResponse
    {
        $action->execute($maker, $slot);

        return redirect()
            ->route('admin.makers.show', $maker)
            ->with('status', 'Maker content '.$slot.' removed successfully.');
    }
}
