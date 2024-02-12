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
        'Trinity Kit',
        'Trinity Kit',
        'manage_options',
        'my-admin-page',
        'show_admin_page',
        'dashicons-admin-generic',
        1
    );
}
add_action('admin_menu', 'register_admin_page');

function show_admin_page() {
    echo '<div class="wrap">';
    echo '<h1>My Administration Page</h1>';
    // Add your page content here
    echo '<form method="post">';
    echo '<input type="submit" name="deploy_button" value="Deploy Master Branch">';
    echo '</form>';
    echo '</div>';

    if (isset($_POST['deploy_button'])) {
        $result = get_latest_jobs();
        if ($result) {
            echo '<p>Deploy initiated successfully!</p>';
        } else {
            echo '<p>Failed to initiate deploy.</p>';
        }
    }
}

// Function to fetch the latest jobs from GitHub Actions
function get_latest_jobs() {
    $github_token = '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'; // Replace with your GitHub token
    $repo_owner = 'TrindadeBRA'; // Replace with your GitHub username or organization name
    $repo_name = 'trinitykit-cms'; // Replace with your GitHub repository name

    $url = "https://api.github.com/repos/{$repo_owner}/{$repo_name}/actions/runs?status=completed&per_page=5";

    $options = array(
        'http' => array(
            'header' => "Authorization: token $github_token\r\n" .
                        "Content-Type: application/json\r\n",
            'method' => 'GET',
        )
    );

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context); // Suppress errors for file_get_contents

    if ($result !== false) {
        $data = json_decode($result, true);
        return $data['workflow_runs'];
    } else {
        return false;
    }
}
