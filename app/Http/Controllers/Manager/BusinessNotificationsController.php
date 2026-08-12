<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;

class BusinessNotificationsController extends Controller
{
    public function show(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manage', $business);

        $notifications = format_business_notifications(
            $business->notifications()->latest()->get()
        );

        return Inertia::render('Business/Notifications/Index', [
            'business'      => $business,
            'notifications' => $notifications,
        ]);
    }
}
