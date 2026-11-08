@extends('layouts.admin')

@section('title', 'Laporan seminar')

@section('content')
    <section class="report-page-heading">
        <div>
            <p class="dashboard-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            <h1>Laporan seminar</h1>
            <p>Rekap pendaftaran, kehadiran, dan kapasitas seminar.</p>
        </div>
        @if ($seminar)
            <div class="report-actions">
                <a class="button-secondary" href="{{ route('admin.reports.export', ['seminar_id' => $seminar->id]) }}">
                    <i class="bi bi-download" aria-hidden="true"></i>Ekspor CSV
                </a>
                <button class="button-primary" type="button" onclick="window.print()">
                    <i class="bi bi-printer" aria-hidden="true"></i>Cetak laporan
                </button>
            </div>
        @endif
    </section>

    <section class="dashboard-panel report-controls" aria-label="Pilih seminar untuk laporan">
        <form class="report-filter-form" method="GET" action="{{ route('admin.reports.index') }}">
            <label for="seminar_id">Seminar</label>
            <select class="form-select" id="seminar_id" name="seminar_id" @disabled($seminars->isEmpty())>
                @foreach ($seminars as $option)
                    <option value="{{ $option->id }}" @selected($seminar?->id === $option->id)>{{ $option->judul }}</option>
                @endforeach
            </select>
            <button class="button-primary" type="submit" @disabled($seminars->isEmpty())>Tampilkan laporan</button>
        </form>
    </section>

    @if (! $seminar)
        <section class="dashboard-panel report-empty-state">
            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-file-earmark-bar-graph"></i></span>
            <h2>Belum ada data seminar</h2>
            <p>Tambahkan seminar terlebih dahulu untuk melihat laporan.</p>
        </section>
    @else
        <section class="report-overview" aria-labelledby="report-seminar-title">
            <div class="report-seminar-heading">
                <div>
                    <p class="dashboard-date">Rekapitulasi</p>
                    <h2 id="report-seminar-title">{{ $seminar->judul }}</h2>
                    <p>{{ $seminar->tanggal->locale('id')->translatedFormat('l, d F Y') }} · {{ $seminar->waktu_mulai }}–{{ $seminar->waktu_selesai }} · {{ $seminar->lokasi }}</p>
                </div>
                <x-badge-status :status="$seminar->status" />
            </div>

            <div class="dashboard-statistics report-statistics" aria-label="Ringkasan angka laporan">
                <x-stat-card label="Jumlah pendaftar" :value="$reportMetrics['registrations']" icon="bi-people" detail="Semua status" tone="primary" />
                <x-stat-card label="Hadir" :value="$reportMetrics['attended']" icon="bi-person-check" detail="Presensi tercatat" tone="green" />
                <x-stat-card label="Tidak hadir" :value="$reportMetrics['absent']" icon="bi-person-x" detail="Status tidak hadir" tone="orange" />
                <x-stat-card label="Belum dicatat" :value="$reportMetrics['unrecorded']" icon="bi-hourglass-split" detail="Masih terdaftar" tone="violet" />
            </div>

            <div class="report-quota-row">
                <div>
                    <h3>Sisa kuota</h3>
                    <p>{{ $reportMetrics['remaining_quota'] }} dari {{ $seminar->kuota }} kursi tersedia</p>
                </div>
                <strong>{{ $reportMetrics['quota_percentage'] }}% terpakai</strong>
            </div>
            <div
                class="report-progress-track"
                role="progressbar"
                aria-label="Kuota terpakai"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="{{ $reportMetrics['quota_percentage'] }}"
            >
                <span style="width: {{ $reportMetrics['quota_percentage'] }}%"></span>
            </div>
        </section>

        <section class="report-visual-grid" aria-label="Infografis laporan">
            <article class="dashboard-panel report-chart-card">
                <div class="section-heading">
                    <div>
                        <h2>Persentase kehadiran</h2>
                        <p>{{ $reportMetrics['attended'] }} dari {{ $reportMetrics['registrations'] }} pendaftar</p>
                    </div>
                </div>
                <div class="report-donut-row">
                    <span
                        class="report-donut"
                        style="--attendance: {{ $reportMetrics['attendance_percentage'] }}%"
                        role="img"
                        aria-label="Kehadiran {{ $reportMetrics['attendance_percentage'] }} persen"
                    >
                        <span><strong>{{ $reportMetrics['attendance_percentage'] }}%</strong><small>hadir</small></span>
                    </span>
                    <div class="report-legend">
                        <span><i class="legend-dot legend-dot--green" aria-hidden="true"></i>Hadir <strong>{{ $reportMetrics['attended'] }}</strong></span>
                        <span><i class="legend-dot legend-dot--neutral" aria-hidden="true"></i>Belum hadir <strong>{{ $reportMetrics['registrations'] - $reportMetrics['attended'] }}</strong></span>
                    </div>
                </div>
            </article>

            <article class="dashboard-panel report-chart-card">
                <div class="section-heading">
                    <div>
                        <h2>Komposisi peserta</h2>
                        <p>Status dari seluruh pendaftar.</p>
                    </div>
                </div>
                <div
                    class="report-composition-bar"
                    role="img"
                    aria-label="Hadir {{ $reportMetrics['attended'] }}, tidak hadir {{ $reportMetrics['absent'] }}, belum dicatat {{ $reportMetrics['unrecorded'] }}"
                >
                    <span class="report-segment report-segment--attended" style="flex-grow: {{ $reportMetrics['attended'] }}"></span>
                    <span class="report-segment report-segment--absent" style="flex-grow: {{ $reportMetrics['absent'] }}"></span>
                    <span class="report-segment report-segment--unrecorded" style="flex-grow: {{ $reportMetrics['unrecorded'] }}"></span>
                </div>
                <div class="report-legend report-legend--stacked">
                    <span><i class="legend-dot legend-dot--green" aria-hidden="true"></i>Hadir <strong>{{ $reportMetrics['attended'] }}</strong></span>
                    <span><i class="legend-dot legend-dot--red" aria-hidden="true"></i>Tidak hadir <strong>{{ $reportMetrics['absent'] }}</strong></span>
                    <span><i class="legend-dot legend-dot--orange" aria-hidden="true"></i>Belum dicatat <strong>{{ $reportMetrics['unrecorded'] }}</strong></span>
                </div>
            </article>
        </section>

        <section class="dashboard-panel report-table-panel" aria-labelledby="report-table-title">
            <div class="section-heading">
                <div>
                    <h2 id="report-table-title">Rekapitulasi peserta</h2>
                    <p>Angka diperbarui langsung dari data pendaftaran seminar ini.</p>
                </div>
            </div>
            <div class="report-table-scroll">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th scope="col">Jumlah pendaftar</th>
                            <th scope="col">Hadir</th>
                            <th scope="col">Tidak hadir</th>
                            <th scope="col">Belum dicatat</th>
                            <th scope="col">Sisa kuota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-label="Jumlah pendaftar">{{ $reportMetrics['registrations'] }}</td>
                            <td data-label="Hadir">{{ $reportMetrics['attended'] }}</td>
                            <td data-label="Tidak hadir">{{ $reportMetrics['absent'] }}</td>
                            <td data-label="Belum dicatat">{{ $reportMetrics['unrecorded'] }}</td>
                            <td data-label="Sisa kuota">{{ $reportMetrics['remaining_quota'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
