<?php
/**
 * resume_renderer.php
 * ----------------------------------------------------------------------
 * The only file allowed to `include` a template's index.php. Keeping
 * this in one place means templates never need to know about routing,
 * sessions, or the database — they just receive $resume.
 * ----------------------------------------------------------------------
 */

declare(strict_types=1);

require_once __DIR__ . '/resume_data.php';

/**
 * Returns the validated registry entry for a slug, or null if unknown.
 */
function get_template_meta(string $slug): ?array
{
    static $registry = null;
    if ($registry === null) {
        $registry = require __DIR__ . '/templates_registry.php';
    }
    return $registry[$slug] ?? null;
}

/** Convenience: list of all valid template slugs. */
function get_template_slugs(): array
{
    static $registry = null;
    if ($registry === null) {
        $registry = require __DIR__ . '/templates_registry.php';
    }
    return array_keys($registry);
}

/** Public web path to a template's stylesheet (relative to project root). */
function resume_template_style_path(string $slug): string
{
    return 'templates/' . $slug . '/style.css';
}

/** Filesystem path to a template's index.php. */
function resume_template_entry_path(string $slug): string
{
    return __DIR__ . '/templates/' . $slug . '/index.php';
}

/**
 * Renders one template with one resume into the current output buffer.
 * This is the single integration point every template must go through.
 *
 * @param string $slug   Template folder name (validated against the registry)
 * @param array  $resume Shared resume data structure (see resume_data.php)
 */
function render_resume_template(string $slug, array $resume): void
{
    if (get_template_meta($slug) === null) {
        throw new InvalidArgumentException("Unknown resume template: \"{$slug}\"");
    }

    $entry = resume_template_entry_path($slug);
    if (!is_file($entry)) {
        throw new RuntimeException("Template \"{$slug}\" is registered but missing its index.php");
    }

    // $resume is intentionally the only variable a template can rely on.
    include $entry;
}
