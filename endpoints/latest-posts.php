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
            'content' => apply_filters('the_content', get_the_content()),
            'date' => get_the_date(),
        );

        // Add post data to the array
        $posts_data[] = $post_data;
    }

    // Reset post data
    wp_reset_postdata();

    // Return a REST response with the latest posts data
    return new WP_REST_Response($posts_data, 200);
}
