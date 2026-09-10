<?php

namespace App\Features\Saves\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['appreciator_id', 'maker_id'])]
final class MakerSave extends Model
{
    /** @return BelongsTo<User, $this> */
    public function appreciator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appreciator_id');
    }

    /** @return BelongsTo<User, $this> */
    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }
}
