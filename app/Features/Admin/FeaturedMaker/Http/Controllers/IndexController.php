<?php

namespace App\Features\Admin\FeaturedMaker\Http\Controllers;

use App\Features\Admin\FeaturedMaker\Http\Requests\FeaturedMakerIndexRequest;
use App\Features\Admin\FeaturedMaker\Services\FeaturedMakerManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class IndexController extends Controller
{
    public function __invoke(FeaturedMakerIndexRequest $request, FeaturedMakerManagementService $service): View
    {
        $setting = $service->setting();
        $requestedMakerId = $request->validated('maker_id');
        $defaultMakerId = $setting->maker?->isActive() ? (int) $setting->maker_id : null;
        $selectedMaker = $service->selectableMaker($requestedMakerId ? (int) $requestedMakerId : $defaultMakerId);
        $artworks = $service->eligibleArtworks($selectedMaker);

        return view('admin.featured-maker.index', [
            'setting' => $setting,
            'makers' => $service->makers(),
            'selectedMaker' => $selectedMaker,
            'artworks' => $artworks,
        ]);
    }
}
