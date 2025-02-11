<?php
/**
* Plugin Name:    Git it Write - PHP 8.0.2+
* Plugin URI:     https://schumann.engineering/wordpress-plugins/git-it-write/
* Description:    Publish markdown files present in a Github repository as posts to WordPress automatically. This is a complete rewrite of the original Git it Write plugin by Aakash Chakravarthy based on PHP 8.0.2+ including more advanced features, better code documentation & testing for better code quality.
* Author:         Janek Schumann
* Author URI:     https://schumann.engineering/
* Version:        2.0-SE
*/

// FILE USES STRICT TYPING
declare( strict_types=1 );
// DECLARATIONS
define( 'GIW_VERSION',      '2.0-SE' );
define( 'GIW_PATH',         plugin_dir_path( __FILE__ ) ); // All have trailing slash
define( 'GIW_ADMIN_URL',    trailingslashit( plugin_dir_url( __FILE__ ) . 'admin' ) );
// NAMESPACE
// default namespace
// IMPORTS
use engineering\schumann\wordpress\plugin\git_it_write\Git_It_Write_SE_Edition_Plugin;


/* ====================
 * initialize plugin
 * ==================== */
Git_It_Write_SE_Edition_Plugin::init();

?>