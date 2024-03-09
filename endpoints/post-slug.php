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

    // Get related posts
    $related_posts = get_related_posts(get_the_ID());

    // Add formatted related posts data to the 'related_posts' field
    $post_data['related_posts'] = $related_posts;

    // Add categories to post data
    foreach ($post_categories as $category) {
        $post_data['categories'][] = array(
            'name' => $category->name,
            'slug' => $category->slug,
        );
    }

    // Get custom fields (ACFs) associated with the post
    $acf_fields = get_fields();

    // Add custom fields to the post data
    if ($acf_fields) {
        foreach ($acf_fields as $key => $value) {
            $post_data[$key] = $value;
        }
    }

    // Reset post data
    wp_reset_postdata();

    // Wrap the post data inside another array
    $response = array($post_data);

    // Return the wrapped post data
    return $response;
}

function get_related_posts($post_id, $exclude_ids = array(), $count = 3) {
    // Get post categories
    $post_categories = get_the_category($post_id);

    // Define an array to store related posts
    $related_posts = array();

    // Loop through each category of the post
    foreach ($post_categories as $category) {
        // Query related posts by category
        $related_args = array(
            'category_name' => $category->slug,
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'posts_per_page' => $count, // Get specified number of related posts
            'post__not_in'  => array_merge(array($post_id), $exclude_ids), // Exclude the current post and any provided IDs to exclude
        );

        $related_query = new WP_Query($related_args);

        // If related posts found, merge them into the $related_posts array
        if ($related_query->have_posts()) {
            $related_posts = array_merge($related_posts, $related_query->posts);
        }
    }

    // If not enough related posts found, query random posts
    if (count($related_posts) < $count) {
        $remaining_count = $count - count($related_posts);
        $random_args = array(
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'posts_per_page' => $remaining_count, // Calculate remaining posts needed
            'orderby'       => 'rand',
            'post__not_in'  => array_merge(array($post_id), wp_list_pluck($related_posts, 'ID'), $exclude_ids), // Exclude current post and already related posts
        );

        $random_query = new WP_Query($random_args);

        // Merge random posts into the $related_posts array
        $related_posts = array_merge($related_posts, $random_query->posts);
    }

    // Initialize an array to store formatted related post data
    $formatted_related_posts = array();

    // Loop through related posts and format their data
    foreach ($related_posts as $related_post) {

        $author_id = $related_post->post_author;
        $author_data = get_userdata($author_id);
        $author_avatar = get_avatar_url($author_id);

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
    }

    return $formatted_related_posts;
}
