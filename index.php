<?php
get_header(); // Adiciona o cabeçalho do tema 
?>

<main class="container mx-auto h-full">
    <div class="h-[100vh] w-full flex flex-col items-center justify-center">
        <!-- Logotipo -->
        <div class="my-4">
            <a href="<?php echo esc_url(admin_url('/')); ?>">
                <img src="<?php echo get_theme_file_uri('assets/images/logo.webp'); ?>" alt="Logotipo" class="mx-auto">
            </a>
        </div>
    
        <!-- Botão para o painel de administração -->
        <a href="<?php echo esc_url(admin_url()); ?>" class="bg-black text-black px-4 py-2 rounded hover:bg-black-700 text-white font-bold">Painel Administrativo</a>
    </div>
</main>

<?php
get_footer(); // Adiciona o rodapé do tema
wp_footer(); // Adiciona scripts do WordPress no rodapé 
?>
