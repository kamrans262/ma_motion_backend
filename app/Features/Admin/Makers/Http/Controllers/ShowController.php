<?php

namespace App\Features\Admin\Makers\Http\Controllers;

use App\Features\Admin\Makers\Services\MakerManagementService;
use App\Features\Auth\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;

final class ShowController extends Controller
{
    public function __invoke(User $maker, MakerManagementService $makers): View
    {
        if (! $maker->hasRole(UserRole::Maker)) {
            throw (new ModelNotFoundException())->setModel(User::class, [$maker->getKey()]);
        }

        $maker->loadMissing('makerProfile.location');

        return view('admin.makers.show', [
            'maker' => $maker,
            'statuses' => $makers->statuses(),
            'locations' => $makers->locations(),
        ]);
    }
}
