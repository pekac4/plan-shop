<?php

namespace App\Http\Controllers;

use App\Services\Highlights\ChefOfMonthService;
use Illuminate\View\View;

class ChefOfMonthController extends Controller
{
    public function __construct(private ChefOfMonthService $chefOfMonthService) {}

    public function __invoke(): View
    {
        return view('highlights.chef-of-the-month', $this->chefOfMonthService->build());
    }
}
