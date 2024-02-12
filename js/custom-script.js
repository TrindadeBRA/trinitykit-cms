jQuery(document).ready(function($) {
    // Ao clicar no botão com o nome 'deploy_button'
    $('input[name="deploy_button"]').on('click', function(e) {
        e.preventDefault(); // Previne o comportamento padrão do formulário

        // Faz uma requisição GET
        $.get({
            url: 'URL_DA_API', // Substitua pela URL de sua API
            success: function(response) {
                console.log(response); // Loga a resposta da requisição
            },
            error: function(xhr, status, error) {
                console.error(status + ': ' + error); // Loga erros caso ocorram
            }
        });
    });
});
