@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <section class="dashboard-welcome" aria-labelledby="dashboard-title">
        <div>
            <p class="dashboard-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            <h1 id="dashboard-title">Ringkasan seminar</h1>
            <p class="dashboard-description">Pantau kegiatan dan kehadiran dalam satu tempat.</p>
        </div>
        <a class="dashboard-calendar-mark" href="{{ route('admin.seminars.index') }}" aria-label="Lihat daftar seminar">
            <i class="bi bi-calendar2-week" aria-hidden="true"></i>
        </a>
    </section>

    <section class="dashboard-statistics" aria-label="Statistik ringkas">
        <x-stat-card
            label="Total seminar"
            :value="$statistics['total_seminars']"
            icon="bi-calendar-event"
            detail="Semua seminar"
            tone="primary"
        />
        <x-stat-card
            label="Total pendaftar"
            :value="$statistics['total_registrations']"
            icon="bi-people"
            detail="Di seluruh seminar"
            tone="violet"
        />
        <x-stat-card
            label="Seminar dibuka"
            :value="$statistics['open_seminars']"
            icon="bi-calendar-check"
            detail="Pendaftaran tersedia"
            tone="green"
        />
        <x-stat-card
            label="Rata-rata kehadiran"
            :value="$statistics['attendance_percentage'].'%'"
            icon="bi-check2-circle"
            detail="Dari seluruh pendaftar"
            :percentage="$statistics['attendance_percentage']"
            tone="orange"
        />
    </section>

    <div class="dashboard-content-grid">
        <section class="dashboard-panel dashboard-upcoming" aria-labelledby="upcoming-title">
            <div class="section-heading">
                <div>
                    <h2 id="upcoming-title">Seminar Mendatang</h2>
                    <p>Jadwal terdekat dalam kalender seminar.</p>
                </div>
                <a class="dashboard-panel-link" href="{{ route('admin.seminars.index') }}">Semua seminar</a>
            </div>

            @forelse ($upcomingSeminars as $seminar)
                <a class="upcoming-seminar-row" href="{{ route('admin.seminars.show', $seminar) }}">
                    <span class="upcoming-date">
                        <strong>{{ $seminar->tanggal->format('d') }}</strong>
                        <small>{{ $seminar->tanggal->locale('id')->translatedFormat('M') }}</small>
                    </span>
                    <span class="upcoming-seminar-copy">
                        <strong>{{ $seminar->judul }}</strong>
                        <small>{{ $seminar->waktu_mulai }} · {{ $seminar->lokasi }}</small>
                    </span>
                    <span class="upcoming-seminar-meta">
                        <x-badge-status :status="$seminar->status" />
                        <small>{{ $seminar->registrations_count }} pendaftar</small>
                    </span>
                </a>
            @empty
                <div class="dashboard-empty-state">
                    <i class="bi bi-calendar2-plus" aria-hidden="true"></i>
                    <p>Belum ada seminar mendatang.</p>
                </div>
            @endforelse
        </section>

        <section class="dashboard-panel dashboard-attendance-panel" aria-labelledby="attendance-overview-title">
            <div class="section-heading">
                <div>
                    <h2 id="attendance-overview-title">Kehadiran keseluruhan</h2>
                    <p>Peserta yang tercatat hadir.</p>
                </div>
            </div>
            <div class="dashboard-donut-wrap">
                <span
                    class="dashboard-donut"
                    style="--attendance: {{ $statistics['attendance_percentage'] }}%"
                    role="img"
                    aria-label="Kehadiran {{ $statistics['attendance_percentage'] }} persen"
                >
                    <span><strong>{{ $statistics['attendance_percentage'] }}%</strong><small>hadir</small></span>
                </span>
                <div class="dashboard-donut-legend">
                    <span><i class="legend-dot legend-dot--green" aria-hidden="true"></i>Hadir</span>
                    <strong>{{ $statistics['attended_registrations'] }} peserta</strong>
                    <a href="{{ route('admin.reports.index') }}">Buka laporan</a>
                </div>
            </div>
        </section>
    </div>

    <section class="dashboard-panel dashboard-recent-panel" aria-labelledby="recent-registrations-title">
        <div class="section-heading">
            <div>
                <h2 id="recent-registrations-title">Pendaftar Terbaru</h2>
                <p>Pendaftaran yang baru masuk dari semua seminar.</p>
            </div>
            <a class="dashboard-panel-link" href="{{ route('admin.seminars.index') }}">Kelola peserta</a>
        </div>

        @forelse ($recentRegistrations as $registration)
            <div class="recent-registration-row">
                <span class="recent-registration-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($registration->nama, 0, 1)) }}</span>
                <span class="recent-registration-copy">
                    <strong>{{ $registration->nama }}</strong>
                    <small>{{ $registration->seminar->judul }}</small>
                </span>
                <span class="recent-registration-date">{{ $registration->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                <x-badge-status :status="$registration->status" />
            </div>
        @empty
            <div class="dashboard-empty-state">
                <i class="bi bi-person-plus" aria-hidden="true"></i>
                <p>Belum ada pendaftar. Pendaftaran baru akan muncul di sini.</p>
            </div>
        @endforelse
    </section>
@endsection
