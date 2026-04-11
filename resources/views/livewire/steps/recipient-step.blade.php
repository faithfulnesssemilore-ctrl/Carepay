{{--
    Recipient Selection Step
    Allows user to search and select a recipient from recent contacts
    UI Components: x-ui.card with hover-lift effect
    CSS Classes: hover-lift, gradient-bg-primary, text-muted-custom
--}}

<div class="d-flex flex-column gap-4">
    <div>
        <h2 class="h3 fw-bold mb-2">Select Recipient</h2>
        <p class="text-muted-custom">Choose who you want to send money to</p>
    </div>

    {{-- Search Input --}}
    <div class="input-group">
        <span class="input-group-text bg-secondary-custom border-0">
            <i class="fas fa-search text-muted-custom"></i>
        </span>
        <x-ui.input 
            type="text"
            name="searchQuery"
            placeholder="Search by name, email, or phone..."
            class="bg-secondary-custom border-0 rounded-xl"
            style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
            wire:model="searchQuery"
        />
    </div>

    {{-- Recent Contacts Grid --}}
    <div>
        <h3 class="small fw-medium mb-3">Recent Contacts</h3>
        <div class="row g-3">
            @forelse($recentContacts as $contact)
                <div class="col-sm-6">
                    <x-ui.card 
                        variant="default" 
                        hover="lift"
                        class="bg-secondary-custom border"
                        wire:click="selectRecipient({{ $contact['id'] }})"
                        style="
                            cursor: pointer; 
                            border-color: #2a2a3a;
                            border-radius: 12px; 
                            transition: all 0.3s ease;
                        "
                    >
                        <div class="card-body d-flex align-items-center gap-3">
                            {{-- Avatar Circle with Gradient Background --}}
                            <div 
                                class="rounded-circle d-flex align-items-center justify-content-center text-white fw-semibold gradient-bg-primary"
                                style="
                                    width: 48px; 
                                    height: 48px; 
                                    flex-shrink: 0;
                                "
                            >
                                {{ substr($contact['name'], 0, 1) }}{{ strpos($contact['name'], ' ') ? substr($contact['name'], strpos($contact['name'], ' ') + 1, 1) : '' }}
                            </div>
                            
                            {{-- Contact Information --}}
                            <div class="flex-fill" style="min-width: 0;">
                                <div class="fw-medium">{{ $contact['name'] }}</div>
                                <div class="small text-muted-custom text-truncate">{{ $contact['email'] }}</div>
                            </div>
                            
                            {{-- Arrow Icon --}}
                            <i class="fas fa-arrow-right text-muted-custom" style="flex-shrink: 0;"></i>
                        </div>
                    </x-ui.card>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No contacts found. Add recipients to get started.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
