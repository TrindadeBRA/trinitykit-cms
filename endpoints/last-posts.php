<?php

add_action('rest_api_init', 'custom_register_last_posts_endpoint');

function custom_register_last_posts_endpoint() {
    register_rest_route('trinitykit/v1', '/last-posts', array(
        'methods' => 'GET',
        'callback' => 'custom_get_last_posts',
    ));
}

function custom_get_last_posts() {
    $args = array(
        'posts_per_page' => 3, // número de posts a serem recuperados
        'post_status' => 'publish', // apenas posts publicados
        'orderby' => 'date', // ordenar por data
        'order' => 'DESC', // em ordem decrescente
    );

    $posts_query = new WP_Query($args);

    $posts = array();

    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()) {
            $posts_query->the_post();

            $post_data = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'description' => get_the_excerpt(),
                'datetime' => get_the_date('c'),
                'date' => get_the_date(),
                'href' => get_permalink(),
                'imageUrl' => get_the_post_thumbnail_url(get_the_ID(), 'large'),
                'category' => array(
                    'title' => get_the_category()[0]->name,
                    'href' => get_category_link(get_the_category()[0]->term_id),
                ),
                'author' => array(
                    'name' => get_the_author_meta('display_name'),
                    'role' => get_the_author_meta('description'),
                    'imageUrl' => get_avatar_url(get_the_author_meta('ID')),
                    'href' => get_author_posts_url(get_the_author_meta('ID')),
                ),
            );

            $posts[] = $post_data;
        }
    }

    // Restaura as informações de post globais
    wp_reset_postdata();

    return $posts;
}
