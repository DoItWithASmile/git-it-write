<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace engineering\schumann\common\helper;

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Boolean helper
 */
final class BooleanHelper {
    const TRUE_VALUES  = [ 1, '1', 'on',  'enabled',  'yes', 'true',  true ];
    const FALSE_VALUES = [ 0, '0', 'off', 'disabled', 'no',  'false', false, null, '' ];


    /**
     * checks if a value represents true
     * 
     * @param  object $value value to check
     * @return bool   true, if value represents true.
     */
    public static function isTrue(
        object $value
    ) : bool {
        return !$this->isFalse( $value );
    }


    /**
     * checks if a value represents false
     * 
     * @param  object $value value to check
     * @return bool   true, if value represents false.
     */
    public static function isFalse(
        object $value
    ) : bool {
        // check if value is null
        if ( $value == null ){
            return true;
        }

        // check if value is in array directly
        if ( in_array( $value, self::FALSE_VALUES ) ){
            return true;
        }

        // check if lower-case value is in array directly
        if ( in_array( strtolower( $value ), self::FALSE_VALUES ) ){
            return true;
        }

        // check if value is empty
        if ( empty($value) ){
            return true;
        }

        // nope
        return false;
    }
}


?>