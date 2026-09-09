<?php
namespace App\Features\Admin\Locations\Http\Controllers;
use App\Features\Admin\Locations\Actions\DeleteLocationAction;
use App\Features\Locations\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class DestroyController extends Controller {
 public function __invoke(Location $location, DeleteLocationAction $action): RedirectResponse {
  $action->execute($location); return redirect()->route('admin.locations.index')->with('status','Location deleted safely.');
 }
}
