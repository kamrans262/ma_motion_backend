<?php
namespace App\Features\Admin\Locations\Http\Controllers;
use App\Features\Locations\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
final class EditController extends Controller {
 public function __invoke(Location $location): View { return view('admin.locations.edit',['location'=>$location]); }
}
