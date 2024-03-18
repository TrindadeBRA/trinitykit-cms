<?php
/**
 * Parent Category Slugs Endpoint
 *
 * This endpoint retrieves slugs of all parent categories.
 *
 * Endpoint URL: /wp-json/trinitykit/v1/all-category-slugs/
 * Method: GET
 */

add_action('rest_api_init', 'register_parent_category_slugs_endpoint');
function register_parent_category_slugs_endpoint() {
    register_rest_route('trinitykit/v1', '/all-category-slugs/', array(
        'methods' => 'GET',
        'callback' => 'get_parent_category_slugs',
    ));
}

function get_parent_category_slugs($request) {
    // Array para armazenar os slugs das categorias pai
    $category_slugs = array();

    // Obtém todas as categorias
    $categories = get_categories();

    // Percorre todas as categorias e verifica se são categorias pai
    foreach ($categories as $category) {
        if ($category->parent == 0) { // Verifica se é uma categoria pai
            // Adiciona o slug da categoria pai ao array
            $category_slugs[] = $category->slug;
        }
    }

    // Retorna uma resposta REST com os slugs das categorias pai
    return new WP_REST_Response(array(
        'parent_category_slugs' => $category_slugs,
    ), 200);
}
