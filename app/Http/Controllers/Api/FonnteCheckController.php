<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FonnteMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FonnteCheckController extends Controller
{
    public function __construct(
        private FonnteMonitoringService $monitoringService
    ) {}

    /**
     * Handle request dari Google Cloud Scheduler.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.fonnte.check_secret');

        if ($secret) {
            $bearer = $request->bearerToken();

            if ($bearer !== $secret) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        $status = $this->monitoringService->checkStatus();

        $this->monitoringService->notifyIfDisconnected($status);

        return response()->json([
            'status' => $status['connected'] ? 'connected' : 'disconnected',
            'message' => $status['message'],
            'checked_at' => $status['checked_at'],
        ], $status['connected'] ? 200 : 503);
    }
}
