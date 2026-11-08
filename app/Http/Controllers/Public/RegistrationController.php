<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Models\Registration;
use App\Models\Seminar;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegistrationController extends Controller
{
    public function store(
        RegistrationRequest $request,
        Seminar $seminar,
        RegistrationService $registrationService,
    ): RedirectResponse {
        $registration = $registrationService->register($seminar, $request->validated());

        return redirect()->route('registrations.show', $registration->token);
    }

    public function show(string $token): View
    {
        $registration = Registration::query()
            ->with('seminar')
            ->where('token', $token)
            ->firstOrFail();
        $qrCode = base64_encode(
            QrCode::format('svg')->size(192)->margin(1)->generate($registration->token),
        );

        return view('public.registrations.show', compact('registration', 'qrCode'));
    }
}
