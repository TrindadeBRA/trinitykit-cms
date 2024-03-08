<?php
/**
 * Latest Posts Endpoint
 *
 * This endpoint retrieves data about the latest 3 WordPress posts.
 *
 * Endpoint URL: /wp-json/trinitykit/v1/all-slugs/
 * Method: GET
 */

 add_action('rest_api_init', 'register_all_slugs_endpoint');
 function register_all_slugs_endpoint() {
     register_rest_route('trinitykit/v1', '/all-slugs/', array(
         'methods' => 'GET',
         'callback' => 'get_all_slugs',
     ));
 }


 function get_all_slugs($request) {

    $count = 15;

    // Obtém os últimos posts
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $count,
        'order' => 'DESC',
        'orderby' => 'date',
    );

    $posts_query = new WP_Query($args);
    
    // Array para armazenar os slugs
    $post_slugs = array();

    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()) {
            $posts_query->the_post();
            // Define $post global para permitir o acesso às propriedades do post
            global $post;
            // Adiciona o slug do post atual ao array
            $post_slugs[] = $post->post_name;
        }
        wp_reset_postdata();
    }

    // Retorna uma resposta REST com os slugs dos últimos posts
    return new WP_REST_Response(array(
        'slugs' => $post_slugs,
    ), 200);
}
