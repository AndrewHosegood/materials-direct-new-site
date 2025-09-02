<?php
/**
 * Allow SVG uploads safely in WordPress.
 */
function allow_svg_upload($mimes) {
    // Add SVG mime type
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

/**
 * Sanitize SVG files on upload to prevent malicious code.
 */
function sanitize_svg_on_upload($file) {
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

    if ($ext === 'svg') {
        $svg = file_get_contents($file['tmp_name']);

        // Remove script tags
        $svg = preg_replace('/<script.*?<\/script>/is', '', $svg);

        // Optionally, remove on* attributes like onclick, onload etc.
        $svg = preg_replace('/on[a-z]+\s*=\s*"[^"]*"/i', '', $svg);

        // Save sanitized SVG back
        file_put_contents($file['tmp_name'], $svg);
    }

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'sanitize_svg_on_upload');
