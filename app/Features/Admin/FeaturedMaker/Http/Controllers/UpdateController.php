<?php

namespace App\Features\Admin\FeaturedMaker\Http\Controllers;

use App\Features\Admin\FeaturedMaker\Http\Requests\UpdateFeaturedMakerRequest;
use App\Features\Admin\FeaturedMaker\Services\FeaturedMakerManagementService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class UpdateController extends Controller
{
    public function __invoke(UpdateFeaturedMakerRequest $request, FeaturedMakerManagementService $service): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $service->update($data, $admin);

        return to_route('admin.featured-maker.index')
            ->with('status', 'Featured Maker updated successfully.');
    }
}
