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
        'posts_per_page' => 1
    );

    $query = new WP_Query($args);

    // If post not found, return an error
    if (!$query->have_posts()) {
        return new WP_Error('no_post', 'Página não encontrada.', array('status' => 404));
    }

    // Get the first post
    $query->the_post();

    // Get author information
    $author_id = get_the_author_meta('ID');
    $author_data = get_userdata($author_id);

    // Get author Gravatar
    $author_avatar = get_avatar_url($author_id);

    // Get post categories
    $post_categories = get_the_category();

    // Get Yoast SEO data
    $yoast_title = get_post_meta(get_the_ID(), '_yoast_wpseo_title', true);
    $yoast_description = get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true);

    // If Yoast SEO data is empty, use default WordPress title and excerpt
    if (empty($yoast_title)) {
        $yoast_title = get_the_title();
    }
    if (empty($yoast_description)) {
        $yoast_description = get_the_excerpt();
    }

    // Get the post content
    $post_content = get_the_content();

    // Renderize o conteúdo (processando os shortcodes do ACF)
    $content = apply_filters('the_content', $post_content);

    // Initialize an array to store post data
    $post_data = array(
        'id' => get_the_ID(),
        'title' => html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'),
        'content' => $content,
        'post_thumbnail_url' => get_the_post_thumbnail_url(),
        'date' => get_the_date(),
        'slug' => $slug,
        'author' => array(
            'name' => $author_data->display_name,
            'avatar' => $author_avatar,
            'bio' => get_the_author_meta('description', $author_id),
        ),
        'categories' => array(),
        'related_posts' => array(),
        'yoast_title' => html_entity_decode(wp_trim_words($yoast_title, 30), ENT_QUOTES, 'UTF-8'),
        'yoast_description' => html_entity_decode(wp_trim_words($yoast_description, 200), ENT_QUOTES, 'UTF-8'),
    );

    // Get related ACF fields
    $book_component_acf = get_book_component_acf(get_the_ID());

    // Add related ACF fields to the post data
    $post_data['book_component'] = $book_component_acf;

    // Get related posts
    $related_posts = get_related_posts();

    // Add formatted related posts data to the 'related_posts' field
    $post_data['related_posts'] = $related_posts;

    // Add categories to post data
    foreach ($post_categories as $category) {
        $post_data['categories'][] = array(
            'name' => $category->name,
            'slug' => $category->slug,
        );
    }

    // Reset post data
    wp_reset_postdata();

    // Wrap the post data inside another array
    $response = array($post_data);

    // Return the wrapped post data
    return $response;
}

function get_book_component_acf($post_id) {
    $acf_fields = array(
        'nome_do_livro' => get_field('nome_do_livro', $post_id),
        'autor_do_livro' => get_field('autor_do_livro', $post_id),
        'capa_do_livro' => get_field('capa_do_livro', $post_id),
        'link_afiliado' => get_field('link_afiliado', $post_id)
    );
    return $acf_fields;
}

function get_related_posts() {
    // Query random posts
    $args = array(
        'post_type'     => 'post',
        'post_status'   => 'publish',
        'posts_per_page' => 3, // Get 3 random posts
        'orderby'       => 'rand',
    );

    $query = new WP_Query($args);

    // If no posts found, return an empty array
    if (!$query->have_posts()) {
        return array();
    }

    // Initialize an array to store formatted related post data
    $formatted_related_posts = array();

    // Loop through random posts and format their data
    foreach ($query->posts as $related_post) {
        // Get author information
        $author_id = $related_post->post_author;
        $author_data = get_userdata($author_id);
        $author_avatar = get_avatar_url($author_id);

        // Get post categories
        $post_categories = get_the_category($related_post->ID);
        $categories = array();
        foreach ($post_categories as $category) {
            $categories[] = array(
                'name' => $category->name,
                'slug' => $category->slug,
            );
        }

        // Add related post data to the formatted array
        $formatted_related_posts[] = array(
            'id' => $related_post->ID,
            'title' => html_entity_decode($related_post->post_title, ENT_QUOTES, 'UTF-8'),
            'content' => html_entity_decode(wp_trim_words(apply_filters('the_content', $related_post->post_content), 30), ENT_QUOTES, 'UTF-8'), // Using post_content for summary
            'post_thumbnail_url' => get_the_post_thumbnail_url($related_post->ID),
            'date' => get_the_date('', $related_post->ID), // Pass the post ID for date
            'author' => array(
                'name' => $author_data->display_name,
                'avatar_url' => $author_avatar,
            ),
            'categories' => $categories,
            'slug' => get_post_field('post_name', $related_post->ID), // Get post slug
        );
    }

    return $formatted_related_posts;
}


