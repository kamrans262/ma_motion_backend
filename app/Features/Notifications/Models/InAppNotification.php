<?php

namespace App\Features\Notifications\Models;

use App\Features\Notifications\Enums\NotificationType;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'recipient_id',
    'maker_id',
    'show_id',
    'type',
    'title',
    'body',
    'data',
    'dedupe_key',
    'read_at',
])]
final class InAppNotification extends Model
{
    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'data' => 'array',
            'read_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /** @return BelongsTo<User, $this> */
    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    /** @return BelongsTo<Show, $this> */
    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
