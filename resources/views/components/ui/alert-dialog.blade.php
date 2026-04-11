@props([
    'id' => 'alert-' . uniqid(),
    'title' => null,
])

<x-ui.modal
    :id="$id"
    :title="$title"
    size="sm"
    centered
    {{ $attributes }}
    role="alertdialog"
    aria-modal="true"
>
    {{ $slot }}
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Confirm</button>
    </x-slot>
</x-ui.modal>