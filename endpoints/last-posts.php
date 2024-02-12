<?php
/*
Plugin Name: Últimos Posts Endpoint
Description: Adiciona um endpoint personalizado para obter os últimos 3 posts publicados.
*/

// Adiciona o endpoint personalizado
add_action('rest_api_init', 'register_last_posts_endpoint');

function register_last_posts_endpoint() {
    register_rest_route('trinitykit/v1', '/posts', array(
        'methods' => 'GET',
        'callback' => 'get_last_posts',
    ));
}

// Função para obter os últimos 3 posts publicados
function get_last_posts($request) {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    $posts = get_posts($args);

    $response = array();

    foreach ($posts as $post) {
        $response[] = array(
            'title' => $post->post_title,
            'content' => $post->post_content,
            'link' => get_permalink($post->ID),
        );
    }

    return new WP_REST_Response($response, 200);
}
