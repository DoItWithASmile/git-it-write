<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace engineering\schumann\wordpress\plugin\git_it_write;


/**
 * Plugin class for "Git It Write PHP 7.4+"
 */
final class Git_It_Write_SE_Edition_Plugin {
    /* ====================
     * CONSTANTS
     * ==================== */
    const DEFAULT_CONFIG = array(
        'username'          => '',
        'repository'        => '',
        'folder'            => '',
        'branch'            => 'master',
        'post_type'         => '',
        'post_author'       => 1,
        'content_template'  => '%%content%%',
        'last_publish'      => 0
    );


    const DEFAULT_GENERAL_SETTINGS = array(
        'webhook_secret'        => '',
        'github_username'       => '',
        'github_access_token'   => ''
    );


    const DEFAULT_ALLOWED_FILE_TYPES = array(
        'md'
    );


    /* ====================
     * METHODS
     * ==================== */
    /**
     * initialize plugin
     */
    public static function init() : void {
        self::includes();
    }


    /**
     * all the includes we need
     */
    public static function includes() : void {
        require __DIR__ . '/vendor/autoload.php';

        require_once( GIW_PATH . 'includes/utilities.php' );
        require_once( GIW_PATH . 'includes/repository.php' );
        require_once( GIW_PATH . 'includes/publisher.php' );
        require_once( GIW_PATH . 'includes/publish-handler.php' );
        require_once( GIW_PATH . 'includes/parsedown.php' );
        require_once( GIW_PATH . 'includes/webhook.php' );
        require_once( GIW_PATH . 'includes/shortcodes.php' );
        require_once( GIW_PATH . 'includes/metadata.php' );
        require_once( GIW_PATH . 'includes/utils/boolean-helper.php' );

        require_once( GIW_PATH . 'admin/admin.php' );
    }


    /**
     * ???
     */
    public static function all_repositories() : array {
        $repos_raw      = get_option( 'giw_repositories', array( array() ) );
        $repos          = array();
        $default_config = self::DEFAULT_CONFIG;

        foreach( $repos_raw as $id => $config ){
            array_push( $repos, wp_parse_args( $config, $default_config ) );
        }

        return $repos;
    }


    /**
     * ???
     */
    public static function general_settings(){
        $settings = get_option( 'giw_general_settings', array() );
        $default_settings = self::DEFAULT_GENERAL_SETTINGS;

        return wp_parse_args( $settings, $default_settings );
    }
}

?>