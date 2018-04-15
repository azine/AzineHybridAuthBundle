<?php

namespace Azine\HybridAuthBundle\Services;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AzineContactMergerTest extends TestCase
{
    public function testMerge()
    {
        $merger = new AzineContactMerger();
        $input = array('xing' => array('a', 'b', 'c', 'd'), 'linkedin' => array('e', 'f', 'g'));
        $output = array('a', 'b', 'c', 'd', 'e', 'f', 'g');
        $this->assertSame($output, $merger->merge($input));
    }
}
