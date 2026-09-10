<?php

namespace App\Features\Admin\Audit\Http\Controllers;

use App\Features\Admin\Audit\Http\Requests\AuditLogIndexRequest;
use App\Features\Admin\Audit\Services\AdminAuditLogService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class IndexController extends Controller
{
    public function __invoke(AuditLogIndexRequest $request, AdminAuditLogService $service): View
    {
        $filters = $request->validated();

        return view('admin.audit.index', [
            ...$service->metadata(),
            'auditLogs' => $service->paginate($filters),
            'filters' => $filters,
        ]);
    }
}
