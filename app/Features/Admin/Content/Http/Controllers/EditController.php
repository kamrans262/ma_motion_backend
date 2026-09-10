<?php

namespace App\Features\Admin\Content\Http\Controllers;

use App\Features\Content\Models\AppContentPage;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class EditController extends Controller
{
    public function __invoke(AppContentPage $contentPage): View
    {
        return view('admin.content.edit', ['page' => $contentPage]);
    }
}
