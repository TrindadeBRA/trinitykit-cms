jQuery(document).ready(function($) {

    $('input[name="deploy_button"]').on('click', function(e) {
        e.preventDefault(); 
        $(this).prop('disabled', true);
        console.log("disabled!");

        
        var github_user = $('#github_user').text();
        var github_repo = $('#github_repo').text();
        var github_token = $('#github_token').text();

        console.log("github_user", github_user);
        console.log("github_repo", github_repo);
        console.log("github_token", github_token);

        $.ajax({
            url: 'https://api.github.com/repos/' + github_user + '/' + github_repo + '/actions/runs?status=completed&per_page=1',
            success: function(response) {

                const lastRunId = response.workflow_runs[0].id;
                console.log(">>>", lastRunId);

                $.ajax({
                    url: 'https://api.github.com/repos/' + github_user + '/' + github_repo + '/actions/runs/' + lastRunId + '/rerun',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + github_token
                    },
                    success: function(response) {
                        console.log("Reexecução iniciada com sucesso!!"); 
                        $('#response_area').text('Reexecução iniciada com sucesso! Aguarde alguns minutos.');
                    },
                    error: function(xhr, status, error, response) {
                        console.error(status + ': ' + error);
                        console.log(error);
                        $('#response_area').text(response.message);

                    }
                });
            },
            error: function(xhr, status, error) {
                console.error(status + ': ' + error); 
            }
        });
    });
});
