<?php
/**
 * Latest Posts Endpoint
 *
 * This endpoint retrieves data about the latest 3 WordPress posts.
 *
 * Endpoint URL: /wp-json/trinitykit/v1/latest-posts/
 * Method: GET
 */

/**
 * Register Latest Posts Endpoint
 *
 * Registers the latest posts endpoint with WordPress REST API.
 */
add_action('rest_api_init', 'register_latest_posts_endpoint');
function register_latest_posts_endpoint() {
    register_rest_route('trinitykit/v1', '/latest-posts/', array(
        'methods' => 'GET',
        'callback' => 'get_latest_posts',
    ));
}
/**
 * Get Latest Posts
 *
 * Retrieves data about the latest 3 posts and prepares it for response.
 *
 * @return WP_REST_Response|WP_Error Response object containing latest posts data or error message.
 */
function get_latest_posts() {
    // Query the latest 3 posts
    $latest_posts_query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    // If no posts found, return an error
    if (!$latest_posts_query->have_posts()) {
        return new WP_Error('no_posts', 'Nenhum post encontrado.', array('status' => 404));
    }

    // Initialize an array to store posts data
    $posts_data = array();

    // Loop through each post and add its data to the array
    while ($latest_posts_query->have_posts()) {
        $latest_posts_query->the_post();

        // Get post data
        $post_data = array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'content' => wp_trim_words(get_the_content(), 30),
            'thumbnail_url' => get_the_post_thumbnail_url(get_the_ID(), 'large'),
            'date' => get_the_date(),
            'category' => get_the_category()[0]->name,
            'author_name' => get_the_author_meta('display_name'),
            'author_photo' => get_avatar_url(get_the_author_meta('user_email')), 
            'slug' => basename(get_permalink()),
        );

        // Add post data to the array
        $posts_data[] = $post_data;
    }

    // Get the blog page
    $blog_page = get_page_by_path('blog');
    if (!$blog_page) {
        return new WP_Error('not_found', 'Página com slug "blog" não encontrada.', array('status' => 404));
    }
    $post_id = $blog_page->ID;

    // Get ACFs for the blog page
    $acfs = get_fields($post_id);

    // Reset post data
    wp_reset_postdata();

    // Return a REST response with the latest posts data and ACFs
    return new WP_REST_Response(array(
        'custom_fields' => $acfs,
        'recent_posts' => $posts_data,
    ), 200);
}
