<?php

namespace Saft\Sparql\Test\Result;

use Saft\Sparql\Result\EmptyResultImpl;
use Saft\Test\TestCase;

class EmptyResultImplTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new EmptyResultImpl();
    }

    /*
     * Tests for isEmptyResult
     */

    public function testIsEmptyResult()
    {
        $this->assertTrue($this->fixture->isEmptyResult());
    }

    /*
     * Tests for isSetResult
     */

    public function testIsSetResult()
    {
        $this->assertFalse($this->fixture->isSetResult());
    }

    /*
     * Tests for isStatementSetResult
     */

    public function testIsStatementSetResult()
    {
        $this->assertFalse($this->fixture->isStatementSetResult());
    }

    /*
     * Tests for isValueResult
     */

    public function testIsValueResult()
    {
        $this->assertFalse($this->fixture->isValueResult());
    }
}
