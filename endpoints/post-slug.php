<?php
/**
 * Post Endpoint
 *
 * This endpoint retrieves data about a specific WordPress page.
 * It returns information such as the page title, content, date, and any custom fields associated with the page.
 *
 * Endpoint URL: /wp-json/trinitykit/v1/page/{post_slug}/
 * Method: GET
 * Parameters:
 *   - {post_slug}: The slug of the page to retrieve.
 */

/**
 * Register Page Endpoint
 *
 * Registers the page endpoint with WordPress REST API.
 */
add_action('rest_api_init', 'register_post_endpoint');
function register_post_endpoint() {
    register_rest_route('trinitykit/v1', '/post/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_post_data',
    ));
}

/**
 * Get Page Data
 *
 * Retrieves data about the specified page and prepares it for response.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error Response object containing page data or error message.
 */
function get_post_data($request) {
    // Get the post slug from the request parameters
    $slug = $request['slug'];

    // Get the post by its slug
    $post = get_page_by_path($slug, OBJECT, 'post');

    // If post not found, return an error
    if (!$post) {
        return new WP_Error('no_post', 'Página não encontrada.', array('status' => 404));
    }

    // Initialize an array to store post data
    $post_data = array(
        'id' => $post->ID,
        'title' => html_entity_decode(get_the_title($post->ID), ENT_QUOTES, 'UTF-8'),
        'content' => apply_filters('the_content', $post->post_content),
        'date' => $post->post_date,
    );

    // Get custom fields (ACFs) associated with the post
    $acf_fields = get_fields($post->ID);

    // Add custom fields to the post data
    if ($acf_fields) {
        foreach ($acf_fields as $key => $value) {
            $post_data[$key] = $value;
        }
    }

    // Return a REST response with the post data
    return new WP_REST_Response($post_data, 200);
}

