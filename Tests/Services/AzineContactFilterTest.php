<?php

namespace Azine\HybridAuthBundle\Tests\Services;

use Azine\HybridAuthBundle\Services\AzineContactFilter;
use Azine\HybridAuthBundle\Tests\AzineTestCase;

class AzineContactFilterTest extends AzineTestCase
{
    public function testFilter()
    {
        $filter = new AzineContactFilter();
        $testData = array('a', 'b', 'c');
        $this->assertSame($testData, $filter->filter($testData, $testData));
    }
}
