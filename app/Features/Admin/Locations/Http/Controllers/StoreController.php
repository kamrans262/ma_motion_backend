<?php
namespace App\Features\Admin\Locations\Http\Controllers;
use App\Features\Admin\Locations\Actions\CreateLocationAction;
use App\Features\Admin\Locations\Actions\NormalizeLocationData;
use App\Features\Admin\Locations\Http\Requests\StoreLocationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class StoreController extends Controller {
 public function __invoke(StoreLocationRequest $request, CreateLocationAction $action, NormalizeLocationData $normalizer): RedirectResponse {
  $action->execute($request->validated(), $normalizer);
  return redirect()->route('admin.locations.index')->with('status','Location created successfully.');
 }
}
