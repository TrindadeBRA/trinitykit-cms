<?php

/**
 * Registers the custom post type "Contact Form".
 *
 * This function registers a custom post type called "Contact Form" with custom labels and arguments.
 *
 * @since 1.0.0
 */
function register_contact_form_post_type() {
    $labels = array(
        'name'                  => _x( 'Formulários de Contato', 'Nome do tipo de post' ),
        'singular_name'         => _x( 'Formulário de Contato', 'Nome singular do tipo de post' ),
        'menu_name'             => _x( 'Form. de Contato', 'Nome do menu' ),
        'add_new'               => _x( 'Adicionar Novo', 'Novo item' ),
        'add_new_item'          => __( 'Adicionar Novo Formulário de Contato' ),
        'edit_item'             => __( 'Editar Formulário de Contato' ),
        'view_item'             => __( 'Ver Formulário de Contato' ),
        'all_items'             => __( 'Todos os Formulários de Contato' ),
        'search_items'          => __( 'Procurar Formulários de Contato' ),
        'not_found'             => __( 'Nenhum Formulário de Contato encontrado' ),
        'not_found_in_trash'    => __( 'Nenhum Formulário de Contato encontrado na lixeira' ),
        'featured_image'        => _x( 'Imagem de Destaque', 'Formulário de Contato' ),
        'set_featured_image'    => _x( 'Definir imagem de destaque', 'Formulário de Contato' ),
        'remove_featured_image' => _x( 'Remover imagem de destaque', 'Formulário de Contato' ),
        'use_featured_image'    => _x( 'Usar como imagem de destaque', 'Formulário de Contato' ),
        'archives'              => _x( 'Arquivos de Formulários de Contato', 'Formulário de Contato' ),
        'insert_into_item'      => _x( 'Inserir em Formulário de Contato', 'Formulário de Contato' ),
        'uploaded_to_this_item' => _x( 'Enviado para este Formulário de Contato', 'Formulário de Contato' ),
        'filter_items_list'     => _x( 'Filtrar lista de Formulários de Contato', 'Formulário de Contato' ),
        'items_list_navigation' => _x( 'Navegação lista de Formulários de Contato', 'Formulário de Contato' ),
        'items_list'            => _x( 'Lista de Formulários de Contato', 'Formulário de Contato' ),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'contact_form' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 5,
        'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'tags' ),
        'menu_icon'           => 'dashicons-email-alt',
    );
    register_post_type( 'contact_form', $args );
}
add_action( 'init', 'register_contact_form_post_type' );

/**
 * Registers a custom REST API route to handle the submission of contact forms.
 *
 * This function registers a custom REST API route to handle the submission of contact forms.
 * The route accepts POST requests and triggers the 'contact_form_submit' callback function.
 *
 * @since 1.0.0
 */
add_action( 'rest_api_init', function () use ($mocked_token) {
    register_rest_route( 'trinitykit/v1', '/contact-form/submit', array(
        'methods'  => 'POST',
        'callback' => 'contact_form_submit',
        'permission_callback' => function () use ($mocked_token, $frontend_app_url) {
            // Verifica se o token enviado é igual ao token mockado
            $token = isset($_SERVER['HTTP_AUTHORIZATION']) ? trim(str_replace('Bearer', '', $_SERVER['HTTP_AUTHORIZATION'])) : '';
            return hash_equals($mocked_token, $token);
        }
    ));
});

/**
 * Callback function to handle the submission of contact forms.
 *
 * This function is called when the custom REST API route for submitting contact forms is accessed via a POST request.
 * It extracts parameters from the request, creates a new contact form post, and updates custom fields for the form.
 * It returns a JSON response indicating success or failure.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The REST API request object.
 * @return WP_REST_Response|WP_Error A response object indicating success or failure.
 */
function contact_form_submit($request) {
    $params = $request->get_params();

    // Extract parameters from the request
    $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
    $email = isset($params['email']) ? sanitize_email($params['email']) : '';
    $message = isset($params['message']) ? sanitize_textarea_field($params['message']) : '';

    if (empty($name)) {
        return new WP_Error('invalid_name_data', __('Name field cannot be empty'), array('status' => 400));
    }
    
    if (empty($email) || !is_email($email)) {
        return new WP_Error('invalid_email_data', __('Invalid email address'), array('status' => 400));
    }
    
    if (empty($message)) {
        return new WP_Error('invalid_message_data', __('Message field cannot be empty'), array('status' => 400));
    }

    // Define post title
    $post_title = $name . ' - ' . $email;

    // Create the post
    $post_id = wp_insert_post(array(
        'post_title'   => $post_title,
        'post_content' => $message,
        'post_status'  => 'publish',
        'post_type'    => 'contact_form',
    ));

    // Update custom fields for the contact form
    update_field( 'email', $email, $post_id );
    update_field( 'name', $name, $post_id );

    // Add "contato" tag to the post
    wp_set_post_tags( $post_id, 'contato', true );

    // Return success or failure response
    if ($post_id) {
        return new WP_REST_Response(array('success' => true), 200);
    } else {
        return new WP_Error('submission_failed', __('Failed to submit form'), array('status' => 500));
    }
}

function contact_form_columns( $columns ) {
    $columns['name'] = 'Nome';
    $columns['email'] = 'Email';
    $columns['message'] = 'Mensagem';
    unset( $columns['author'] );
    return $columns;
}
add_filter( 'manage_contact_form_posts_columns', 'contact_form_columns' );

function contact_form_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'name':
            echo get_field( 'name', $post_id );
            break;
        case 'email':
            echo get_field( 'email', $post_id );
            break;
        case 'message':
            echo get_post_field( 'post_content', $post_id );
            break;
        default:
            // Lidar com outras colunas, se necessário
            break;
    }
}
add_action( 'manage_contact_form_posts_custom_column', 'contact_form_column_content', 10, 2 );