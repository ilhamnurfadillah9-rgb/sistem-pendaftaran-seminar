<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Seminar;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $registrationCounts = Registration::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalRegistrations = (int) $registrationCounts->sum();
        $attendedRegistrations = (int) $registrationCounts->get('hadir', 0);

        $statistics = [
            'total_seminars' => Seminar::query()->count(),
            'total_registrations' => $totalRegistrations,
            'attended_registrations' => $attendedRegistrations,
            'open_seminars' => Seminar::query()->where('status', 'dibuka')->count(),
            'attendance_percentage' => $totalRegistrations === 0
                ? 0
                : (int) round($attendedRegistrations / $totalRegistrations * 100),
        ];

        $upcomingSeminars = Seminar::query()
            ->withCount('registrations')
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->limit(5)
            ->get();

        $recentRegistrations = Registration::query()
            ->with('seminar:id,judul')
            ->latest()
            ->latest('id')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'statistics',
            'upcomingSeminars',
            'recentRegistrations',
        ));
    }
}
