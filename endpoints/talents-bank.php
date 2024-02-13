<?php
// Adicione este código ao seu arquivo functions.php do tema ou a um plugin
function registrar_tipo_post_talent_bank() {
    $labels = array(
        'name'                  => _x( 'Banco de talentos', 'Nome do tipo de post' ),
        'singular_name'         => _x( 'Talento', 'Nome singular do tipo de post' ),
        'menu_name'             => _x( 'Banco de talentos', 'Nome do menu' ),
        'add_new'               => _x( 'Adicionar Novo', 'Novo item' ),
        'add_new_item'          => __( 'Adicionar Novo Talento' ),
        'edit_item'             => __( 'Editar Talento' ),
        'view_item'             => __( 'Ver Talento' ),
        'all_items'             => __( 'Todos os Talentos' ),
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
        'menu_position'       => 5,
        'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
    );
 
    register_post_type( 'talent_bank', $args );
}
add_action( 'init', 'registrar_tipo_post_talent_bank' );

add_action( 'rest_api_init', function () {
    register_rest_route( 'talent_bank/v1', '/add_talent/', array(
        'methods' => 'POST',
        'callback' => 'criar_talento',
        'permission_callback' => function () {
            return current_user_can( 'publish_posts' );
        }
    ));
});

function criar_talento( $request ) {
    $params = $request->get_params();
    
    $postarr = array(
        'post_title'    => sanitize_text_field( $params['nome_completo'] ),
        'post_status'   => 'publish',
        'post_type'     => 'talent_bank'
    );

    $post_id = wp_insert_post( $postarr );

    // Salvar os campos ACF
    update_field( 'nome_completo', sanitize_text_field( $params['nome_completo'] ), $post_id );
    update_field( 'email', sanitize_email( $params['email'] ), $post_id );
    update_field( 'telefone', sanitize_text_field( $params['telefone'] ), $post_id );

    // Salvar arquivo de CV
    if (!empty($_FILES['cv_file']['name'])) {
        $file = wp_upload_bits($_FILES['cv_file']['name'], null, file_get_contents($_FILES['cv_file']['tmp_name']));
        if ($file['error'] == '') {
            update_field('cv_file', $file['url'], $post_id);
        }
    }

    return new WP_REST_Response( array( 'message' => 'Talento criado com sucesso' ), 200 );
}
