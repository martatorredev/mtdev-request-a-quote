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

// Cargar el textdomain para traducciones
add_action( 'plugins_loaded', 'mtdev_request_a_quote_load_textdomain' );
function mtdev_request_a_quote_load_textdomain() {
    load_plugin_textdomain( 'mtdev-request-a-quote', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

// Lista de IDs de productos que requieren consulta previa
function mtdev_request_a_quote_get_products() {
    return [
        123, // Reemplazar con el ID del producto "Tratamiento adelgazante reafirmante 3 máquinas"
        124, // Reemplazar con el ID del producto "Tratamiento de adelgazamiento y reafirmante 2 máquinas"
    ];
}

// Ocultar el precio de los productos específicos
add_filter( 'woocommerce_get_price_html', 'mtdev_request_a_quote_hide_price', 10, 2 );
function mtdev_request_a_quote_hide_price( $price, $product ) {
    if ( in_array( $product->get_id(), mtdev_request_a_quote_get_products() ) ) {
        return '<p class="mtdev-quote-message">' . __( 'Price available upon request', 'mtdev-request-a-quote' ) . '</p>';
    }
    return $price;
}

// Desactivar el botón "Añadir al carrito" para productos específicos
add_filter( 'woocommerce_is_purchasable', 'mtdev_request_a_quote_make_unpurchasable', 10, 2 );
function mtdev_request_a_quote_make_unpurchasable( $purchasable, $product ) {
    if ( in_array( $product->get_id(), mtdev_request_a_quote_get_products() ) ) {
        return false;
    }
    return $purchasable;
}

// Añadir estilos opcionales para el mensaje personalizado
add_action( 'wp_enqueue_scripts', 'mtdev_request_a_quote_enqueue_styles' );
function mtdev_request_a_quote_enqueue_styles() {
    wp_enqueue_style( 'mtdev-request-a-quote-style', plugins_url( 'css/style.css', __FILE__ ) );
}
