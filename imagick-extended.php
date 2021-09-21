<?php

/*
 * Plugin name: ImageMagick extended
 */

// lol no autoload
require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
require __DIR__ . '/class-wp-image-editor-imagick-extended.php';

class ImageMagickExtended {
    /**
     * @var array
     */
    public $options;
    public $resampling_filters;

    public function __construct()
    {
        $default_options = [
            'chroma_subsampling' => '420',
            'progressive' => true,
            'filter' => 'FILTER_LANCZOS',
        ];

        $db_options = get_option('imex_options') ?: [];
        $this->options = array_merge($default_options, $db_options);

        $available_filters = [];
        $resampling_filters = [
            'FILTER_POINT',
            'FILTER_BOX',
            'FILTER_TRIANGLE',
            'FILTER_HERMITE',
            'FILTER_HANNING',
            'FILTER_HAMMING',
            'FILTER_BLACKMAN',
            'FILTER_GAUSSIAN',
            'FILTER_QUADRATIC',
            'FILTER_CUBIC',
            'FILTER_CATROM',
            'FILTER_MITCHELL',
            'FILTER_LANCZOS',
            'FILTER_BESSEL',
            'FILTER_SINC',
        ];

        foreach ($resampling_filters as $filter) {
            if (defined( 'Imagick::' . $filter )) {
                array_push($available_filters, $filter);
            }
        }

        $this->resampling_filters = $available_filters;

        add_filter('wp_image_editors', [$this, 'add_editor'], 10);
        add_action('wp_ajax_imex_save_options', [$this, 'ajax_save_options']);
        add_action('wp_ajax_imex_get_options', [$this, 'ajax_get_options']);
        add_action('wp_ajax_imex_preview', [$this, 'ajax_preview']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_filter('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }

    public function add_editor($image_editors) {
        if (extension_loaded('imagick')) {
            array_unshift($image_editors, 'WP_Image_Editor_Imagick_Options');
        }
        return $image_editors;
    }

    function ajax_save_options() {
        $options = $_POST['imex_options'];

        // 10 years and counting. Don't hold your breath. https://core.trac.wordpress.org/ticket/18322
        $options = stripslashes_deep($options);

        $options = json_decode($options, true);

        // TODO: maybe validate data somehow?
        update_option('imex_options', $options);
    }

    function ajax_preview()
    {
        $image_editor = wp_get_image_editor(__DIR__ . '/test-images/kodim01.png');
        $image_editor->resize(150, 150);
        echo $image_editor->stream('image/jpeg');
    }

    function ajax_get_options() {
        wp_send_json($this->options);
    }

    function admin_menu()
    {
        add_options_page(
            __('ImageMagick Extended', 'ime'),
            __('ImageMagick Extended', 'ime'),
            'manage_options',
            'imagemagick_extended',
            [$this, 'render_menu']
        );
    }

    public function render_menu() {

        $context = [];
        $context['resampling_filters'] = $this->resampling_filters;
        $context['test_images'] = [
            plugin_dir_url(__FILE__) . 'test-images/kodim01.png'
        ];
        $json = htmlspecialchars(json_encode($context));

        echo "<div id=\"imagemagick-extended\"><imagemagick-extended v-bind:context=\"$json\"></imagemagick-extended></div>";
    }

    function admin_enqueue_scripts()
    {
        $plugin_data = get_plugin_data(__FILE__);
        $version = $plugin_data['Version'];
        $url = plugin_dir_url(__FILE__);
        $path = plugin_dir_path(__FILE__);

        wp_enqueue_script(
            'imex-script',
            "{$url}frontend/dist/script.js",
            [],
            WP_DEBUG ? md5_file($path . 'frontend/dist/script.js') : $version
        );

        wp_enqueue_style(
            'imex-style',
            "{$url}frontend/dist/style.css",
            [],
            WP_DEBUG ? md5_file($path . 'frontend/dist/style.css') : $version
        );

        $strings = [
            'options_saved' => __('Options saved', 'imex')
        ];
        wp_localize_script('imex-script', 'imex_translations', $strings);
    }
}

$imagemagick_extended = new ImageMagickExtended();
