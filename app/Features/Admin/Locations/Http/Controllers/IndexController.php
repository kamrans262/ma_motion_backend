<?php
namespace App\Features\Admin\Locations\Http\Controllers;
use App\Features\Admin\Locations\Http\Requests\LocationIndexRequest;
use App\Features\Admin\Locations\Services\LocationManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
final class IndexController extends Controller {
 public function __invoke(LocationIndexRequest $request, LocationManagementService $locations): View {
  $filters=$request->validated();
  return view('admin.locations.index',['locations'=>$locations->paginate($filters),'filters'=>$filters]);
 }
}
