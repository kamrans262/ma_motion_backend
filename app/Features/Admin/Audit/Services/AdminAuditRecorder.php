<?php

namespace App\Features\Admin\Audit\Services;

use App\Features\Admin\Audit\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminAuditRecorder
{
    public function __construct(private readonly AuditRequestSummaryService $summary) {}

    public function record(Request $request, User $admin, Response $response): AdminAuditLog
    {
        [$targetType, $targetId] = $this->target($request);
        $routeName = $request->route()?->getName();

        return AdminAuditLog::query()->create([
            'admin_user_id' => $admin->id,
            'event' => $routeName ?: strtolower($request->method()).':'.$request->path(),
            'route_name' => $routeName,
            'method' => strtoupper($request->method()),
            'path' => $request->path(),
            'target_type' => $targetType,
            'target_id' => $targetId,
            'request_data' => $this->summary->summarize($request),
            'response_status' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ? substr($request->userAgent(), 0, 1000) : null,
        ]);
    }

    /** @return array{0: ?string, 1: ?string} */
    private function target(Request $request): array
    {
        $parameters = $request->route()?->parameters() ?? [];

        foreach ($parameters as $name => $value) {
            if ($value instanceof Model) {
                return [(string) $name, (string) $value->getKey()];
            }

            if (is_scalar($value) && ! in_array((string) $name, ['page'], true)) {
                return [(string) $name, (string) $value];
            }
        }

        return [null, null];
    }
}
