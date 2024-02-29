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
            'title' => 'My Page 1',
            'slug' => 'my-page-1',
            'content' => 'Content of Page 1',
        ),
        array(
            'title' => 'My Page 2',
            'slug' => 'my-page-2',
            'content' => 'Content of Page 2',
        ),
        // Add more pages as needed
    );

    foreach ($pages as $page) {
        create_page_once($page['title'], $page['slug'], $page['content']);
    }

    // Mark that the pages have been created
    // update_option('pages_created', 'created');
}

// Function to create a page only once
function create_page_once($title, $slug, $content) {
    $page = get_page_by_title($title);

    if (empty($page)) {
        $new_page = array(
            'post_type'    => 'page',
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => $content,
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
