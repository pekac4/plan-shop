<?php

namespace App\Http\Controllers;

use App\Services\Home\HomeService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private HomeService $homeService) {}

    public function __invoke(): View
    {
        return view('welcome', $this->homeService->build());
    }
}
