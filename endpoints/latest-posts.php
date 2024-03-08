<?php
/**
 * Latest Posts Endpoint
 *
 * This endpoint retrieves data about the latest 3 WordPress posts.
 *
 * Endpoint URL: /wp-json/trinitykit/v1/latest-posts/
 * Method: GET
 */

 add_action('rest_api_init', 'register_latest_posts_endpoint');
 function register_latest_posts_endpoint() {
     register_rest_route('trinitykit/v1', '/latest-posts/', array(
         'methods' => 'GET',
         'callback' => 'get_latest_posts',
         'args' => array(
             'page' => array(
                 'default' => 1,
                 'sanitize_callback' => 'absint',
             ),
             'per_page' => array(
                 'default' => 3,
                 'sanitize_callback' => 'absint',
             ),
         ),
     ));
 }


function get_latest_posts($request) {
    // Obtenha os parâmetros da solicitação
    $params = $request->get_params();
    $page = isset($params['page']) ? $params['page'] : 1;
    $posts_per_page = isset($params['per_page']) ? $params['per_page'] : 3;

    // Calcule o deslocamento
    $offset = ($page - 1) * $posts_per_page;

    // Consulte os últimos posts
    $latest_posts_query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => $posts_per_page,
        'offset' => $offset,
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    // Se nenhum post for encontrado, retorne um erro
    if (!$latest_posts_query->have_posts()) {
        return new WP_Error('no_posts', 'Nenhum post encontrado.', array('status' => 404));
    }

    // Inicialize um array para armazenar os dados dos posts
    $posts_data = array();

    // Loop através de cada post e adicione seus dados ao array
    while ($latest_posts_query->have_posts()) {
        $latest_posts_query->the_post();

        // Obtenha os dados do post
        $post_id = get_the_ID();
        $title = get_the_title();
        $content = get_the_content();
        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'large');
        $date = get_the_date('j \d\e F \d\e Y');
        $categories = get_the_category();
        $author_name = get_the_author_meta('display_name');
        $author_photo = get_avatar_url(get_the_author_meta('user_email')); 
        $slug = basename(get_permalink());

        // Adicione os dados do post ao array
        $posts_data[] = array(
            'id' => $post_id,
            'title' => html_entity_decode(wp_trim_words($title, 30), ENT_QUOTES, 'UTF-8'),
            'content' => html_entity_decode(wp_trim_words(apply_filters('the_content', $content), 30), ENT_QUOTES, 'UTF-8'),
            'thumbnail_url' => $thumbnail_url,
            'date' => $date,
            'category' => !empty($categories) ? $categories[0]->name : '',
            'author_name' => $author_name,
            'author_photo' => $author_photo,
            'slug' => $slug,
        );
    }

    // Obtenha a página do blog
    $blog_page = get_page_by_path('blog');
    if (!$blog_page) {
        return new WP_Error('not_found', 'Página com slug "blog" não encontrada.', array('status' => 404));
    }
    $post_id = $blog_page->ID;

    // Obtenha os ACFs para a página do blog
    $acfs = get_fields($post_id);

    // Restaure os dados originais do post
    wp_reset_postdata();

    // Retorne uma resposta REST com os dados dos últimos posts e os ACFs
    return new WP_REST_Response(array(
        'custom_fields' => $acfs,
        'recent_posts' => $posts_data,
    ), 200);
}


add_action('rest_api_init', 'register_total_pages_endpoint');
function register_total_pages_endpoint() {
    register_rest_route('trinitykit/v1', '/total-pages/', array(
        'methods' => 'GET',
        'callback' => 'get_total_pages',
        'args' => array(
            'per_page' => array(
                'default' => 3,
                'sanitize_callback' => 'absint',
            ),
        ),
    ));
}

function get_total_pages($request) {
    $params = $request->get_params();
    $posts_per_page = isset($params['per_page']) ? $params['per_page'] : 3;

    // Query the total number of posts
    $total_posts_query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => -1, // Query all posts
    ));

    // Calculate total pages
    $total_pages = ceil($total_posts_query->found_posts / $posts_per_page);

    // Return total pages
    return new WP_REST_Response(array(
        'total_pages' => $total_pages,
    ), 200);
}
