<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Service;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AvailabilityController extends Controller
{
    public function dates(Business $business, Service $service, AvailabilityService $availabilityService): JsonResponse
    {
        $this->ensureServiceBelongsToBusiness($business, $service);

        return response()->json([
            'dates' => $availabilityService->getAvailableDates($business, $service),
        ]);
    }

    public function times(Business $business, Service $service, string $date, AvailabilityService $availabilityService): JsonResponse
    {
        $this->ensureServiceBelongsToBusiness($business, $service);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
        }

        return response()->json([
            'times' => $availabilityService->getAvailableTimes($business, $service, $date),
        ]);
    }

    private function ensureServiceBelongsToBusiness(Business $business, Service $service): void
    {
        if ($service->business_id !== $business->id) {
            throw new NotFoundHttpException('Service not found for this business.');
        }
    }
}
