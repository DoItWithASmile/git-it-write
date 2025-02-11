<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace com\aakashweb\wordpress\plugin\git_it_write;
// IMPORTS
// none

// FIXME: refactor and document code


if( ! defined( 'ABSPATH' ) ) exit;

/**
 * ???
 */
final class GIW_Shortcodes {
    /* ====================
     * METHODS
     * ==================== */
    /**
     * initializes the plugin's shortcodes
     */
    public static function init() : void {
        add_shortcode( 'giw_edit_link', array( __CLASS__, 'edit_link' ) );
    }


    /**
     * Shortcode: edit_link
     */
    public static function edit_link( 
        $atts 
    ) {
        global $post;

        $current_post_id = '';
        if ( is_object( $post ) ){
            $current_post_id = $post->ID;
        }

        $atts = shortcode_atts( array(
            'post_id'   => $current_post_id,
            'text'      => 'Edit this page',
            'icon'      => '<i class="fas fa-pen"></i> &nbsp; ',
            'auto_p'    => false
        ), $atts );

        if ( empty( $atts[ 'post_id' ] ) ){
            return '';
        }

        $meta = get_post_meta( $atts[ 'post_id' ], '', true );

        if ( !array_key_exists( 'github_url', $meta ) || empty( $meta[ 'github_url' ][0] ) ){
            return '';
        }

        $github_url = $meta[ 'github_url' ][0];

        $link = '<a href="' . esc_url( $github_url ) . '" class="giw-edit_link" target="_blank" rel="noreferrer noopener">' . wp_kses_post( $atts[ 'icon' ] ) . esc_html( $atts[ 'text' ] ) . '</a>';

        if ( $atts[ 'auto_p' ] ){
            return '<p>' . $link . '</p>';
        } else {
            return $link;
        }
   }
}


/* ====================
 * REGISTER WITH WORDPRESS
 * ==================== */
GIW_Shortcodes::init();

?>