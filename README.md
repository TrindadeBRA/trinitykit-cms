# README - Trinity Kit - WordPress e Next.js

Bem-vindo ao tema Trinity Kit CMS para WordPress! Este README fornece instruções básicas para configurar e personalizar o tema.

## Passo 01 - Instalando e ativando plugins necessários

1. Instale e ative o plugin [Advanced Custom Fields (ACF)](https://br.wordpress.org/plugins/advanced-custom-fields/). (Versão FREE)
2. Instale e ative o plugin [Yoast SEO](https://br.wordpress.org/plugins/wordpress-seo/). (Versão FREE)

## Passo 02 - Instalando e ativando o nosso tema

1. Faça o download do tema Trinity Kit CMS.
2. Acesse o painel de administração do WordPress.
3. Navegue até "Aparência" > "Temas".
4. Clique em "Adicionar Novo" e depois em "Enviar Tema".
5. Selecione o arquivo zip do tema que você baixou e clique em "Instalar Agora".
6. Após a instalação, ative o tema.

Ao ativar o tema irá ser criado as páginas iniciais e importados os ACFs necessários.

## Passo 03 - Configurando o WordPress

1. Crie o menu da aplicação e insira o menu criado na posição do menu principal. (/wp-admin/nav-menus.php?action=locations).
2. Adicionar os campos em "Identidade do site" e "TrinityKit Settings". (/wp-admin/customize.php?return=%2Fwp-admin%2Ftheme-editor.php)

## Passo 04 - Configurando os secrets do repositorio do frontend

1. Para o CI/CD do projeto é necessário adicionar 4 variaveis de ambiente, sendo elas: ```FTP_HOST```, ```FTP_LOGIN```, ```FTP_PASSWORD``` e ```WORDPRESS_URL```. (https://github.com/username/repo-namesettings/secrets/actions)
2. No frontend, no arquivo ```.github/workflows/master.yml```, no parametro ```server-dir``` é necessário adicionar o path da pasta em que você deseja fazer upload do projeto. 

## Passo 05 - Configurando os campos ACF nas páginas criadas

1. Entre em todas as páginas criadas, e preencha todos os ACFs. (/wp-admin/edit.php?post_type=page)



## Contribuição

Este tema é de código aberto e aceita contribuições. Se você encontrar bugs ou tiver sugestões de melhorias, por favor, relate-os em nosso repositório no GitHub.

---

Esperamos que você aproveite o uso do tema Trinity Kit CMS para WordPress! Se você tiver alguma dúvida ou precisar de suporte adicional, não hesite em nos contatar.
