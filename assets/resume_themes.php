<?php
/**
 * assets/resume_themes.php
 * ----------------------------------------------------------------------
 * Single source of truth for THEME METADATA (id, display name, category
 * label, picker swatch hex). Colors actually used for rendering live in
 * assets/resume-themes.css — this file only describes what to show in
 * the picker. Keeping metadata (PHP) and styling (CSS) separate means
 * adding a theme touches exactly two files, never any template.
 *
 * IMPORTANT: every key here MUST have a matching `.theme-<key>` block
 * in assets/resume-themes.css, or selecting it will silently fall back
 * to a template's default look (no error, just no visual change).
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

return [
    'executive' => ['name' => 'Executive Blue', 'category' => 'Corporate',      'swatch' => '#1F3A5F'],
    'ocean'     => ['name' => 'Ocean',           'category' => 'Modern',        'swatch' => '#0077B6'],
    'forest'    => ['name' => 'Forest',          'category' => 'Professional',  'swatch' => '#2D5A3D'],
    'emerald'   => ['name' => 'Emerald',         'category' => 'Startup',       'swatch' => '#0E9F6E'],
    'burgundy'  => ['name' => 'Burgundy',        'category' => 'Premium',       'swatch' => '#7A1F2B'],
    'royal'     => ['name' => 'Royal',           'category' => 'Creative',      'swatch' => '#4338CA'],
    'slate'     => ['name' => 'Slate',           'category' => 'Minimal',       'swatch' => '#475569'],
    'graphite'  => ['name' => 'Graphite',        'category' => 'Modern',        'swatch' => '#2E2E33'],
    'charcoal'  => ['name' => 'Charcoal',        'category' => 'Executive',     'swatch' => '#1C1C1F'],
    'navy'      => ['name' => 'Navy',            'category' => 'Traditional',   'swatch' => '#1E3A5F'],
    'sunset'    => ['name' => 'Sunset',          'category' => 'Creative',      'swatch' => '#C2542E'],
    'sandstone' => ['name' => 'Sandstone',       'category' => 'Elegant',       'swatch' => '#9C7B53'],
    'ivory'     => ['name' => 'Ivory',           'category' => 'Editorial',     'swatch' => '#6B6456'],
];