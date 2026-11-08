@foreach ($adminNavigation as $item)
    @if (isset($item['route']))
        @php
            $isActive = request()->routeIs($item['active'] ?? $item['route']);
        @endphp
        <a class="admin-nav-link {{ $isActive ? 'is-active' : '' }}" href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif @if ($mobile) data-bs-dismiss="offcanvas" @endif>
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @else
        <span class="admin-nav-link is-disabled" aria-disabled="true">
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
            <span class="nav-soon">Segera</span>
        </span>
    @endif
@endforeach
