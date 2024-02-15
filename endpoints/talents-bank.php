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

add_action( 'rest_api_init', function () {
    register_rest_route( 'trinitykit/v1/talents-bank', '/add-talent/', array(
        'methods' => 'POST',
        'callback' => 'talent_bank_add',
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
    $name = sanitize_text_field($params['name']);
    $email = sanitize_email($params['email']);
    $phone = sanitize_text_field($params['phone']);

    // Create the post
    $post_id = wp_insert_post(array(
        'post_title'   => $name,
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'talent_bank',
    ));

    // Handle the upload of the presentation document and set it as the featured image
    if (!empty($_FILES['presentation_document'])) {
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
