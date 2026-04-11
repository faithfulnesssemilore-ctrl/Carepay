@props([
    'id' => 'command-' . uniqid(),
    'placeholder' => 'Type a command or search...',
])

<div
    x-data="{ open: false, query: '' }"
    @keydown.cmd.k.window="open = true"
    @keydown.ctrl.k.window="open = true"
    @keydown.escape.window="open = false"
>
    <!-- Trigger (optional) -->
    <button @click="open = true" class="btn btn-outline-secondary">
        <i class="bi bi-search"></i> Search (⌘K)
    </button>

    <!-- Modal overlay -->
    <div
        x-show="open"
        x-cloak
        class="modal fade show d-block"
        tabindex="-1"
        style="background: rgba(0,0,0,0.5);"
        @click.away="open = false"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="{{ $placeholder }}"
                        x-model="query"
                        x-ref="searchInput"
                    >
                    <button type="button" class="btn-close" @click="open = false"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>