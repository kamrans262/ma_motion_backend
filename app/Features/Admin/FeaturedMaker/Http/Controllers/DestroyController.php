<?php

namespace App\Features\Admin\FeaturedMaker\Http\Controllers;

use App\Features\Admin\FeaturedMaker\Services\FeaturedMakerManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class DestroyController extends Controller
{
    public function __invoke(FeaturedMakerManagementService $service): RedirectResponse
    {
        $service->remove();

        return to_route('admin.featured-maker.index')
            ->with('status', 'Featured Maker removed successfully.');
    }
}
