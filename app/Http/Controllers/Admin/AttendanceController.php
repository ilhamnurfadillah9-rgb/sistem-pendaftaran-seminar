<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Registration;
use App\Models\Seminar;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function scan(Request $request, AttendanceService $attendanceService): View
    {
        $attendanceService->markExpiredAsAbsent();

        $seminars = Seminar::query()
            ->orderByDesc('tanggal')
            ->orderBy('waktu_mulai')
            ->get(['id', 'judul', 'tanggal', 'waktu_mulai', 'waktu_selesai']);
        $requestedSeminarId = $request->integer('seminar_id');
        $selectedSeminarId = $seminars->contains('id', $requestedSeminarId)
            ? $requestedSeminarId
            : $seminars->first()?->id;

        return view('admin.attendance.scan', compact('seminars', 'selectedSeminarId'));
    }

    public function storeScan(Request $request, AttendanceService $attendanceService): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:64'],
            'seminar_id' => ['required', 'integer', 'exists:seminars,id'],
        ], [
            'token.required' => 'Masukkan kode QR.',
            'token.string' => 'Kode QR tidak valid.',
            'token.max' => 'Kode QR tidak valid.',
            'seminar_id.required' => 'Pilih seminar terlebih dahulu.',
            'seminar_id.exists' => 'Seminar yang dipilih tidak ditemukan.',
        ]);

        $result = $attendanceService->scan(
            $validated['token'],
            Seminar::findOrFail($validated['seminar_id']),
        );

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'participant' => [
                'nama' => $result['registration']->nama,
                'seminar' => $result['registration']->seminar->judul,
            ],
        ]);
    }

    public function index(Seminar $seminar, AttendanceService $attendanceService): View
    {
        $attendanceService->markExpiredAsAbsent($seminar);
        $seminar->loadCount('registrations');

        $participants = $seminar->registrations()
            ->orderBy('nama')
            ->orderBy('id')
            ->paginate(25);

        return view('admin.seminars.attendance', compact('seminar', 'participants'));
    }

    public function update(
        UpdateAttendanceRequest $request,
        Seminar $seminar,
        Registration $registration,
    ): RedirectResponse {
        $status = $request->validated('status');

        $registration->update([
            'status' => $status,
            'waktu_hadir' => $status === 'hadir' ? now('Asia/Jakarta') : null,
        ]);

        return redirect()
            ->route('admin.seminars.attendance.index', $seminar)
            ->with('success', 'Status kehadiran berhasil diperbarui.');
    }
}
