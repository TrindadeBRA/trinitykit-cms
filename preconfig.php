<?php

function create_initial_pages() {
    // Check if the pages have already been created previously
    $pages_created = get_option('pages_created');

    // If the pages have already been created, do nothing
    if ($pages_created === 'created') {
        return;
    }

    // Create the pages
    $pages = array(
        array(
            'title' => 'Home',
            'slug' => 'home'
        ),
        array(
            'title' => 'Nosso Time',
            'slug' => 'nosso-time',
        ),
        array(
            'title' => 'Trabalhe Conosco',
            'slug' => 'trabalhe-conosco',
        ),
        array(
            'title' => 'Blog',
            'slug' => 'blog',
        ),
        // Add more pages as needed
    );

    foreach ($pages as $page) {
        create_page_once($page['title'], $page['slug']);
    }

    if (function_exists("acf_add_local_field_group")) {
        $acf_json_data = locate_template("/acf-export/acf-export.json");
        $custom_fields = $acf_json_data ? json_decode(file_get_contents($acf_json_data), true) : array();

        foreach ($custom_fields as $custom_field) {
            acf_add_local_field_group($custom_field);
        }
    }


    // Mark that the pages have been created
    update_option('pages_created', 'created');
}

// Function to create a page only once
function create_page_once($title, $slug) {
    $page = get_page_by_title($title);

    if (empty($page)) {
        $new_page = array(
            'post_type'    => 'page',
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_status'  => 'publish',
        );

        $page_id = wp_insert_post($new_page);

        if ($page_id) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// Execute the creation of initial pages
create_initial_pages();
