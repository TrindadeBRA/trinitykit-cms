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

    // Query posts by slug
    $args = array(
        'name'        => $slug,
        'post_type'   => 'post',
        'post_status' => 'publish',
        'numberposts' => 1
    );

    $posts = get_posts($args);

    // If post not found, return an error
    if (empty($posts)) {
        return new WP_Error('no_post', 'Página não encontrada.', array('status' => 404));
    }

    // Get the first post
    $post = $posts[0];

    // Get author information
    $author_id = $post->post_author;
    $author_data = get_userdata($author_id);

    // Get author Gravatar
    $author_avatar = get_avatar_url($author_id);

    // Get post categories
    $post_categories = get_the_category($post->ID);

    // Get Yoast SEO data
    $yoast_title = get_post_meta($post->ID, '_yoast_wpseo_title', true);
    $yoast_description = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);

    // If Yoast SEO data is empty, use default WordPress title and excerpt
    if (empty($yoast_title)) {
        $yoast_title = get_the_title($post->ID);
    }
    if (empty($yoast_description)) {
        $yoast_description = get_the_excerpt($post->ID);
    }

    // Obtenha o conteúdo do post
    $post_content = get_post_field('post_content', $post->ID);
    
    // Verifique se há shortcodes do ACF no conteúdo do post
    if (strpos($post_content, '[acf') !== false) {
        // Se houver shortcodes do ACF, use do_shortcode para interpretá-los
        $post_content = do_shortcode($post_content);
    }
    
    // Use apply_filters para processar outros filtros, como the_content
    $content = apply_filters('the_content', $post_content);

    // Initialize an array to store post data
    $post_data = array(
        'id' => $post->ID,
        'title' => html_entity_decode(get_the_title($post->ID), ENT_QUOTES, 'UTF-8'),
        'content' => $content,
        'post_thumbnail_url' => get_the_post_thumbnail_url($post->ID),
        'date' => $post->post_date,
        'author' => array(
            'name' => $author_data->display_name,
            'avatar' => $author_avatar,
            'bio' => get_the_author_meta('description', $author_id),
        ),
        'categories' => array(),
        'yoast_title' => html_entity_decode(wp_trim_words($yoast_title, 30), ENT_QUOTES, 'UTF-8'),
        'yoast_description' => html_entity_decode(wp_trim_words($yoast_description, 200), ENT_QUOTES, 'UTF-8'),
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
