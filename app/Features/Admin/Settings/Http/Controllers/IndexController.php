<?php

namespace App\Features\Admin\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class IndexController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('admin.settings.index', ['admin' => $request->user()]);
    }
}
