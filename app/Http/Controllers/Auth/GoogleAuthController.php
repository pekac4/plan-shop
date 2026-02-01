<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Http\RedirectResponse;

class GoogleAuthController extends Controller
{
    public function __construct(private GoogleAuthService $googleAuthService) {}

    public function redirect(): RedirectResponse
    {
        return $this->googleAuthService->redirect();
    }

    public function callback(): RedirectResponse
    {
        return $this->googleAuthService->handleCallback();
    }
}
