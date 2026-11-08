@props([
    'label',
    'value',
    'icon',
    'detail' => null,
    'tone' => 'primary',
    'percentage' => null,
])

<article {{ $attributes->class(['stat-card', 'stat-card--'.$tone]) }}>
    <div class="stat-card-copy">
        <span class="stat-card-label">{{ $label }}</span>
        <strong class="stat-card-value">{{ $value }}</strong>
        @if ($detail)
            <span class="stat-card-detail">{{ $detail }}</span>
        @endif
    </div>
    @if ($percentage !== null)
        <span class="attendance-ring" style="--attendance: {{ max(0, min(100, (int) $percentage)) }}%" role="img" aria-label="{{ $percentage }} persen kehadiran">
            <span>{{ $percentage }}<small>%</small></span>
        </span>
    @else
        <span class="stat-card-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
    @endif
</article>
