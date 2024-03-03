<?php

function generate_jwt_token() {
    $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : 'chave_padrao_secreta';
    
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 15); //15 Min

    $payload = array(
        'iat' => $issued_at,
        'exp' => $expiration_time
    );

    // Gera o token JWT
    $token = JWT::encode($payload, $secret_key);

    return $token;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'trinitykit/v1', '/generate-token/', array(
        'methods'  => 'GET',
        'callback' => 'generate_jwt_token',
    ));
});
