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





// Adicione uma função para registrar a página no menu do painel de administração
function registrar_pagina_administracao() {
    add_menu_page(
        'Minha Página de Administração', // Título da página
        'Minha Página', // Título do menu
        'manage_options', // Capacidade necessária para acessar esta página
        'minha-pagina-admin', // Slug da página
        'mostrar_pagina_admin', // Função de callback para exibir o conteúdo da página
        'dashicons-admin-generic', // Ícone do menu (veja https://developer.wordpress.org/resource/dashicons/)
        30 // Posição do menu
    );
}
add_action('admin_menu', 'registrar_pagina_administracao');

// Função de callback para exibir o conteúdo da página
function mostrar_pagina_admin() {
    echo '<div class="wrap">';
    echo '<h1>Minha Página de Administração</h1>';
    // Adicione aqui o conteúdo da sua página
    echo '</div>';
}
