<?php
// FILE USES STRICT TYPING
declare( strict_types=1 );
// NAMESPACE
namespace engineering\schumann\common\test;
// IMPORTS
use PHPUnit\Framework\TestCase;
use engineering\schumann\common\DataContainer;


/**
 * Test case for engineering.schumann.common.DataContainer;
 */
final class DataContainerTest
extends TestCase {

    /**
     * 
     */
    public function test...SomeAssumptionHere...(): void
    {
        $this->assertInstanceOf(
            Email::class,
            Email::fromString('user@example.com')
        );
    }
}