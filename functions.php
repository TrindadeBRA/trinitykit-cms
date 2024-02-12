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
        'Rebuildar aplicação',
        'Rebuildar aplicação',
        'manage_options',
        'my-admin-page',
        'show_admin_page',
        'dashicons-admin-generic',
        1
    );
}
add_action('admin_menu', 'register_admin_page');



function show_admin_page() {
    $github_user = get_theme_mod('github_user');
    $github_repo = get_theme_mod('github_repo');
    $github_token = get_theme_mod('github_token');

    echo '<div class="wrap">';
    echo '<h1>Trinity Kit</h1>';
    echo '<h3>Rebuildar aplicação</h3>';
    echo '<form method="post">';
    echo '<input type="submit" name="deploy_button" value="Rebuildar a aplicação">';
    echo '<div style="display:none;">';
    echo '<span id="github_user">';
    echo $github_user;
    echo '</span>';
    echo '<span id="github_repo">';
    echo $github_repo;
    echo '</span>';
    echo '<span id="github_token">';
    echo $github_token;
    echo '</span>';
    echo '</div>';
    echo '</form>';
    echo '</div>';

}

function register_custom_scripts() {
    wp_enqueue_script('custom-script', get_template_directory_uri() . '/js/custom-script.js', array('jquery'), '1.0', true);
}

add_action('admin_enqueue_scripts', 'register_custom_scripts');
