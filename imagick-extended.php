<?php

/*
 * Plugin name: ImageMagick extended
 */

// lol no autoload
require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
require __DIR__ . '/class-wp-image-editor-imagick-extended.php';

add_filter('wp_image_editors', function($image_editors) {
    if (extension_loaded('imagick')) {
        array_unshift($image_editors, 'WP_Image_Editor_Imagick_Options');
    }
    return $image_editors;
}, 20);
