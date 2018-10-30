<?php

namespace Azine\HybridAuthBundle\Tests\Services;

use Azine\HybridAuthBundle\Services\AzineGenderGuesser;
use Azine\HybridAuthBundle\Tests\AzineTestCase;

class AzineGenderGuesserTest extends AzineTestCase
{
    public function testGuess()
    {
        $guesser = new AzineGenderGuesser();
        $result = $guesser->guess('Patrick');
        $this->assertSame('m', $result['gender']);
        $result = $guesser->guess('Tom');
        $this->assertSame('m', $result['gender']);

        $result = $guesser->guess('Sonja');
        $this->assertSame('f', $result['gender']);
        $result = $guesser->guess('Petra');
        $this->assertSame('f', $result['gender']);

        $this->assertNull($guesser->guess(null));
    }

    public function testGender()
    {
        $guesser = new AzineGenderGuesser();
        $this->assertSame('m', $guesser->gender('Patrick'));
        $this->assertSame('m', $guesser->gender('Tom'));
        $this->assertSame('f', $guesser->gender('Sonja'));
        $this->assertSame('f', $guesser->gender('Petra'));
        $this->assertSame('', $guesser->gender('ertwerqwrfas'));
    }

    public function testGuesserWithOptions()
    {
        $females = array('lkjlkjlkj' => 1);
        $males = array('oiuoiuoui' => 1);
        $matchList = array('one_only',
                            'either_weight',
                            'one_only_metaphone',
                            'either_weight_metaphone',
                            'v2_rules',
                            'v1_rules', );

        // use with extra names supplied via constructor
        $guesser = new AzineGenderGuesser($females, $males, true, $matchList);

        $result = $guesser->guess('oiuoiuoui');
        $this->assertSame('m', $result['gender']);
        $result = $guesser->guess('Patrick');
        $this->assertSame('m', $result['gender']);
        $result = $guesser->guess('Tom');
        $this->assertSame('m', $result['gender']);

        $result = $guesser->guess('lkjlkjlkj');
        $this->assertSame('f', $result['gender']);
        $result = $guesser->guess('Sonja');
        $this->assertSame('f', $result['gender']);
        $result = $guesser->guess('Petra');
        $this->assertSame('f', $result['gender']);

        // use only names supplied via constructor
        $guesser = new AzineGenderGuesser($females, $males, false, array('one_only'));

        $result = $guesser->guess('oiuoiuoui');
        $this->assertSame('m', $result['gender']);

        $result = $guesser->guess('Patrick');
        $this->assertNull($result);
        $result = $guesser->guess('Tom');
        $this->assertNull($result);

        $result = $guesser->guess('lkjlkjlkj');
        $this->assertSame('f', $result['gender']);

        $result = $guesser->guess('Sonja');
        $this->assertNull($result);
        $result = $guesser->guess('Petra');
        $this->assertNull($result);
    }

    public function testGuesserWithEitherWeight()
    {
        $females = array();
        $males = array();
        $matchList = array('either_weight');

        // use with extra names supplied via constructor
        $guesser = new AzineGenderGuesser($females, $males, false, $matchList);

        $result = $guesser->guess('Randy');
        $this->assertSame('m', $result['gender']);
    }

    public function testGuesserWithRules1()
    {
        $females = array();
        $males = array();
        $matchList = array('v1_rules');

        // use with extra names supplied via constructor
        $guesser = new AzineGenderGuesser($females, $males, false, $matchList);

        $result = $guesser->guess('Randy');
        $this->assertSame('m', $result['gender']);

        $result = $guesser->guess('Lois');
        $this->assertSame('f', $result['gender']);
    }

    public function testGuesserWithRules2()
    {
        $females = array();
        $males = array();
        $matchList = array('v2_rules');

        // use with extra names supplied via constructor
        $guesser = new AzineGenderGuesser($females, $males, false, $matchList);

        $result = $guesser->guess('John');
        $this->assertSame('m', $result['gender']);
        $result = $guesser->guess('Anfernee');
        $this->assertSame('m', $result['gender']);

        $result = $guesser->guess('Trish');
        $this->assertSame('f', $result['gender']);
        $result = $guesser->guess('Ellie');
        $this->assertSame('f', $result['gender']);
    }

    public function testGuesserWithMetaPhone()
    {
        $females = array();
        $males = array();
        $matchList = array('one_only_metaphone');

        // use with extra names supplied via constructor
        $guesser = new AzineGenderGuesser($females, $males, true, $matchList);

        $result = $guesser->guess('Patric');
        $this->assertSame('m', $result['gender']);
        $result = $guesser->guess('Tomàs');
        $this->assertSame('m', $result['gender']);

        $result = $guesser->guess('Sonja');
        $this->assertSame('f', $result['gender']);
    }

    public function testGuesserWithMetaPhone2()
    {
        $females = array();
        $males = array();
        $matchList = array('either_weight_metaphone');

        // use with extra names supplied via constructor
        $guesser = new AzineGenderGuesser($females, $males, true, $matchList);

        $result = $guesser->guess('Patric');
        $this->assertSame('m', $result['gender']);
        $result = $guesser->guess('Tomàs');
        $this->assertSame('m', $result['gender']);

        $result = $guesser->guess('Sonja');
        $this->assertSame('f', $result['gender']);
    }
}
