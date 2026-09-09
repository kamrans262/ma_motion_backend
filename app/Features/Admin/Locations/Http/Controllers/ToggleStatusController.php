<?php
namespace App\Features\Admin\Locations\Http\Controllers;
use App\Features\Admin\Locations\Actions\ToggleStatusLocationAction;
use App\Features\Locations\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class ToggleStatusController extends Controller {
 public function __invoke(Location $location, ToggleStatusLocationAction $action): RedirectResponse {
  $action->execute($location); return back()->with('status','Location status updated successfully.');
 }
}
