@props([
    'type' => 'line', // line, bar, pie, etc.
    'labels' => '[]',
    'data' => '[]',
    'options' => '{}',
])

<div
    x-data="{
        init() {
            // Here you would initialize your chart library, e.g.:
            // new Chart(this.$refs.canvas, { type: '{{ $type }}', data: { labels: {{ $labels }}, datasets: [ { data: {{ $data }} } ] }, options: {{ $options }} })
        }
    }"
    {{ $attributes->merge(['class' => 'chart-container']) }}
>
    <canvas x-ref="canvas"></canvas>
</div>