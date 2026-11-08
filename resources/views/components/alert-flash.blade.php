@props([
    'success' => session('success'),
    'error' => session('error'),
])

@if ($success)
    <div {{ $attributes->class(['alert-flash', 'alert-flash--success']) }} role="status">
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
        <span>{{ $success }}</span>
    </div>
@endif

@if ($error)
    <div {{ $attributes->class(['alert-flash', 'alert-flash--danger']) }} role="alert">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
        <span>{{ $error }}</span>
    </div>
@endif
