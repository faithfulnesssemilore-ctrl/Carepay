@props([
    'tab' => '', // id of the tab pane this triggers
    'active' => false,
])

@php
    $isActive = $active || (isset($activeTab) && $activeTab === $tab);
@endphp

<li class="nav-item" role="presentation">
    <button
        class="nav-link {{ $isActive ? 'active' : '' }}"
        id="tab-{{ $tab }}-btn"
        data-bs-toggle="tab"
        data-bs-target="#tab-{{ $tab }}"
        type="button"
        role="tab"
        aria-controls="tab-{{ $tab }}"
        aria-selected="{{ $isActive ? 'true' : 'false' }}"
        x-on:click="activeTab = '{{ $tab }}'"
        {{ $attributes }}
    >
        {{ $slot }}
    </button>
</li>