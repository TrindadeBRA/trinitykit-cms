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
    echo '<button id="deploy_button">Deploy Master Branch</button>';
    echo '</div>';

    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("deploy_button").addEventListener("click", function() {
            deployMasterBranch();
        });

        function deployMasterBranch() {
            fetch('/wp-json/myplugin/deploy-master-branch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
                // Handle successful response
                alert('Deploy initiated successfully!');
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
                // Handle error
                alert('Failed to initiate deploy.');
            });
        }
    });
    </script>
    <?php
}

// Add REST API endpoint to initiate deployment
add_action('rest_api_init', function() {
    register_rest_route('myplugin/v1', '/deploy-master-branch', array(
        'methods' => 'POST',
        'callback' => 'deploy_master_branch',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
});

// Function to deploy the master branch using GitHub API
function deploy_master_branch() {
    $github_token = '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'; // Replace with your GitHub token
    $repo_owner = 'TrindadeBRA'; // Replace with your GitHub username or organization name
    $repo_name = 'trinitykit'; // Replace with your GitHub repository name

    $url = "https://api.github.com/repos/{$repo_owner}/{$repo_name}/actions/workflows/deploy.yml/dispatches";

    $data = array(
        'ref' => 'refs/heads/master'
    );

    $options = array(
        'http' => array(
            'header' => "Authorization: token $github_token\r\n" .
                        "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        )
    );

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context); // Suppress errors for file_get_contents

    if ($result !== false) {
        return array('success' => true);
    } else {
        return array('success' => false);
    }
}
