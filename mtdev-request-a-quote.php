<?php
/*
Plugin Name: MTDev Request a Quote for WooCommerce
Plugin URI: https://github.com/martatorredev/mtdev-request-a-quote
Description: A custom WooCommerce plugin by Marta Torre to hide prices for specific products and show a custom message.
Version: 1.0
Author: Marta Torre
Author URI: https://martatorre.dev/
License: GPLv2 or later
Text Domain: mtdev-request-a-quote
Domain Path: /languages
*/

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar textdomain
add_action( 'plugins_loaded', 'mtdev_request_a_quote_load_textdomain' );
function mtdev_request_a_quote_load_textdomain() {
    load_plugin_textdomain( 'mtdev-request-a-quote', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

// 1. Agregar un metabox al editor de productos
add_action( 'add_meta_boxes', 'mtdev_add_custom_fields_metabox' );
function mtdev_add_custom_fields_metabox() {
    add_meta_box(
        'mtdev_request_a_quote',
        __( 'Request a Quote Settings', 'mtdev-request-a-quote' ),
        'mtdev_render_custom_fields_metabox',
        'product',
        'side'
    );
}

function mtdev_render_custom_fields_metabox( $post ) {
    // Obtener valores actuales
    $hide_price = get_post_meta( $post->ID, '_mtdev_hide_price', true );
    $button_text = get_post_meta( $post->ID, '_mtdev_button_text', true );

    ?>
    <label for="mtdev_hide_price">
        <input type="checkbox" id="mtdev_hide_price" name="mtdev_hide_price" value="yes" <?php checked( $hide_price, 'yes' ); ?> />
        <?php _e( 'Hide price and enable custom button', 'mtdev-request-a-quote' ); ?>
    </label>
    <br><br>
    <label for="mtdev_button_text">
        <?php _e( 'Custom button text:', 'mtdev-request-a-quote' ); ?>
    </label>
    <input type="text" id="mtdev_button_text" name="mtdev_button_text" value="<?php echo esc_attr( $button_text ); ?>" placeholder="<?php _e( 'Request a Quote', 'mtdev-request-a-quote' ); ?>" />
    <?php
}

// Guardar datos del metabox
add_action( 'save_post', 'mtdev_save_custom_fields' );
function mtdev_save_custom_fields( $post_id ) {
    if ( isset( $_POST['mtdev_hide_price'] ) ) {
        update_post_meta( $post_id, '_mtdev_hide_price', 'yes' );
    } else {
        delete_post_meta( $post_id, '_mtdev_hide_price' );
    }

    if ( isset( $_POST['mtdev_button_text'] ) ) {
        update_post_meta( $post_id, '_mtdev_button_text', sanitize_text_field( $_POST['mtdev_button_text'] ) );
    }
}

// 2. Ocultar precio y botón "Añadir al carrito" para productos configurados
add_filter( 'woocommerce_get_price_html', 'mtdev_hide_price_html', 10, 2 );
function mtdev_hide_price_html( $price, $product ) {
    if ( get_post_meta( $product->get_id(), '_mtdev_hide_price', true ) === 'yes' ) {
        return '<p class="mtdev-quote-message">' . __( 'Price available upon request', 'mtdev-request-a-quote' ) . '</p>';
    }
    return $price;
}

add_filter( 'woocommerce_is_purchasable', 'mtdev_make_product_unpurchasable', 10, 2 );
function mtdev_make_product_unpurchasable( $purchasable, $product ) {
    if ( get_post_meta( $product->get_id(), '_mtdev_hide_price', true ) === 'yes' ) {
        return false;
    }
    return $purchasable;
}

// 3. Mostrar el botón personalizado
add_action( 'woocommerce_single_product_summary', 'mtdev_add_custom_button', 30 );
function mtdev_add_custom_button() {
    global $product;

    if ( get_post_meta( $product->get_id(), '_mtdev_hide_price', true ) === 'yes' ) {
        $button_text = get_post_meta( $product->get_id(), '_mtdev_button_text', true ) ?: __( 'Request a Quote', 'mtdev-request-a-quote' );
        echo '<a href="' . esc_url( get_permalink( $product->get_id() ) ) . '" class="button alt">' . esc_html( $button_text ) . '</a>';
    }
}