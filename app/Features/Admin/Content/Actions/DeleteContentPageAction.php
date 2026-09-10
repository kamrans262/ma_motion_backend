<?php

namespace App\Features\Admin\Content\Actions;

use App\Features\Content\Models\AppContentPage;
use Illuminate\Validation\ValidationException;

final class DeleteContentPageAction
{
    public function execute(AppContentPage $page): void
    {
        if ($page->is_system) {
            throw ValidationException::withMessages(['content' => 'Required Terms & Conditions and Privacy Policy pages cannot be deleted.']);
        }

        $page->delete();
    }
}
