<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $service): View
    {
        return view('dashboard', $service->summary());
    }
}
