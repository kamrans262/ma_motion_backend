<?php
namespace App\Features\Admin\Locations\Http\Controllers;
use App\Features\Admin\Locations\Actions\NormalizeLocationData;
use App\Features\Admin\Locations\Actions\UpdateLocationAction;
use App\Features\Admin\Locations\Http\Requests\UpdateLocationRequest;
use App\Features\Locations\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class UpdateController extends Controller {
 public function __invoke(UpdateLocationRequest $request, Location $location, UpdateLocationAction $action, NormalizeLocationData $normalizer): RedirectResponse {
  $action->execute($location,$request->validated(),$normalizer);
  return redirect()->route('admin.locations.edit',$location)->with('status','Location updated successfully.');
 }
}
