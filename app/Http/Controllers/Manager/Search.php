<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\TG\SearchEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;

class Search extends Controller
{
    public function postSearch(Business $business, Request $request): Response
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'criteria' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $criteria = $validated['criteria'];

        $search = new SearchEngine($criteria);
        $search->setBusinessScope([$business->id])->run();

        $results = $search->results();

        return Inertia::render('Business/Search/Index', [
            'results'  => $results,
            'criteria' => $criteria,
            'business' => $business,
        ]);
    }
}
