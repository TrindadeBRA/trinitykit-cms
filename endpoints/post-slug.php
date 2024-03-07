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

    // Get author information
    $author_id = $post->post_author;
    $author_data = get_userdata($author_id);

    // Get author Gravatar
    $author_avatar = get_avatar_url($author_id);

    // Get post categories
    $post_categories = get_the_category($post->ID);

    // Initialize an array to store post data
    $post_data = array(
        'id' => $post->ID,
        'title' => html_entity_decode(get_the_title($post->ID), ENT_QUOTES, 'UTF-8'),
        'content' => apply_filters('the_content', $post->post_content),
        'date' => $post->post_date,
        'author' => array(
            'name' => $author_data->display_name,
            'avatar' => $author_avatar,
            'bio' => get_the_author_meta('description', $author_id),
        ),
        'categories' => array(),
    );


    // Add categories to post data
    foreach ($post_categories as $category) {
        $post_data['categories'][] = array(
            'name' => $category->name,
            'slug' => $category->slug,
        );
    }

    // Get custom fields (ACFs) associated with the post
    $acf_fields = get_fields($post->ID);

    
    // Add custom fields to the post data
    if ($acf_fields) {
        foreach ($acf_fields as $key => $value) {
            $post_data[$key] = $value;
        }
    }

    // Wrap the post data inside another array
    $response = array($post_data);

    // Return the wrapped post data
    return $response;
}