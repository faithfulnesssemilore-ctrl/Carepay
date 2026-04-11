@props([
    'tab' => '', // unique id for this pane
    'active' => false,
])

@php
    $isActive = $active || (isset($activeTab) && $activeTab === $tab);
@endphp

<div
    class="tab-pane fade {{ $isActive ? 'show active' : '' }}"
    id="tab-{{ $tab }}"
    role="tabpanel"
    aria-labelledby="tab-{{ $tab }}-btn"
    x-show="activeTab === '{{ $tab }}'"
    x-cloak
    {{ $attributes }}
>
    {{ $slot }}
</div>