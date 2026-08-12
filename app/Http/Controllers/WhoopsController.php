<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class WhoopsController extends Controller
{
    public function display(): Response
    {
        logger()->info(__METHOD__);

        return Inertia::render('Errors/Whoops');
    }
}
