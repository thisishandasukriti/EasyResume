<?php
/**
 * templates_registry.php
 * ----------------------------------------------------------------------
 * Central catalogue of every resume template. Add a new template by
 * dropping a folder in /templates and adding one entry here — nothing
 * else in the system needs to change (template-selection.php,
 * resume-builder.php and template-preview.php all read from this file).
 * ----------------------------------------------------------------------
 * Each entry:
 *   name        Display name shown to the user
 *   slug        Must match the folder name under /templates exactly
 *   description One-line pitch shown on the selection card
 *   category    Used to group/filter the gallery
 *   fonts       Display label only (actual fonts are set in the
 *               template's own style.css via --font-heading/--font-body)
 *   accent      Default accent color used for the gallery card chrome
 *               (independent from the template's own CSS variables)
 */

declare(strict_types=1);

return [
    'modern-sidebar' => [
        'name'        => 'Modern Sidebar',
        'slug'        => 'modern-sidebar',
        'description' => 'Two-column layout with a colored sidebar for contact, skills and languages.',
        'category'    => 'Modern',
        'fonts'       => 'Poppins / Inter',
        'accent'      => '#2454FF',
    ],
    'traditional-corporate' => [
        'name'        => 'Traditional Corporate',
        'slug'        => 'traditional-corporate',
        'description' => 'Single-column, conservative layout built for banking, law and finance roles.',
        'category'    => 'Traditional',
        'fonts'       => 'Source Sans Pro',
        'accent'      => '#1F2A44',
    ],
    'executive-professional' => [
        'name'        => 'Executive Professional',
        'slug'        => 'executive-professional',
        'description' => 'Bold header banner with a refined two-column body for senior leadership resumes.',
        'category'    => 'Executive',
        'fonts'       => 'Lora / Lato',
        'accent'      => '#7A4B1B',
    ],
    'minimal-ats' => [
        'name'        => 'Minimal ATS',
        'slug'        => 'minimal-ats',
        'description' => 'Pure single-column, zero graphics — the safest layout for automated parsers.',
        'category'    => 'ATS-Safe',
        'fonts'       => 'Inter',
        'accent'      => '#111111',
    ],
    'elegant-two-column' => [
        'name'        => 'Elegant Two Column',
        'slug'        => 'elegant-two-column',
        'description' => 'Centered serif header over two balanced columns, divided by a hairline rule.',
        'category'    => 'Elegant',
        'fonts'       => 'Source Serif Pro / Inter',
        'accent'      => '#5C5470',
    ],
    'modern-card' => [
        'name'        => 'Modern Card Style',
        'slug'        => 'modern-card',
        'description' => 'Each section reads as a clean bordered card with a colored accent edge.',
        'category'    => 'Modern',
        'fonts'       => 'Poppins / Roboto',
        'accent'      => '#0E8388',
    ],
    'clean-academic' => [
        'name'        => 'Clean Academic',
        'slug'        => 'clean-academic',
        'description' => 'Education-first serif layout suited to research, teaching and academic CVs.',
        'category'    => 'Academic',
        'fonts'       => 'Source Serif Pro',
        'accent'      => '#3B3A30',
    ],
    'contemporary-creative' => [
        'name'        => 'Contemporary Creative',
        'slug'        => 'contemporary-creative',
        'description' => 'A confident color header band with pill skill tags — modern but still ATS-friendly.',
        'category'    => 'Creative',
        'fonts'       => 'Poppins / Inter',
        'accent'      => '#E0532A',
    ],
];
