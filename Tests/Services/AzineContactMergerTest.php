<?php

namespace Azine\HybridAuthBundle\Tests\Services;

use Azine\HybridAuthBundle\Services\AzineContactMerger;
use Azine\HybridAuthBundle\Tests\AzineTestCase;

class AzineContactMergerTest extends AzineTestCase
{
    public function testMerge()
    {
        $merger = new AzineContactMerger();
        $input = array('xing' => array('a', 'b', 'c', 'd'), 'linkedin' => array('e', 'f', 'g'));
        $output = array('a', 'b', 'c', 'd', 'e', 'f', 'g');
        $this->assertSame($output, $merger->merge($input));
    }
}
