<?php

namespace App\Features\Admin\Analytics\Http\Controllers;

use App\Features\Admin\Analytics\Http\Requests\AnalyticsIndexRequest;
use App\Features\Admin\Analytics\Services\AdminAnalyticsService;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportController extends Controller
{
    public function __invoke(AnalyticsIndexRequest $request, AdminAnalyticsService $service): StreamedResponse
    {
        $report = $service->report($request->days());
        $filename = 'ma-motion-analytics-'.$report['days'].'d-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Section', 'Metric', 'Value'], ',', '"', '');

            foreach ($report['totals'] as $metric => $value) {
                fputcsv($handle, ['Platform totals', $metric, $value], ',', '"', '');
            }
            foreach ($report['period'] as $metric => $value) {
                fputcsv($handle, [$report['days'].' day activity', $metric, $value], ',', '"', '');
            }
            foreach ($report['user_status'] as $metric => $value) {
                fputcsv($handle, ['User status', $metric, $value], ',', '"', '');
            }
            foreach ($report['artwork_moderation'] as $metric => $value) {
                fputcsv($handle, ['Artwork', $metric, $value], ',', '"', '');
            }
            foreach ($report['show_status'] as $metric => $value) {
                fputcsv($handle, ['Shows', $metric, $value], ',', '"', '');
            }
            foreach ($report['top_makers'] as $index => $maker) {
                fputcsv($handle, ['Most Hearted Makers', '#'.($index + 1).' '.$maker->name, $maker->saves_count], ',', '"', '');
            }
            foreach ($report['daily_activity'] as $row) {
                fputcsv($handle, ['Daily '.$row['date'], 'users', $row['users']], ',', '"', '');
                fputcsv($handle, ['Daily '.$row['date'], 'artworks', $row['artworks']], ',', '"', '');
                fputcsv($handle, ['Daily '.$row['date'], 'saves', $row['saves']], ',', '"', '');
                fputcsv($handle, ['Daily '.$row['date'], 'admin_actions', $row['admin_actions']], ',', '"', '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
