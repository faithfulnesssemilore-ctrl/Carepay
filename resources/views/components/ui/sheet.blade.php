@props([
    'id' => 'sheet-' . uniqid(),
    'title' => null,
    'placement' => 'end', // start, end, top, bottom
    'backdrop' => true,
    'scrollable' => false, // if true, body can scroll while offcanvas is open
])

<div
    class="offcanvas offcanvas-{{ $placement }}"
    tabindex="-1"
    id="{{ $id }}"
    aria-labelledby="{{ $id }}Label"
    data-bs-backdrop="{{ $backdrop ? 'true' : 'false' }}"
    data-bs-scroll="{{ $scrollable ? 'true' : 'false' }}"
    {{ $attributes }}
>
    @if($title)
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="{{ $id }}Label">{{ $title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    @endif
    <div class="offcanvas-body">
        {{ $slot }}
    </div>
</div>