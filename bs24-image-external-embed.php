<?php

/**
 * Plugin bootstrap file
 * 
 * @wordpress-plugin
 * Plugin Name:       BS24 Image External Embed
 * Description:       A WordPress plugin that embed image for external use
 * Version:           1.0.4
 * Author:            Md Hiron Mia
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bs24-image-external-embed
 */

//if this file is called directly abort
if( ! defined( 'WPINC' ) ){
    die;
}

//define path for BS24 Image External Embed plugin
define( 'BS24_IEE_DIR', plugin_dir_path(__FILE__) );
define( 'BS24_IEE_URL', plugin_dir_url(__FILE__) );

/**
 * Plugin Internalization
 */
function bs24_iee_textdomain_load(){
    load_plugin_textdomain( 'bs24-image-external-embed', false, BS24_IEE_DIR . 'languages' );
}
add_action( 'plugins_loaded', 'bs24_iee_textdomain_load' );

/**
 * Enqueue necessary scripts and styles 
 */
function bs24_iee_enqueue_scrips(){
    wp_enqueue_style( 'bs24-iee-style', BS24_IEE_URL . 'assets/css/main.css', array(), '1.4' );
    wp_enqueue_script( 'bs24-iee-script', BS24_IEE_URL . 'assets/js/main.js', array('jquery'), '1.4', true );
    wp_localize_script( 'bs24-iee-script', 'bs24Data', array(
        'siteUrl' => get_site_url()
    ) );
}

add_action( 'wp_enqueue_scripts', 'bs24_iee_enqueue_scrips' );

/**
 * Add embeded options on image right click
 */
function bs24_iee_add_embed_popup(){
    ?>
    <div id="bs24-embed-popup" class="bs24-embed-popup-wrap">
        <div class="bs24-embed-popup-container">
            <div class="bs24-embed-popup-header">
                <h2><?php _e( 'Ein Foto einbetten', 'bs24-image-external-embed' );?></h2>
            </div>
            <div class="bs24-embed-popup-content">
                <h3><?php _e( 'Kopieren Sie diesen Code, um dieses Foto auf Ihrer Site einzubetten:', 'bs24-image-external-embed' );?></h3>
                <div class="bs24-embed-image-box">
                    <h4><?php _e( 'Großes Bild (500 Pixel):','bs24-image-external-embed' );?></h4>
                    <textarea id="bs24-embed-large-image-input" class="bs24-embed-image-input" readonly></textarea>
                </div>
                <div class="bs24-embed-image-box">
                    <h4><?php _e( 'Kleines Bild (320 Pixel):','bs24-image-external-embed' );?></h4>
                    <textarea id="bs24-embed-small-image-input" class="bs24-embed-image-input" readonly></textarea>
                </div>
                <p class="bs24-embed-popup-desc"><?php _e( '' ); echo wp_kses_post( __('Dieses Bild wird Ihnen zur Verfügung gestellt und unterliegt den Nutzungsbedingungen von ', 'bs24-image-external-embed') . '<a href="https://www.badsanieren24.de">'. __( 'Badsanieren24', 'bs24-image-external-embed' ) .'</a>.' )?></p>
            </div>
            <div class="bs24-embed-popup-footer">
                <button class="bs24-embed-popup-close"><?php _e( 'Ok', 'bs24-image-external-embed' );?></button>
            </div>
        </div>
    </div>
    <?php
}

add_action( 'wp_footer', 'bs24_iee_add_embed_popup' );

/**
 * add credit field to image attachment edit screen
 */
function bs24_iee_add_credit_fields( $form_fields, $post ){
    $credit_text = !empty( get_post_meta( $post->ID, 'bs24_iee_image_credit', true ) ) ? get_post_meta( $post->ID, 'bs24_iee_image_credit', true ) : '';
    $credit_url = !empty( get_post_meta( $post->ID, 'bs24_iee_credit_url', true ) ) ? esc_url( get_post_meta( $post->ID, 'bs24_iee_credit_url', true ) ) : '';

    $form_fields['bs24_iee_image_credit'] = array(
        'label' => __( 'Image Credit' , 'bs24-image-external-embed' ),
        'type'  => 'text',
        'value' => $credit_text,
        'helps' =>  __( 'Enter Credit text for this image (used for embedding)' , 'bs24-image-external-embed' )
    );

    $form_fields['bs24_iee_credit_url'] = array(
        'label' => __( 'Image Credit Url' , 'bs24-image-external-embed' ),
        'type'  => 'text',
        'value' => $credit_url,
        'helps' =>  __( 'Enter credit url for this image (used for embedding)' , 'bs24-image-external-embed' )
    );

    return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'bs24_iee_add_credit_fields', 10, 2 );

/**
 * Save image embed credit meta fields
 */
function bs24_iee_save_credit_field_data( $post, $attachment ){
    if( isset( $attachment['bs24_iee_image_credit'] ) ){
        update_post_meta( $post['ID'], 'bs24_iee_image_credit', sanitize_text_field( $attachment['bs24_iee_image_credit'] ) );
    }

    if( isset( $attachment['bs24_iee_credit_url'] ) ){
        update_post_meta( $post['ID'], 'bs24_iee_credit_url', esc_url( sanitize_text_field( $attachment['bs24_iee_credit_url'] ) ) );
    }

    return $post;
}
add_filter( 'attachment_fields_to_save', 'bs24_iee_save_credit_field_data', 10, 3 );

/**
 * Register custom REST API endpoint for getting image credit data by javascript
 */
function bs24_iee_register_image_url_endpoint(){
    register_rest_route( 'bs24/v1', '/image-meta', array(
        'methods'  => 'GET',
        'callback' => 'bs24_iee_get_image_url',
        'permission_callback' => '__return_true'
    ) );
}
add_action( 'rest_api_init', 'bs24_iee_register_image_url_endpoint' );

/**
 * Callback function for rest api data
 */
function bs24_iee_get_image_url( $data ){
    $attachment_url = get_site_url() . $data->get_param('url');
    $attachment_id = bs24_get_attachment_id_by_url( $attachment_url );

    $credit_text   = !empty( get_post_meta( $attachment_id, 'bs24_iee_image_credit', true ) ) ? get_post_meta( $attachment_id, 'bs24_iee_image_credit', true ) : '';
    $credit_url    = !empty( get_post_meta( $attachment_id, 'bs24_iee_credit_url', true ) ) ?  esc_url( get_post_meta( $attachment_id, 'bs24_iee_credit_url', true ) ) : '';

    return new WP_REST_Response( array(
        'image_url'   => $attachment_url,
        'credit_text' => $credit_text,
        'credit_url'  => $credit_url,
    ) );
}

/**
 * Get attachment id from URL
 */
function bs24_get_attachment_id_by_url( $url ){
    if( empty( $url ) ){
        return false;
    }

    //sanitize url
    $url = esc_url_raw( $url );

    $filtered_url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif|webp)$)/i', '', $url);

    // using WP built in function to get attachment id from url
    $attachment_id = attachment_url_to_postid( $filtered_url );

    return $attachment_id ? intval( $attachment_id ) : false;
}
