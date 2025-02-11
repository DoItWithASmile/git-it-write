<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace engineering\schumann\wordpress\webhook\common;
// IMPORTS
use engineering\schumann\http\common\HTTP_STATUS_CODE;


if ( ! defined( 'ABSPATH' ) ) exit;

// TODO: document class
class HttpResult {
    /**
     * error return
     */
    // TODO: document method
    public static function error(
        HTTP_STATUS_CODE $httpErrorCode,
        string           $wordpressErrorCode,
        string           $message, 
        array            $body = null
    ) : WP_Error {
        // log
        GIW_Utils::log( 'Error - ' . $message );

        // create body
        $data = array_merge(
            $body,
            array( 'status' => $httpErrorCode )
        );

        // return error 
        return new WP_Error( $wordpressErrorCode, $message, $data );
    }
}

?>