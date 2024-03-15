<?php

/**
 * Registers the custom post type "Talent Bank".
 *
 * This function registers a custom post type called "Talent Bank" with custom labels and arguments.
 *
 * @since 1.0.0
 */

function register_talent_bank() {
    $labels = array(
        'name'                  => _x( 'Banco de talentos', 'Nome do tipo de post' ),
        'singular_name'         => _x( 'Talento', 'Nome singular do tipo de post' ),
        'menu_name'             => _x( 'Talentos', 'Nome do menu' ),
        'add_new'               => _x( 'Adicionar Novo', 'Novo item' ),
        'add_new_item'          => __( 'Adicionar Novo Talento' ),
        'edit_item'             => __( 'Editar Talento' ),
        'view_item'             => __( 'Ver Talento' ),
        'all_items'             => __( 'Banco de Talentos' ),
        'search_items'          => __( 'Procurar Talentos' ),
        'not_found'             => __( 'Nenhum Talento encontrado' ),
        'not_found_in_trash'    => __( 'Nenhum Talento encontrado na lixeira' ),
        'featured_image'        => _x( 'Imagem de Destaque', 'Talento' ),
        'set_featured_image'    => _x( 'Definir imagem de destaque', 'Talento' ),
        'remove_featured_image' => _x( 'Remover imagem de destaque', 'Talento' ),
        'use_featured_image'    => _x( 'Usar como imagem de destaque', 'Talento' ),
        'archives'              => _x( 'Arquivos de Talentos', 'Talento' ),
        'insert_into_item'      => _x( 'Inserir em Talento', 'Talento' ),
        'uploaded_to_this_item' => _x( 'Enviado para este Talento', 'Talento' ),
        'filter_items_list'     => _x( 'Filtrar lista de Talentos', 'Talento' ),
        'items_list_navigation' => _x( 'Navegação lista de Talentos', 'Talento' ),
        'items_list'            => _x( 'Lista de Talentos', 'Talento' ),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'talent_bank' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 3,
        'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
        'menu_icon'           => 'dashicons-star-filled',
    );
    register_post_type( 'talent_bank', $args );
}
add_action( 'init', 'register_talent_bank' );

function custom_talent_bank_columns( $columns ) {
    $columns['full_name'] = 'Nome';
    $columns['email'] = 'Email';
    $columns['cellphone'] = 'Celular';
    $columns['presentation_document'] = 'Documento anexado';
    unset( $columns['author'] );
    return $columns;
}
add_filter( 'manage_talent_bank_posts_columns', 'custom_talent_bank_columns' );

function custom_talent_bank_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'full_name':
            echo get_field( 'full_name', $post_id );
            break;
        case 'email':
            echo get_field( 'email', $post_id );
            break;
        case 'cellphone':
            echo get_field( 'cellphone', $post_id );
            break;
        case 'presentation_document':
            $attachment_id = get_field( 'presentation_document', $post_id );
            $attachment_url = wp_get_attachment_url( $attachment_id );
            echo $attachment_url ? '<a href="' . $attachment_url . '" target="_blank">' . $attachment_url . '</a>' : 'No attachment';
            break;
        default:
            // Lidar com outras colunas, se necessário
            break;
    }
}
add_action( 'manage_talent_bank_posts_custom_column', 'custom_talent_bank_column_content', 10, 2 );

/**
 * Registers a custom REST API route to add a talent to the Talent Bank.
 *
 * This function registers a custom REST API route to handle the addition of a talent to the Talent Bank.
 * The route accepts POST requests and triggers the 'talent_bank_add' callback function.
 *
 * @since 1.0.0
 */

 add_action( 'rest_api_init', function () use ($mocked_token) {

    $mocked_token = 'ohuhasgdkahsdkjasnbdkjbasdkjbdjb';
    $frontend_app_url = get_theme_mod('frontend_app_url');

    register_rest_route( 'trinitykit/v1/talents-bank', '/add-talent/', array(
        'methods'  => 'POST',
        'callback' => 'talent_bank_add',
        'permission_callback' => function () use ($mocked_token, $frontend_app_url) {
            // Verifica se o token enviado é igual ao token mockado
            $token = isset($_SERVER['HTTP_AUTHORIZATION']) ? trim(str_replace('Bearer', '', $_SERVER['HTTP_AUTHORIZATION'])) : '';

            // Verifique se o domínio da origem da solicitação é permitido
            if (isset($_SERVER['HTTP_ORIGIN']) && !empty($frontend_app_url)) {
                // Permita apenas solicitações do domínio da aplicação frontend
                if ($_SERVER['HTTP_ORIGIN'] === $frontend_app_url) {
                    // Permita a solicitação
                    header('Access-Control-Allow-Origin: ' . $frontend_app_url);
                    header('Access-Control-Allow-Methods: POST');
                    header('Access-Control-Allow-Headers: Content-Type');
                } else {
                    // Se o domínio da origem não estiver na lista permitida, negue a solicitação
                    return false;
                }
            }

            return hash_equals($mocked_token, $token);
        }
    ));
});

/**
 * Callback function to add a talent to the Talent Bank.
 *
 * This function is called when the custom REST API route for adding a talent is accessed via a POST request.
 * It extracts parameters from the request, creates a new talent post, handles file uploads for the presentation document,
 * and updates custom fields for the talent. It returns a JSON response indicating success or failure.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request The REST API request object.
 * @return WP_REST_Response|WP_Error A response object indicating success or failure.
 */

function talent_bank_add($request) {
    $params = $request->get_params();

    // Extract parameters from the request
    $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
    $email = isset($params['email']) ? sanitize_email($params['email']) : '';
    $phone = isset($params['phone']) ? sanitize_text_field($params['phone']) : '';

    if (empty($name)) {
        return new WP_Error('invalid_name_data', __('Name field cannot be empty'), array('status' => 400));
    }
    
    if (empty($email)) {
        return new WP_Error('invalid_email_data', __('Email field cannot be empty'), array('status' => 400));
    }
    
    if (empty($phone)) {
        return new WP_Error('invalid_phone_data', __('Phone field cannot be empty'), array('status' => 400));
    }

    // Create the post
    $post_id = wp_insert_post(array(
        'post_title'   => $name,
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'talent_bank',
    ));

    // Handle the upload of the presentation document and set it as the featured image
    if (!empty($_FILES['presentation_document'])) {

        // Verifique se o arquivo foi enviado com sucesso
        if ($_FILES['presentation_document']['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', __('Failed to upload file'), array('status' => 500));
        }

        // Verifique o tipo de arquivo permitido
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        $file_type = wp_check_filetype($_FILES['presentation_document']['name']);
        if (!in_array($file_type['ext'], $allowed_types)) {
            return new WP_Error('invalid_file_type', __('Invalid file type'), array('status' => 400));
        }

        // Verifique o tamanho máximo do arquivo (em bytes)
        $max_file_size = 5 * 1024 * 1024; // 5 MB
        if ($_FILES['presentation_document']['size'] > $max_file_size) {
            return new WP_Error('file_too_large', __('File size exceeds maximum limit'), array('status' => 400));
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('presentation_document', $post_id);

        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    // Update custom fields for the talent
    update_field( 'full_name', $name, $post_id );
    update_field( 'email', $email, $post_id );
    update_field( 'cellphone', $phone, $post_id );
    update_field( 'presentation_document', $attachment_id, $post_id );

    // Return success or failure response
    if ($post_id) {
        return new WP_REST_Response(array('success' => true), 200);
    } else {
        return new WP_Error('submission_failed', __('Failed to submit form'), array('status' => 500));
    }
}
