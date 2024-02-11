<?php
/**
 * Page Endpoint
 *
 * This endpoint retrieves data about a specific WordPress page.
 * It returns information such as the page title, content, date, and any custom fields associated with the page.
 *
 * Endpoint URL: /wp-json/trinitykit/v1/{page_slug}/
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
    register_rest_route('trinitykit/v1', '/(?P<slug>[a-zA-Z0-9-]+)/', array(
        'methods' => 'GET',
        'callback' => 'get_page_data',
    ));
}

/**
 * Get Page Data
 *
 * Retrieves data about the specified page and prepares it for response.
 *
 * @param array $data The data from the request, including the page slug.
 * @return WP_REST_Response|WP_Error Response object containing page data or error message.
 */
function get_page_data($data) {
    // Get the page slug from the request data
    $slug = $data['slug'];

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
