<?php
// Adicione este código ao seu arquivo functions.php do tema ou a um plugin

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

// Função para adicionar um talento
// function add_talent( $request ) {

//     $params = $request->get_params(); // Obtendo os parâmetros da requisição

//     // Criando um array com os dados do post
//     $postarr = array(
//         'post_title'    => sanitize_text_field( $params['nome_completo'] ), // Sanitizando e definindo o título do post
//         'post_status'   => 'publish',
//         'post_type'     => 'talent_bank'
//     );

//     // Inserindo o post e obtendo o ID
//     $post_id = wp_insert_post( $postarr );

//     // Salvando os campos personalizados usando ACF
//     update_field( 'full_name', sanitize_text_field( $params['nome_completo'] ), $post_id );
//     update_field( 'email', sanitize_email( $params['email'] ), $post_id );
//     update_field( 'cellphone', sanitize_text_field( $params['telefone'] ), $post_id );

//     // Salvando o arquivo de apresentação (CV)
//     if (!empty($_FILES['presentation_document']['name'])) {
//         $file = wp_upload_bits($_FILES['presentation_document']['name'], null, file_get_contents($_FILES['presentation_document']['tmp_name']));
//         if ($file['error'] == '') {
//             update_field('presentation_document', $file['url'], $post_id);
//         }
//     }

//     // Retornando uma resposta da API REST
//     return new WP_REST_Response( array( 'message' => 'Talento criado com sucesso' ), 200 );
// }


// Função para adicionar um talento
function add_talent( $request ) {
    $params = $request->get_params(); // Obtendo os parâmetros da requisição

    // Criando um array com os dados do post
    $postarr = array(
        'post_title'    => sanitize_text_field( $params['nome_completo'] ), // Sanitizando e definindo o título do post
        'post_status'   => 'publish',
        'post_type'     => 'talent_bank'
    );

    // Inserindo o post e obtendo o ID
    $post_id = wp_insert_post( $postarr );

    // Salvando os campos personalizados usando ACF
    update_field( 'full_name', sanitize_text_field( $params['nome_completo'] ), $post_id );
    update_field( 'email', sanitize_email( $params['email'] ), $post_id );
    update_field( 'cellphone', sanitize_text_field( $params['telefone'] ), $post_id );

    // Salvando o arquivo de apresentação (CV)
    if (!empty($_FILES['presentation_document']['name'])) {
        $file_name = $_FILES['presentation_document']['name'];
        $file_tmp_name = $_FILES['presentation_document']['tmp_name'];
        $file_type = $_FILES['presentation_document']['type'];
        $file_size = $_FILES['presentation_document']['size'];
        $file_error = $_FILES['presentation_document']['error'];

        if ($file_error === 0) {
            $upload_dir = wp_upload_dir();
            $file_dest_path = $upload_dir['path'] . '/' . $file_name;

            if (move_uploaded_file($file_tmp_name, $file_dest_path)) {
                $file_url = $upload_dir['url'] . '/' . $file_name;
                update_field('presentation_document', $file_url, $post_id);
            }
        }
    }

    // Retornando uma resposta da API REST
    return new WP_REST_Response( array( 'message' => 'Talento criado com sucesso' ), 200 );
}
