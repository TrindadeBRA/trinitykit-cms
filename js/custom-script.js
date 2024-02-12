jQuery(document).ready(function($) {
    // Ao clicar no botão com o nome 'deploy_button'
    $('input[name="deploy_button"]').on('click', function(e) {
        e.preventDefault(); // Previne o comportamento padrão do formulário

        console.log("clicaque")

        // Faz uma requisição GET
        $.ajax({
            url: 'https://api.github.com/repos/TrindadeBRA/trinitykit/actions/runs?status=completed&per_page=1', // Substitua pela URL de sua API
            success: function(response) {
                // const lastRunId = response.workflow_runs[0].workflow_id;
                const lastRunId = response.workflow_runs[0].id;
                console.log(">>>", lastRunId); // Loga o ID retornado pela primeira requisição

                // Faz uma requisição POST com o ID obtido
                $.ajax({
                    url: 'https://api.github.com/repos/TrindadeBRA/trinitykit/actions/runs/' + lastRunId + '/rerun',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer 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'
                    },
                    success: function(response) {
                        console.log("Reexecução iniciada com sucesso!"); // Loga a confirmação da reexecução
                    },
                    error: function(xhr, status, error) {
                        console.error(status + ': ' + error); // Loga erros caso ocorram
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error(status + ': ' + error); // Loga erros caso ocorram
            }
        });
    });
});
