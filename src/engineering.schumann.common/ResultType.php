<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace engineering\schumann\common;


if( ! defined( 'ABSPATH' ) ) exit;

/**
 * result type of operations
 */
enum E_SE_RESULT_TYPE {
    case SUCCESS;
    case FAIL;
    case SKIPPED;
}