<?php
/**
 * Page Endpoint
 *
 * This endpoint retrieves data about a specific WordPress page.
 * It returns information such as the page title, content, date, and any custom fields associated with the page.
 *
 * Endpoint URL: /wp-json/trinitykit/v1/page/{page_slug}/
 * Method: GET
 * Parameters:
 *   - {page_slug}: The slug of the page to retrieve.
 */

/**
 * Register Page Endpoint
 *
 * Registers the page endpoint with WordPress REST API.
 */
add_action('rest_api_init', 'register_page_endpoint');
function register_page_endpoint() {
    register_rest_route('trinitykit/v1', '/page/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_page_data',
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
function get_page_data($request) {
    // Get the page slug from the request parameters
    $slug = $request['slug'];

    // Get the page by its slug
    $page = get_page_by_path($slug);

    // If page not found, return an error
    if (!$page) {
        return new WP_Error('no_page', 'Página não encontrada.', array('status' => 404));
    }

    // Initialize an array to store page data
    $page_data = array(
        'id' => $page->ID,
        'title' => get_the_title($page->ID),
        'content' => apply_filters('the_content', $page->post_content),
        'date' => $page->post_date,
    );

    // Get Yoast SEO data
    $yoast_title = get_post_meta($page->ID, '_yoast_wpseo_title', true);
    $yoast_description = get_post_meta($page->ID, '_yoast_wpseo_metadesc', true);

    // If Yoast SEO data is empty, use default WordPress title and excerpt
    if (empty($yoast_title)) {
        $yoast_title = get_the_title($page->ID);
    }
    if (empty($yoast_description)) {
        // Sanitize content and limit to 150 characters with ellipsis
        $sanitized_content = wp_strip_all_tags($page->post_content);
        $yoast_description = mb_substr($sanitized_content, 0, 150);
        if (mb_strlen($sanitized_content) > 150) {
            $yoast_description .= '...';
        }
    }

    $page_data['yoast_title'] = $yoast_title;
    $page_data['yoast_description'] = $yoast_description;

    // Get custom fields (ACFs) associated with the page
    $acf_fields = get_fields($page->ID);

    // Add custom fields to the page data
    if ($acf_fields) {
        foreach ($acf_fields as $key => $value) {
            $page_data[$key] = $value;
        }
    }

    // Return a REST response with the page data
    return new WP_REST_Response($page_data, 200);
}

