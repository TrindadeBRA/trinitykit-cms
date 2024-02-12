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
    echo '<input type="submit" name="deploy_button" value="Requisicao">';
    echo '</form>';
    echo '</div>';

    // Check if the deploy_button form is submitted
    if (isset($_POST['deploy_button'])) {
        // Make a GET request to fetch the workflow runs
        $response = wp_remote_get('https://api.github.com/repos/TrindadeBRA/trinitykit/actions/runs?status=completed&per_page=1', array(
            'headers' => array(
                'Authorization' => 'Bearer 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',
            ),
        ));

        // Check if the request was successful
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['workflow_runs'][0]['id'])) {
                $lastRunId = $data['workflow_runs'][0]['id'];

                // Make a POST request to rerun the workflow
                $rerun_response = wp_remote_post('https://api.github.com/repos/TrindadeBRA/trinitykit/actions/runs/' . $lastRunId . '/rerun', array(
                    'headers' => array(
                        'Authorization' => 'Bearer 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',
                    ),
                ));

                // Check if the rerun request was successful
                if (!is_wp_error($rerun_response) && wp_remote_retrieve_response_code($rerun_response) === 201) {
                    echo '<p>Reexecução iniciada com sucesso!</p>';
                } else {
                    echo '<p>Ocorreu um erro ao iniciar a reexecução.</p>';
                }
            } else {
                echo '<p>Nenhuma execução encontrada.</p>';
            }
        } else {
            echo '<p>Ocorreu um erro ao buscar as execuções.</p>';
        }
    }
}
