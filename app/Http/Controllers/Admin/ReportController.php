<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $seminars = $this->seminarOptions();
        $seminar = $this->selectedSeminar($request, $seminars);
        $reportMetrics = $this->metrics($seminar);

        return view('admin.reports.index', compact('seminars', 'seminar', 'reportMetrics'));
    }

    public function export(Request $request): StreamedResponse
    {
        $seminar = $this->selectedSeminar($request, $this->seminarOptions());
        abort_if($seminar === null, 404);

        $reportMetrics = $this->metrics($seminar);

        return response()->streamDownload(function () use ($seminar, $reportMetrics): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuka aliran ekspor laporan.');
            }

            echo "\xEF\xBB\xBF";
            $this->writeCsvRow($output, ['Laporan seminar', $seminar->judul]);
            $this->writeCsvRow($output, ['Jumlah pendaftar', $reportMetrics['registrations']]);
            $this->writeCsvRow($output, ['Hadir', $reportMetrics['attended']]);
            $this->writeCsvRow($output, ['Tidak hadir', $reportMetrics['absent']]);
            $this->writeCsvRow($output, ['Belum dicatat', $reportMetrics['unrecorded']]);
            $this->writeCsvRow($output, ['Sisa kuota', $reportMetrics['remaining_quota']]);
            $this->writeCsvRow($output, []);
            $this->writeCsvRow($output, ['Nama', 'Email', 'Telepon', 'Status', 'Waktu daftar']);

            $seminar->registrations()
                ->orderBy('id')
                ->cursor()
                ->each(function ($registration) use ($output): void {
                    $this->writeCsvRow($output, [
                        $registration->nama,
                        $registration->email,
                        $registration->telepon,
                        $registration->status,
                        $registration->created_at->format('Y-m-d H:i:s'),
                    ]);
                });

            fclose($output);
        }, 'laporan-seminar-'.$seminar->id.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return Collection<int, Seminar>
     */
    private function seminarOptions(): Collection
    {
        return Seminar::query()
            ->select(['id', 'judul'])
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->orderBy('id')
            ->get();
    }

    private function selectedSeminar(Request $request, Collection $seminars): ?Seminar
    {
        if ($seminars->isEmpty()) {
            return null;
        }

        $seminar = $request->filled('seminar_id')
            ? $seminars->firstWhere('id', $request->integer('seminar_id'))
            : $seminars->first();

        abort_if($seminar === null, 404);

        return Seminar::query()
            ->withCount([
                'registrations',
                'registrations as attended_count' => fn ($query) => $query->where('status', 'hadir'),
                'registrations as absent_count' => fn ($query) => $query->where('status', 'tidak_hadir'),
                'registrations as unrecorded_count' => fn ($query) => $query->where('status', 'terdaftar'),
            ])
            ->findOrFail($seminar->id);
    }

    /**
     * @return array{
     *     registrations: int,
     *     attended: int,
     *     absent: int,
     *     unrecorded: int,
     *     remaining_quota: int,
     *     attendance_percentage: int,
     *     quota_percentage: int
     * }
     */
    private function metrics(?Seminar $seminar): array
    {
        $registrations = (int) ($seminar?->registrations_count ?? 0);
        $attended = (int) ($seminar?->attended_count ?? 0);
        $absent = (int) ($seminar?->absent_count ?? 0);
        $unrecorded = (int) ($seminar?->unrecorded_count ?? 0);
        $quota = (int) ($seminar?->kuota ?? 0);

        return [
            'registrations' => $registrations,
            'attended' => $attended,
            'absent' => $absent,
            'unrecorded' => $unrecorded,
            'remaining_quota' => $quota - $registrations,
            'attendance_percentage' => $registrations === 0
                ? 0
                : (int) round($attended / $registrations * 100),
            'quota_percentage' => $quota === 0
                ? 0
                : max(0, min(100, (int) round($registrations / $quota * 100))),
        ];
    }

    /**
     * @param  resource  $output
     * @param  array<int, string|int>  $row
     */
    private function writeCsvRow($output, array $row): void
    {
        $safeRow = array_map(
            fn (string|int $value): string => is_int($value)
                ? (string) $value
                : $this->safeCsvCell($value),
            $row,
        );

        fputcsv($output, $safeRow, ',', '"', '');
    }

    private function safeCsvCell(string $value): string
    {
        if (preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
