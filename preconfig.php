<?php

function create_initial_pages_and_import_acf() {
    // Check if the pages and ACF settings have already been created/imported previously
    $imported = get_option('pages_and_acf_imported');

    // If the pages and ACF settings have already been imported, do nothing
    if ($imported === 'imported') {
        return;
    }

    // Create the initial pages
    create_initial_pages();

    // Import ACF settings from acf-export.json
    $acf_json_path = get_template_directory() . '/acf-export/acf-export.json';
    if (file_exists($acf_json_path)) {
        $json_data = file_get_contents($acf_json_path);
        if ($json_data !== false) {
            $settings = json_decode($json_data, true);
            if (function_exists('acf_add_local_field_group')) {
                foreach ($settings as $setting) {
                    acf_add_local_field_group($setting);
                }
            }
        }
    }

    // Mark that the pages and ACF settings have been created/imported
    update_option('pages_and_acf_imported', 'imported');
}

// Function to create initial pages
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

// Execute the creation of initial pages and ACF import
create_initial_pages_and_import_acf();
