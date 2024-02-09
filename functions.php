<?php
define('themeUrl', get_template_directory_uri());
define('siteUrl', get_site_url());

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

// /wp-json/trinitykit/header
add_action('rest_api_init', 'register_custom_menu_endpoint');
function register_custom_menu_endpoint() {
    register_rest_route('trinitykit/v1', '/menu/', array(
        'methods' => 'GET',
        'callback' => 'get_custom_menu_data',
    ));
}
function get_custom_menu_data() {
    $menu_locations = get_nav_menu_locations();
    $menu_id = $menu_locations['primary'];
    $menu_items = wp_get_nav_menu_items($menu_id);
    if (!$menu_items) {
        return new WP_Error('no_menu_items', 'Não foi possível encontrar itens de menu.', array('status' => 404));
    }
    $processed_menu = array();
    foreach ($menu_items as $menu_item) {
        $post_slug = get_post_field('post_name', $menu_item->object_id);

        if($post_slug === "home"){
            $post_slug = "/";
        }

        $processed_menu[] = array(
            'id' => $menu_item->ID,
            'title' => $menu_item->title,
            'slug' => $post_slug,
            'url' => $menu_item->url,
        );
    }
    return new WP_REST_Response($processed_menu, 200);
}


add_action('rest_api_init', 'register_page_endpoint');

function register_page_endpoint() {
    register_rest_route('trinitykit/v1', '/(?P<slug>[a-zA-Z0-9-]+)/', array(
        'methods' => 'GET',
        'callback' => 'get_page_data',
    ));
}

function get_page_data($data) {
    $slug = $data['slug']; // Obter a slug da página a partir dos dados da requisição

    // Obter a página pelo slug
    $page = get_page_by_path($slug);

    if (!$page) {
        return new WP_Error('no_page', 'Página não encontrada.', array('status' => 404));
    }

    // Obter dados da página
    $page_data = array(
        'id' => $page->ID,
        'title' => get_the_title($page->ID),
        'content' => apply_filters('the_content', $page->post_content),
        'date' => $page->post_date,
    );

    // Obter campos personalizados (ACFs) vinculados à página
    $acf_fields = get_fields($page->ID);

    // Adicionar campos personalizados aos dados da página
    if ($acf_fields) {
        foreach ($acf_fields as $key => $value) {
            $page_data[$key] = $value;
        }
    }

    return new WP_REST_Response($page_data, 200);
}
