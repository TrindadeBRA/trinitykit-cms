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
    $github_user = get_theme_mod('github_user');
    $github_repo = get_theme_mod('github_repo');
    $github_token = get_theme_mod('github_token');

    echo '<div class="wrap">';
    echo '<h1>My Administration Page</h1>';
    // Add your page content here
    echo '<form method="post">';
    echo '<input type="submit" name="deploy_button" value="Requisicao">';
    echo '<br>';
    echo '<span id="github_user">';
    echo $github_user;
    echo '</span>';
    echo '<br>';
    echo '<span id="github_repo">';
    echo $github_repo;
    echo '</span>';
    echo '<br>';
    echo '<span id="github_token">';
    echo $github_token;
    echo '</span>';
    echo '</form>';
    echo '</div>';

    // Add your JavaScript code here
    echo '<script>';
    echo 'jQuery(document).ready(function($) {';
    echo '$(\'input[name="deploy_button"]\').on(\'click\', function(e) {';
    echo 'e.preventDefault();';
    echo 'var github_user = $(\'#github_user\').text();';
    echo 'var github_repo = $(\'#github_repo\').text();';
    echo 'var github_token = $(\'#github_token\').text();';
    echo 'console.log("github_user", github_user);';
    echo 'console.log("github_repo", github_repo);';
    echo 'console.log("github_token", github_token);';
    echo '$.ajax({';
    echo 'url: \'https://api.github.com/repos/\' + github_user + \'/\' + github_repo + \'/actions/runs?status=completed&per_page=1\',';
    echo 'success: function(response) {';
    echo 'const lastRunId = response.workflow_runs[0].id;';
    echo 'console.log(">>>", lastRunId);';
    echo '$.ajax({';
    echo 'url: \'https://api.github.com/repos/\' + github_user + \'/\' + github_repo + \'/actions/runs/\' + lastRunId + \'/rerun\',';
    echo 'type: \'POST\',';
    echo 'headers: {';
    echo '\'Authorization\': \'Bearer \' + github_token';
    echo '},';
    echo 'success: function(response) {';
    echo 'console.log("Reexecução iniciada com sucesso!");';
    echo '},';
    echo 'error: function(xhr, status, error) {';
    echo 'console.error(status + \': \' + error);';
    echo '}';
    echo '});';
    echo '},';
    echo 'error: function(xhr, status, error) {';
    echo 'console.error(status + \': \' + error);';
    echo '}';
    echo '});';
    echo '});';
    echo '});';
    echo '</script>';
}
