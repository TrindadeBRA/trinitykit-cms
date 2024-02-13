<?php
/**
 * Custom Menu Endpoint
 *
 * This endpoint retrieves data about a custom menu registered in WordPress.
 * It returns an array of menu items with their respective IDs, titles, slugs, and URLs.
 *
 * Endpoint URL: /wp-json/trinitykit/v1/menu/
 * Method: GET
 */

/**
 * Register Custom Menu Endpoint
 *
 * Registers the custom menu endpoint with WordPress REST API.
 */
add_action('rest_api_init', 'register_custom_menu_endpoint');
function register_custom_menu_endpoint() {
    register_rest_route('trinitykit/v1', '/menu/', array(
        'methods' => 'GET',
        'callback' => 'get_custom_menu_data',
    ));
}

/**
 * Get Custom Menu Data
 *
 * Retrieves data about the custom menu and prepares it for response.
 *
 * @return WP_REST_Response|WP_Error Response object containing menu data or error message.
 */
function get_custom_menu_data() {
    // Get the locations of registered menus
    $menu_locations = get_nav_menu_locations();

    // Get the ID of the primary menu
    $menu_id = $menu_locations['primary'];

    // Get the menu items for the primary menu
    $menu_items = wp_get_nav_menu_items($menu_id);

    // If no menu items found, return an error
    if (!$menu_items) {
        return new WP_Error('no_menu_items', 'Não foi possível encontrar itens de menu.', array('status' => 404));
    }

    // Initialize an array to store processed menu items
    $processed_menu = array();

    // Loop through each menu item and process its data
    foreach ($menu_items as $menu_item) {
        // Get the slug of the associated post
        $post_slug = get_post_field('post_name', $menu_item->object_id);

        // If the post slug is "home", set it to "/"
        if($post_slug === "home"){
            $post_slug = "";
        }

        // Add the processed menu item to the array
        $processed_menu[] = array(
            'id' => $menu_item->ID,
            'title' => $menu_item->title,
            'slug' => $post_slug,
            'url' => $menu_item->url,
        );
    }

    // Return a REST response with the processed menu data
    return new WP_REST_Response($processed_menu, 200);
}
