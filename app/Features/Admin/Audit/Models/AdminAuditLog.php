<?php

namespace App\Features\Admin\Audit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'admin_user_id',
    'event',
    'route_name',
    'method',
    'path',
    'target_type',
    'target_id',
    'request_data',
    'response_status',
    'ip_address',
    'user_agent',
])]
final class AdminAuditLog extends Model
{
    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'response_status' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
