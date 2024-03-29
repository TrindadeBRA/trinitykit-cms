<?php
define('themeUrl', get_template_directory_uri());
define('siteUrl', get_site_url());

require_once get_template_directory() . '/preconfig.php';
require_once get_template_directory() . '/endpoints/menu.php';
require_once get_template_directory() . '/endpoints/settings.php';
require_once get_template_directory() . '/endpoints/page-slug.php';
require_once get_template_directory() . '/endpoints/post-slug.php';
require_once get_template_directory() . '/endpoints/latest-posts.php';
require_once get_template_directory() . '/endpoints/talents-bank.php';
require_once get_template_directory() . '/endpoints/all-slugs.php';
require_once get_template_directory() . '/endpoints/all-category-slugs.php';
require_once get_template_directory() . '/endpoints/contact.php';

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

// Desativar notificações de plugins desatualizados
add_filter('pre_site_transient_update_plugins', '__return_null');















// Add a function to register the page in the admin menu
function register_admin_page() {
    add_menu_page(
        'Reconstrução',
        'Reconstrução',
        'manage_options',
        'my-admin-page',
        'show_admin_page',
        'dashicons-controls-repeat',
        2
    );
}
add_action('admin_menu', 'register_admin_page');



function show_admin_page() {
    $github_user = get_theme_mod('github_user');
    $github_repo = get_theme_mod('github_repo');
    $github_token = get_theme_mod('github_token');

    echo '<div class="wrap">';
    echo '<h1>Trinity Kit - Reconstrução do frontend</h1>';
    echo '<p>Reconstruir a aplicação do frontend para gerar arquivos estáticos atualizados com as últimas alterações do WordPress.</p>';
    echo '<form method="post">';
    echo '<input type="submit" name="deploy_button" value="Reconstruir a aplicação" id="redeploy-button">';
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
    echo '<span id="response_area"></span>';
    echo '</div>';

}

function register_custom_scripts() {
    wp_enqueue_script('custom-script', get_template_directory_uri() . '/js/rebuildFrontend.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'register_custom_scripts');

function register_custom_styles() {
    wp_enqueue_style('custom-style', get_template_directory_uri() . '/css/rebuildFrontend.css', array(), '1.0', 'all');
}
add_action('admin_enqueue_scripts', 'register_custom_styles');


// Função para adicionar o botão "Visualizar no Front"
function add_custom_post_preview_button($actions, $post) {
    // Obtém o URL do frontend do aplicativo do tema mod
    $frontend_app_url = get_theme_mod('frontend_app_url');

    // Verifica se o URL do frontend do aplicativo está definido e se é um valor válido
    if ($frontend_app_url && filter_var($frontend_app_url, FILTER_VALIDATE_URL)) {
        // Constrói o novo link de visualização com base no URL do frontend, slug e a parte "/preview/blog/"
        $preview_link = trailingslashit($frontend_app_url) . 'preview/blog?slug=' . $post->post_name;

        // Texto do botão
        $button_text = 'Visualizar no Front';

        // Constrói o HTML do botão de visualização
        $preview_button = '<a href="' . esc_url($preview_link) . '" target="_blank">' . esc_html($button_text) . '</a>';

        // Adiciona o botão de visualização ao array de ações
        $actions['custom_preview'] = $preview_button;
    }

    return $actions;
}

// Adiciona o botão na listagem de posts
add_filter('post_row_actions', 'add_custom_post_preview_button', 10, 2);



function preview_on_admin_bar($wp_admin_bar){
    global $post;
    if ($post && is_admin() && isset($post->ID)) {
        $frontend_app_url = get_theme_mod('frontend_app_url');
        $post_name = $post->post_name;
        $preview_link = trailingslashit($frontend_app_url) . 'preview/blog?slug=' . $post_name;
        $wp_admin_bar->add_node(array(
            'id'    => 'custom_preview_button',
            'title' => 'Visualizar no Front',
            'href'  => $preview_link,
            'meta'  => array('target' => '_blank'),
        ));
    }
}
add_action('admin_bar_menu', 'preview_on_admin_bar', 999);


function pick_your_book_html_github_shortcode() {
    // URL do arquivo HTML no GitHub
    $github_url = 'https://raw.githubusercontent.com/TrindadeBRA/pick-your-book/master/index.html';

    // Obtém o conteúdo do arquivo HTML
    $html_content = wp_remote_get( $github_url );

    // Verifica se a solicitação foi bem-sucedida
    if ( ! is_wp_error( $html_content ) && wp_remote_retrieve_response_code( $html_content ) === 200 ) {
        return wp_remote_retrieve_body( $html_content );
    } else {
        return 'Não foi possível carregar o conteúdo HTML do GitHub.';
    }
}
add_shortcode( 'pick-your-book-html-github', 'pick_your_book_html_github_shortcode' );


function most_read_book_by_year_html_github_shortcode() {
    // URL do arquivo HTML no GitHub
    $github_url = 'https://raw.githubusercontent.com/TrindadeBRA/most-read-book-by-year/master/index.html';

    // Obtém o conteúdo do arquivo HTML
    $html_content = wp_remote_get( $github_url );

    // Verifica se a solicitação foi bem-sucedida
    if ( ! is_wp_error( $html_content ) && wp_remote_retrieve_response_code( $html_content ) === 200 ) {
        return wp_remote_retrieve_body( $html_content );
    } else {
        return 'Não foi possível carregar o conteúdo HTML do GitHub.';
    }
}
add_shortcode( 'most-read-book-by-year-html-github', 'most_read_book_by_year_html_github_shortcode' );
