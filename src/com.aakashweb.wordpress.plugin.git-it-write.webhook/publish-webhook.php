<?php
// FIXME: don't code for GitHub only. Other sources of webhook might be interessting as well

// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace com\aakashweb\wordpress\plugin\git_it_write\webhook;
// IMPORTS
use com\aakashweb\wordpress\webhook\HttpResult;
use com\aakashweb\wordpress\plugin\git_it_write\GIW_PublishHandler;


if ( ! defined( 'ABSPATH' ) ) exit;

final class GIW_PublishWebhook {
    /**
     * initializes webhook
     */
    public static function init() : void {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'giw/v1', '/publish', array(
                'methods'             => 'POST',
                'callback'            => array( __CLASS__, 'handle_webhook'),
                'permission_callback' => array( __CLASS__, 'check_permission' )
            ));
        });
    }

    /**
     * handler for webhook
     * 
     * @param  WP_REST_Request $request the request itself
     * 
     * @return object
     */
    public static function handle_webhook( 
        WP_REST_Request $request
    ) : object {
        $result = null;

        // retrieve information from request
        $body           = $request->get_json_params();
        $event          = $request->get_header( 'X-GitHub-Event' );
        $repositoryName = $body[ 'repository' ][ 'full_name' ];

        // log
        if ( $request->get_header( 'X-GitHub-Delivery' ) ){
            GIW_Utils::log('Got webhook delivery ' . $request->get_header( 'X-GitHub-Delivery' ) );
        }

        GIW_Utils::log( "Received ${event} event from GitHub for repository '${repositoryName}'" );

        // handle event
        switch ( $event ){
            case 'ping':
                $result = self::handleEvent_Ping( $request, $body );
                break;

            case 'push':
                $result = self::handleEvent_Push( $request, $body );
                break;

            default:
                $result = HttpResult::error( HTTPS_STATUS_CODE::STATUS_501_Not_Implemented, 'unsupported_event', "Unsupported event: ${event}" );
                break;
        }

        // log
        if ( !is_wp_error( $result ) ){
            GIW_Utils::log( 'SUCCESS - honored webhook event.' );
        } else {
            GIW_Utils::log( 'FAILED - webhook event could not be processed successfully' );
        }

        // == SUCCESS? ==
        return $result;
    }


    /**
     * Handle git event: ping
     * 
     * @see https://docs.github.com/en/developers/webhooks-and-events/webhooks/webhook-events-and-payloads#ping
     * 
     * @param  WP_REST_Request $request the request itself
     * @param  array           $body
     * 
     * @return object
     */
    private static function handleEvent_Ping(
        WP_REST_Request $request,
        array           $body
    ) : object {
        // == LOGIC ==
        $result = 'pong';

        // == SUCCESS ==
        return $result;
    }


    /**
     * Handle git event: push
     * 
     * @see https://docs.github.com/en/developers/webhooks-and-events/webhooks/webhook-events-and-payloads#push
     * 
     * @param  WP_REST_Request $request the request itself
     * @param  array           $body
     * 
     * @return object
     */
    private static function handleEvent_Push(
        WP_REST_Request $request,
        array           $body
    ) : object {
        // == LOGIC ==
        // retrieve information from request
        $before         = $body[ 'before' ];
        $after          = $body[ 'after' ];
        $date           = $body[ 'pusher' ][ 'date' ];
        $author         = $body[ 'pusher' ][ 'name' ];
        $username       = $body[ 'pusher' ][ 'username' ];
        $ref            = $body[ 'ref' ];
        $repositoryName = $body[ 'repository' ][ 'full_name' ];

        // log
        GIW_Utils::log( "Processing commit from '${before}' up to '${after}' made on '${date}' by '${author} (${username})' for ref '${ref}'" );

        // Load media related WP files to upload images when in REST API mode
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // trigger publish handler for repository
        $result = GIW_Publish_Handler::publishRepositoryByFullName( $repositoryName );

        // == SUCCESS ==
        return $result;
    }    

    
    /**
     * check permission of request
     * 
     * NOTE: No authentication needed right now. But a couple of checks should be done.
     * 
     * @param  WP_REST_Request $request the request itself
     * 
     * @return bool|WP_Error   true if successful, WP_Error if failed
     */
    public static function check_permission(
        WP_REST_Request $request
    ) /* : bool|WP_Error */ {
        // == GUARDS ==
        /*
         * 4xx = client side checks
         */
        // User agent check
        if ( !$request->get_header( 'User-Agent' ) ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_403_Forbidden, 'no_user_agent', 'No user agent' );
        }

        if ( strpos( $request->get_header( 'User-Agent' ), 'GitHub-Hookshot' ) === false ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_403_Forbidden, 'who_are_you', 'Who are you ?' );
        }

        // check github event exists
        if ( !$request->get_header( 'X-GitHub-Event' ) ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_400_Bad_Request, 'no_github_event', 'No event from github' );
        }

        // check signature exists
        if ( !$request->get_header( 'X-Hub-Signature' ) ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_401_Unauthorized, 'no_secret_configured', 'No secret configured' );
        }

        // get request as json
        $body = $request->get_json_params();
        // repository not set
        if ( !isset( $body[ 'repository' ] ) ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_500_Internal_Server_Error, 'invalid_data', 'Invalid data: repository not set' );
        }
        
        if ( !isset( $body[ 'repository' ][ 'full_name' ] ) ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_500_Internal_Server_Error, 'invalid_data', 'Invalid data: full name of repository not set in "repository"' );
        }

        /*
         * 5xx = server side checks
         */
        // ensure type of event is acceptable
        $event = $request->get_header( 'X-GitHub-Event' );
        if ( !in_array( $event, array( 'ping', 'push' ) ) ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_501_Not_Implemented, 'unsupported_event', "Unsupported event: ${event}" );
        }

        // check signature
        $got_signature = $request->get_header( 'X-Hub-Signature' );

        $settings = Git_It_Write_SE_Edition::general_settings();
        $secret = trim( $settings[ 'webhook_secret' ] );

        // guard: secret not set
        if ( empty( $secret ) ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_500_Internal_Server_Error, 'no_server_secret', 'No secret configured on server' );
        }

        // guard: secret mismatch
        if ( !hash_equals( 'sha1=' . hash_hmac( 'sha1', $request->get_body(), $secret ), $got_signature ) ){
            // == FAIL ==
            return HttpResult::error( HTTPS_STATUS_CODE::STATUS_401_Unauthorized, 'signature_mismatch', 'Signature mismatch' );
        }

        // == SUCCESS ==
        return true;
    }

}


/* ====================
 * REGISTER WITH WORDPRESS
 * ==================== */
GIW_PublishWebhook::init();

?>