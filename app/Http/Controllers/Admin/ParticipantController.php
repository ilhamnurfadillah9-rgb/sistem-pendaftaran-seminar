<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRegistrationRequest;
use App\Models\Registration;
use App\Models\Seminar;
use App\Services\AttendanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ParticipantController extends Controller
{
    public function index(Request $request, Seminar $seminar, AttendanceService $attendanceService): View
    {
        $attendanceService->markExpiredAsAbsent($seminar);

        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';

        $seminar->loadCount('registrations');

        $participants = $seminar->registrations()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.seminars.participants', compact('seminar', 'participants', 'search'));
    }

    public function edit(Seminar $seminar, Registration $registration): View
    {
        return view('admin.seminars.participant-edit', compact('seminar', 'registration'));
    }

    public function update(
        UpdateRegistrationRequest $request,
        Seminar $seminar,
        Registration $registration,
    ): RedirectResponse {
        $registration->update($request->validated());

        return redirect()
            ->route('admin.seminars.participants.index', $seminar)
            ->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Seminar $seminar, Registration $registration): RedirectResponse
    {
        $registration->delete();

        return redirect()
            ->route('admin.seminars.participants.index', $seminar)
            ->with('success', 'Peserta berhasil dihapus.');
    }

    public function qr(Seminar $seminar, Registration $registration): Response
    {
        $svg = QrCode::format('svg')
            ->size(220)
            ->margin(1)
            ->generate($registration->token);

        return response($svg)->header('Content-Type', 'image/svg+xml; charset=UTF-8');
    }
}
