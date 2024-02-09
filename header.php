<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://cdn.tailwindcss.com"></script>
    <title><?php bloginfo('name'); ?> - <?php bloginfo('description'); ?></title>
    <?php wp_head(); ?>
</head>

<body <?php body_class("bg-gray-700"); ?>>

    <!-- Navbar -->
    <!-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container mx-auto">
            <a class="navbar-brand flex items-center" href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo get_theme_file_uri('assets/images/logo.webp'); ?>" alt="Logotipo" class="mx-auto">
            </a>
        </div>
    </nav> -->