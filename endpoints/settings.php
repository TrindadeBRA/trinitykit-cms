<?php

/**
 * Register a custom REST API endpoint for fetching theme settings.
 *
 * This function registers a custom REST API endpoint at '/custom-theme/v1/settings'
 * which can be accessed via HTTP GET request to fetch theme settings.
 *
 * @since 1.0.0
 */
function custom_theme_api_endpoint() {
    register_rest_route('trinitykit/v1', '/settings', array(
        'methods' => 'GET',
        'callback' => 'get_custom_settings_data',
    ));
}

/**
 * Callback function to retrieve theme settings.
 *
 * This function retrieves various theme settings such as site title, description,
 * WhatsApp URL, URL da aplicação frontend, and Google Analytics ID, and returns
 * them as a JSON response.
 *
 * @since 1.0.0
 *
 * @return WP_REST_Response The REST response containing theme settings.
 */
function get_custom_settings_data() {
    $settings = array(
        'title' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'whatsapp_url' => get_theme_mod('whatsapp_url'),
        'frontend_app_url' => get_theme_mod('frontend_app_url'),
        'google_analytics_id' => get_theme_mod('google_analytics_id', 'G-XXXXXXX'),
    );

    return rest_ensure_response($settings);
}


// Register the custom API endpoint
add_action('rest_api_init', 'custom_theme_api_endpoint');
/**
 * Add custom fields to WordPress Customizer.
 *
 * This function adds custom fields to the WordPress Customizer
 * for setting a WhatsApp URL, URL da aplicação frontend, and Google Analytics ID.
 *
 * @since 1.0.0
 *
 * @param WP_Customize_Manager $wp_customize The WordPress Customizer instance.
 */
function my_theme_add_custom_fields($wp_customize) {
    // Section for custom fields
    $wp_customize->add_section('my_custom_settings_section', array(
        'title' => __('TrinityKit Settings', 'my-theme'),
        'priority' => 30,
    ));

    // Field for WhatsApp URL
    $wp_customize->add_setting('whatsapp_url', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('whatsapp_url', array(
        'label' => __('WhatsApp URL', 'my-theme'),
        'section' => 'my_custom_settings_section',
        'settings' => 'whatsapp_url',
        'description' => __('Entre com a URL do WhatsApp.', 'my-theme'),
        'type' => 'url',
    ));

    // Field for URL da aplicação frontend
    $wp_customize->add_setting('frontend_app_url', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('frontend_app_url', array(
        'label' => __('Frontend URL', 'my-theme'),
        'section' => 'my_custom_settings_section',
        'settings' => 'frontend_app_url',
        'type' => 'url',
        'description' => __('URL da aplicação frontend.', 'my-theme'),

    ));

    // Field for Github - User
    $wp_customize->add_setting('github_user', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('github_user', array(
        'label' => __('Github User', 'my-theme'),
        'section' => 'my_custom_settings_section',
        'settings' => 'github_user',
        'type' => 'text',
        'description' => __('Github user do front.', 'my-theme'),

    ));

    // Field for Github - Repo
    $wp_customize->add_setting('github_repo', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('github_repo', array(
        'label' => __('Github Repo', 'my-theme'),
        'section' => 'my_custom_settings_section',
        'settings' => 'github_repo',
        'type' => 'text',
        'description' => __('Github repo do front.', 'my-theme'),

    ));

    // Field for Github - Token
    $wp_customize->add_setting('github_token', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('github_token', array(
        'label' => __('Github Token', 'my-theme'),
        'section' => 'my_custom_settings_section',
        'settings' => 'github_token',
        'type' => 'text',
        'description' => __('Github token do front.', 'my-theme'),

    ));

    // Field for Google Analytics ID
    $wp_customize->add_setting('google_analytics_id', array(
        'default' => 'G-XXXXXXX',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('google_analytics_id', array(
        'label' => __('Google Analytics ID', 'my-theme'),
        'section' => 'my_custom_settings_section',
        'settings' => 'google_analytics_id',
        'type' => 'text',
        'description' => __('Entre com o seu Google Analytics ID Ex. G-XXXXXXX.', 'my-theme'),
    ));
}
add_action('customize_register', 'my_theme_add_custom_fields');
