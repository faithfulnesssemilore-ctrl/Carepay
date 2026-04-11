@props([
    'id' => 'toast-' . uniqid(),
    'title' => null,
    'time' => 'just now',
    'dismissible' => true,
])

<div class="toast" id="{{ $id }}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
    <div class="toast-header">
        @if($title)
            <strong class="me-auto">{{ $title }}</strong>
        @endif
        <small>{{ $time }}</small>
        @if($dismissible)
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        @endif
    </div>
    <div class="toast-body">
        {{ $slot }}
    </div>
</div>