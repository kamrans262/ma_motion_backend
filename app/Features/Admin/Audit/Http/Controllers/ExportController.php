<?php

namespace App\Features\Admin\Audit\Http\Controllers;

use App\Features\Admin\Audit\Http\Requests\AuditLogIndexRequest;
use App\Features\Admin\Audit\Services\AdminAuditLogService;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportController extends Controller
{
    public function __invoke(AuditLogIndexRequest $request, AdminAuditLogService $service): StreamedResponse
    {
        $rows = $service->export($request->validated());
        $filename = 'ma-motion-audit-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Timestamp', 'Admin', 'Admin Email', 'Event', 'Method', 'Path', 'Target Type', 'Target ID', 'HTTP Status', 'IP Address'], ',', '"', '');

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->created_at?->toIso8601String(),
                    $row->admin?->name ?? 'Deleted admin',
                    $row->admin?->email,
                    $row->event,
                    $row->method,
                    $row->path,
                    $row->target_type,
                    $row->target_id,
                    $row->response_status,
                    $row->ip_address,
                ], ',', '"', '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
