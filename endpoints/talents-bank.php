<?php
// Função para registrar o tipo de post "Banco de talentos"
function register_talent_bank() {
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

    // Registrando o tipo de post "Banco de talentos"
    register_post_type( 'talent_bank', $args );
}
add_action( 'init', 'register_talent_bank' );




// Adicionando uma rota de API REST para adicionar talentos
add_action( 'rest_api_init', function () {
    register_rest_route( 'trinitykit/v1/talents-bank', '/add-talent/', array(
        'methods' => 'POST',
        'callback' => 'add_talent', // Chama a função add_talent quando esta rota é acessada
    ));
});

function add_talent( $request ) {
    // Obtendo os parâmetros da requisição
    $params = $request->get_params(); 

    // Criando um novo post
    $post_data = array(
        'post_title'    => $params['full_name'], // Usando o nome completo como título do post
        'post_type'     => 'talent_bank', // Tipo de post
        'post_status'   => 'publish', // Publicar o post imediatamente
    );

    // Inserindo o post no banco de dados
    $post_id = wp_insert_post( $post_data );

    // Verificando se o post foi criado com sucesso
    if ( !is_wp_error( $post_id ) ) {
        // Verificando se o arquivo de documento de apresentação foi enviado
        if ( isset( $_FILES['presentation_document'] ) && !empty( $_FILES['presentation_document'] ) ) {
            $file = $_FILES['presentation_document'];

            // Realizando o upload do arquivo e associando-o ao post
            $attachment_id = media_handle_upload( 'presentation_document', $post_id );

            // Verificando se o upload foi bem-sucedido
            if ( is_wp_error( $attachment_id ) ) {
                return new WP_Error( 'upload_error', $attachment_id->get_error_message() );
            }

            // Atualizando o post com o ID do arquivo anexado
            update_post_meta( $post_id, 'presentation_document', $attachment_id );
        }
    } else {
        return new WP_Error( 'post_creation_error', 'Erro ao criar o post.' );
    }

    // Retornando a ID do post recém-criado
    return $post_id;
}
