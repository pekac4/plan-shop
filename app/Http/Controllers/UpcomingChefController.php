<?php

namespace App\Http\Controllers;

use App\Services\Highlights\UpcomingChefService;
use Illuminate\View\View;

class UpcomingChefController extends Controller
{
    public function __construct(private UpcomingChefService $upcomingChefService) {}

    public function __invoke(): View
    {
        return view('highlights.upcoming-chef', $this->upcomingChefService->build());
    }
}
