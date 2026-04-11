@props([
    'type' => 'radio', // or 'checkbox'
    'value' => null,   // for radio: selected value; for checkbox: array of selected values
])

<div
    x-data="{
        value: @json($value),
        toggle(val) {
            if ('{{ $type }}' === 'radio') {
                this.value = val;
            } else {
                if (Array.isArray(this.value)) {
                    if (this.value.includes(val)) {
                        this.value = this.value.filter(v => v !== val);
                    } else {
                        this.value.push(val);
                    }
                }
            }
        }
    }"
    class="btn-group"
    role="group"
    {{ $attributes }}
>
    {{ $slot }}
</div>