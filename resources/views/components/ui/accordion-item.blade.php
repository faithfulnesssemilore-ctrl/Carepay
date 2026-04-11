@props([
    'title' => '',
    'show' => false,
    'parentId' => null,
])

<div class="accordion-item">
    <h2 class="accordion-header">
        <button
            class="accordion-button {{ $show ? '' : 'collapsed' }}"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#collapse-{{ $slot->toHtml() }}"
            aria-expanded="{{ $show ? 'true' : 'false' }}"
            aria-controls="collapse-{{ $slot->toHtml() }}"
        >
            {{ $title }}
        </button>
    </h2>
    <div
        id="collapse-{{ $slot->toHtml() }}"
        class="accordion-collapse collapse {{ $show ? 'show' : '' }}"
        data-bs-parent="#{{ $parentId }}"
    >
        <div class="accordion-body">
            {{ $slot }}
        </div>
    </div>
</div>