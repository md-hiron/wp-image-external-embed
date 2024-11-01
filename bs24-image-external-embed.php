<?php

/**
 * Plugin bootstrap file
 * 
 * @wordpress-plugin
 * Plugin Name:       BS24 Image External Embed
 * Description:       A WordPress plugin that embed image for external use
 * Version:           1.0.0
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
 * Enqueue necessary scripts and styles 
 */
function bs24_iee_enqueue_scrips(){
    wp_enqueue_style( 'bs24-iee-style', BS24_IEE_URL . 'assets/css/main.css', array(), time() );
    wp_enqueue_script( 'bs24-iee-script', BS24_IEE_URL . 'assets/js/main.js', array('jquery'), time(), true );
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
                <h2>Embed a Photo</h2>
            </div>
            <div class="bs24-embed-popup-content">
                <h3>Copy this code to embed this photo on your site:</h3>
                <div class="bs24-embed-image-box">
                    <h4>Large Image (500 pixels):</h4>
                    <textarea id="bs24-embed-large-image-input" class="bs24-embed-image-input" readonly></textarea>
                </div>
                <div class="bs24-embed-image-box">
                    <h4>Small Image (320 pixels):</h4>
                    <textarea id="bs24-embed-small-image-input" class="bs24-embed-image-input" readonly></textarea>
                </div>
            </div>
            <div class="bs24-embed-popup-footer">
                <button class="bs24-embed-popup-close">Done</button>
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
    $credit_url = !empty( get_post_meta( $post->ID, 'bs24_iee_credit_url', true ) ) ? esc_ur( get_post_meta( $post->ID, 'bs24_iee_credit_url', true ) ) : '';

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
 * add credit field to image attachment edit screen
 */
// function bs24_iee_add_credit_fields(){
//     add_meta_box(
//         'bs24_iee_image_custom_field',
//         __( 'Embed image credit fields', 'bs24-image-external-embed' ),
//         'bs24_iee_credit_fields_callback',
//         'attachment',
//         'side'
//     );
// }

//add_action( 'add_meta_boxes', 'bs24_iee_add_credit_fields' );

/**
 * callback function for image credit custom meta field
 */
function bs24_iee_credit_fields_callback( $post ){
    wp_nonce_field( basename( __FILE__ ), 'bs24_image_credit_nonce' );

    $credit_text = !empty( get_post_meta( $post->ID, 'bs24_iee_image_credit', true ) ) ? get_post_meta( $post->ID, 'bs24_iee_image_credit', true ) : '';
    $credit_url = !empty( get_post_meta( $post->ID, 'bs24_iee_credit_url', true ) ) ? esc_ur( get_post_meta( $post->ID, 'bs24_iee_credit_url', true ) ) : '';
    ?>
    <div class="bs24-credit-field-wrap">
        <p>
            <label><?php echo __( 'Image Credit' , 'bs24-image-external-embed' ); ?></label>
            <div>
                <input type="text" name="bs24_iee_image_credit", id="bs24_iee_image_credit" value="<?php echo $credit_text; ?>" style="width: 100%">
            </div>
        </p>
        <p>
            <label><?php echo __( 'Image Credit URL' , 'bs24-image-external-embed' ); ?></label>
            <div>
                <input type="text" name="bs24_iee_credit_url", id="bs24_iee_credit_url" value="<?php echo $credit_url; ?>" style="width: 100%">
            </div>
        </p>
    </div>
    
    <?php
}

/**
 * Save image credit meta to database
 */
function bs24_iee_save_image_credit_meta( $post_id ){
    if( !isset( $_POST['bs24_image_credit_nonce'] ) || !wp_verify_nonce( $_POST['bs24_image_credit_nonce'], basename( __FILE__ ) ) ){
        return;
    }

    //check if user has permission to autosave 
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
        return;
    }

    if( 'attachment' !== get_post_type( $post_id ) ){
        return;
    }

    if( isset( $_POST['bs24_iee_image_credit'] ) ){
        update_post_meta( $post_id, 'bs24_iee_image_credit', wp_kses_post( $_POST['bs24_iee_image_credit'] ) );
    }

    if( isset( $_POST['bs24_iee_credit_url'] ) ){
        update_post_meta( $post_id, 'bs24_iee_credit_url', esc_url( $_POST['bs24_iee_credit_url'] ) );
    }
}
// add_action( 'save_post', 'bs24_iee_save_image_credit_meta' );