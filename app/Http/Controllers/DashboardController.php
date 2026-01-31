<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function __invoke(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('dashboard', $this->dashboardService->build($user));
    }
}
