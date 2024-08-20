<?php

namespace App\Http\Controllers\Cms;

use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }
}
