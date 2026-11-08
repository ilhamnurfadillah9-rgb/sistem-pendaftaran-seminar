@props(['status'])

@php
    $badgeStatuses = [
        'dibuka' => ['label' => 'Dibuka', 'class' => 'status-badge--success'],
        'ditutup' => ['label' => 'Ditutup', 'class' => 'status-badge--neutral'],
        'terdaftar' => ['label' => 'Terdaftar', 'class' => 'status-badge--warning'],
        'hadir' => ['label' => 'Hadir', 'class' => 'status-badge--success'],
        'tidak_hadir' => ['label' => 'Tidak hadir', 'class' => 'status-badge--danger'],
    ];
    $badge = $badgeStatuses[$status] ?? ['label' => $status, 'class' => 'status-badge--neutral'];
@endphp

<span {{ $attributes->class(['status-badge', $badge['class']]) }}>{{ $badge['label'] }}</span>
