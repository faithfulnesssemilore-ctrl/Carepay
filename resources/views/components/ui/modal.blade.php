@props([
    'id' => 'modal-' . uniqid(),
    'size' => '', // sm, lg, xl
    'scrollable' => false,
    'centered' => true,
    'static' => false, // if true, click outside doesn't close
    'title' => null,
])

<div
    class="modal fade"
    id="{{ $id }}"
    tabindex="-1"
    aria-labelledby="{{ $id }}Label"
    aria-hidden="true"
    data-bs-backdrop="{{ $static ? 'static' : 'true' }}"
    data-bs-keyboard="{{ $static ? 'false' : 'true' }}"
    {{ $attributes }}
>
    <div class="modal-dialog
        {{ $size ? 'modal-' . $size : '' }}
        {{ $scrollable ? 'modal-dialog-scrollable' : '' }}
        {{ $centered ? 'modal-dialog-centered' : '' }}
    ">
        <div class="modal-content">
            @if($title)
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>