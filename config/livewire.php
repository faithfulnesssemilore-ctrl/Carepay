<?php

return [
    'class_namespace' => 'App\\Livewire',
    'view_path' => resource_path('views/livewire'),
    // Livewire expects `component_layout` and `component_namespaces` keys for
    // page-based components. Ensure they point to the components layout
    // folder used in this project.
    'component_namespaces' => [
        'layouts' => resource_path('views/components/layouts'),
        'pages' => resource_path('views/pages'),
    ],
    'component_layout' => 'layouts::app',
    // Backwards-compatible alias used elsewhere in the app
    'layout' => 'components.layouts.app',
    'inject_assets' => true,
    'inject_morph_markers' => true,
    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#a855f7',
    ],
    'temporary_file_upload' => [
        'disk' => 'local',
        'rules' => ['required', 'file', 'max:10240'],
        'directory' => 'livewire-tmp',
        'middleware' => 'throttle:60,1',
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'rtf', 'pdf', 'psd', 'zip', 'zst', 'tar',
            'gz', 'gif', 'bmp', 'tiff', 'jpeg', 'jpg',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'html_morph_anchors' => true,
    'features' => [
        \Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::class,
        \Livewire\Features\SupportFileUploads\SupportFileUploads::class,
        \Livewire\Features\SupportEntangle\SupportEntangle::class,
        \Livewire\Features\SupportEvents\SupportEvents::class,
        \Livewire\Features\SupportLocales\SupportLocales::class,
        \Livewire\Features\SupportTeleport\SupportTeleport::class,
    ],
];
