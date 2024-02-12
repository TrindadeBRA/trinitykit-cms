<?php
define('themeUrl', get_template_directory_uri());
define('siteUrl', get_site_url());

require_once get_template_directory() . '/endpoints/menu.php';
require_once get_template_directory() . '/endpoints/settings.php';
require_once get_template_directory() . '/endpoints/page_slug.php';

// Add suporte a imagens destacadas
add_theme_support('post-thumbnails');

// Add suporte a menu
function theme_register_menus()
{
    register_nav_menus(array(
        'primary' => 'Menu Principal',
    ));
}
add_action('after_setup_theme', 'theme_register_menus');

// Desativar Gutenberg
add_filter('use_block_editor_for_post', '__return_false', 10);

// Add favicon
function add_favicon()
{
    $favicon_url = get_stylesheet_directory_uri() . '/assets/images/favicon.png';
    echo '<link rel="shortcut icon" href="' . $favicon_url . '" />';
}
add_action('wp_head', 'add_favicon');

// Add admin-style.css
function enqueue_custom_admin_styles()
{
    wp_enqueue_style('admin-styles', get_template_directory_uri() . '/panel-assets/admin-style.css');
}
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_styles');

// Desativar notificações de plugins desatualizados
add_filter('pre_site_transient_update_plugins', '__return_null');



// Add a function to register the page in the admin menu
function register_admin_page() {
    add_menu_page(
        'Trinity Kit', // Page title
        'My Page', // Menu title
        'manage_options', // Capability required to access this page
        'my-admin-page', // Page slug
        'show_admin_page', // Callback function to display the page content
        'dashicons-admin-generic', // Menu icon (see https://developer.wordpress.org/resource/dashicons/)
        1 // Menu position
    );
}
add_action('admin_menu', 'register_admin_page');

// Callback function to display the page content
function show_admin_page() {
    echo '<div class="wrap">';
    echo '<h1>Trinity Kit</h1>';
    // Add your page content here
    echo '</div>';
}
