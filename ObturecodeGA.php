<?php
/*
Plugin Name: Google Analytics y la ley de Cookies
Plugin URI: http://obturecode.com
Description: Obturecode te intenta ayudar con la ley española de cookies, integra analitycs facilmente en tu sitio
Author: Obture Code
Version: 1.2
Author URI: http://obturecode.com

*/  
define('OBTURECODE_GA_NAME','Google Analytics y la ley de Cookies');
define('OBTURECODE_GA_NAME_PATH','google-analytics-y-la-ley-de-cookies');
function widget_obtcookies_control(){
    require dirname(__FILE__).'/obturecode_ga_admin.php';
}



function widget_obtcookies_menu() {
  add_options_page( 'Ajustes del plugin "'.OBTURECODE_GA_NAME.'"', OBTURECODE_GA_NAME, 'manage_options', "Widget_Obturecode_GA", 'widget_obtcookies_control' );

      register_setting( 'obt_cookies', 'obtga_idanalitycs' );
      register_setting( 'obt_cookies', 'obtga_texto' );
      register_setting( 'obt_cookies', 'obtga_titulo' );
      if(get_option('obtga_idanalitycs')==null || get_option('obtga_idanalitycs')==""){
        showMessage('El plugin: "'.OBTURECODE_GA_NAME.'" necesita ser configurado, hasta que no añadas tu ID de Analitycs no va a funcionar. <a href="options-general.php?page=Widget_Obturecode_GA">Configúralo aquí</a>',true);
      }
}
  
// This registers the (optional!) widget control form.
    wp_register_widget_control('Obturecode_GA','ObturecodeGA', 'widget_obtcookies_control');

    add_action( 'admin_menu', 'widget_obtcookies_menu' );

function obtcookies_add_script(){
    wp_enqueue_script( 'obtga-script', WP_PLUGIN_URL . '/'.OBTURECODE_GA_NAME_PATH.'/main.js', array(), null, true );
    $params = array(
  		'idGA' => get_option('obtga_idanalitycs'),
  		'texto' => get_option('obtga_texto','Utilizamos cookies propias y de terceros para mejorar nuestros servicios. Si continúa navegando, consideramos que acepta su uso.'),
      'titulo' => get_option('obtga_titulo','Uso de Cookies'),
      'url' => esc_url( get_permalink( get_option('obtga_id_pagina') )),
      'titulo_pagina' => get_option('obtga_titulo_pagina')
	);
    wp_enqueue_style('obtga-style',WP_PLUGIN_URL . '/'.OBTURECODE_GA_NAME_PATH.'/obturecode_ga.css');
    wp_localize_script( 'obtga-script', 'ObtGAParams', $params );
}

add_action( 'wp_enqueue_scripts', 'obtcookies_add_script' );

function obtcookies_install(){
    global $wpdb;

    $titulo = 'Política de cookies';
    $slug = 'politica-de-cookies';

    // the menu entry...
    delete_option('obtga_titulo_pagina');
    add_option('obtga_titulo_pagina', $titulo, '', 'yes');
    // the slug...
    delete_option('obtga_slug_pagina');
    add_option('obtga_slug_pagina', $slug, '', 'yes');
    // the id...
    delete_option('obtga_id_pagina');
    add_option('obtga_id_pagina', '0', '', 'yes');

    $the_page = get_page_by_title( $titulo );

    if ( ! $the_page ) {

        // Se crea el objeto del post
        $_p = array();
        $_p['post_title'] = $titulo;
        //$_p['post_content'] = file_get_contents(WP_PLUGIN_URL . '/obturecode_ga/politica.html');
        $_p['post_content'] = file_get_contents( plugins_url( 'politica.html', __FILE__ ) );
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1);

        // Se guarda en la base de datos
        $id_pagina = wp_insert_post( $_p );

    }
    else {
        // significa que el plugin ya estaba activado antes

        $id_pagina = $the_page->ID;

        //Por si no esta publicada, la publicamos
        $the_page->post_status = 'publish';
        $id_pagina = wp_update_post( $the_page );

    }

    delete_option( 'obtga_id_pagina' );
    add_option( 'obtga_id_pagina', $id_pagina );
}

function obtcookies_remove() {

    global $wpdb;

    $titulo = get_option( 'obtga_titulo_pagina' );
    $slug = get_option( 'obtga_slug_pagina' );

    //  obtenemos el id de la página para borrarlo
    $id_pagina = get_option( 'obtga_id_pagina' );
    if( $id_pagina ) {

        wp_delete_post( $id_pagina ); // this will trash, not delete

    }

    delete_option('obtga_titulo_pagina');
    delete_option('obtga_slug_pagina');
    delete_option('obtga_id_pagina');
    delete_option( 'obtga_idanalitycs' );
    delete_option( 'obtga_texto' );
    delete_option( 'obtga_titulo' );

}
function showMessage($message, $errormsg = false)
{
    if ($errormsg) {
        echo '<div id="message" class="error">';
    }
    else {
        echo '<div id="message" class="updated fade">';
    }

    echo "<p><strong>$message</strong></p></div>";
}

register_activation_hook(__FILE__,'obtcookies_install'); 

register_deactivation_hook( __FILE__, 'obtcookies_remove' );