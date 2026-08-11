<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function welcome()
    {
        $businesses = Business::withCount('services')
            ->latest()
            ->limit(6)
            ->get();

        return Inertia::render('Welcome', [
            'businesses' => $businesses,
        ]);
    }

    public function directory()
    {
        $businesses = Business::withCount('services')
            ->latest()
            ->paginate(12);

        return Inertia::render('Directory', [
            'businesses' => $businesses,
        ]);
    }
}
