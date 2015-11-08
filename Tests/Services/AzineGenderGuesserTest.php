<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AzineGenderGuesserTest extends TestCase {

	public function testGuess(){
		$guesser = new AzineGenderGuesser();
		$result = $guesser->guess("Patrick");
		$this->assertEquals("m", $result['gender']);
		$result = $guesser->guess("Tom");
		$this->assertEquals("m", $result['gender']);

		$result = $guesser->guess("Sonja");
		$this->assertEquals("f", $result['gender']);
		$result = $guesser->guess("Petra");
		$this->assertEquals("f", $result['gender']);

		$this->assertNull($guesser->guess(null));
	}


	public function testGender(){
		$guesser = new AzineGenderGuesser();
		$this->assertEquals("m", $guesser->gender("Patrick"));
		$this->assertEquals("m", $guesser->gender("Tom"));
		$this->assertEquals("f", $guesser->gender("Sonja"));
		$this->assertEquals("f", $guesser->gender("Petra"));
		$this->assertEquals('', $guesser->gender("ertwerqwrfas"));
	}

	public function testGuesserWithOptions(){
		$females = array("lkjlkjlkj" => 1);
		$males = array("oiuoiuoui" => 1);
		$matchList = array(	'one_only',
							'either_weight',
							'one_only_metaphone',
							'either_weight_metaphone',
							'v2_rules',
							'v1_rules');

		// use with extra names supplied via constructor
		$guesser = new AzineGenderGuesser($females, $males, true, $matchList);

		$result = $guesser->guess("oiuoiuoui");
		$this->assertEquals("m", $result['gender']);
		$result = $guesser->guess("Patrick");
		$this->assertEquals("m", $result['gender']);
		$result = $guesser->guess("Tom");
		$this->assertEquals("m", $result['gender']);

		$result = $guesser->guess("lkjlkjlkj");
		$this->assertEquals("f", $result['gender']);
		$result = $guesser->guess("Sonja");
		$this->assertEquals("f", $result['gender']);
		$result = $guesser->guess("Petra");
		$this->assertEquals("f", $result['gender']);


		// use only names supplied via constructor
		$guesser = new AzineGenderGuesser($females, $males, false, array('one_only'));

		$result = $guesser->guess("oiuoiuoui");
		$this->assertEquals("m", $result['gender']);

		$result = $guesser->guess("Patrick");
		$this->assertNull($result);
		$result = $guesser->guess("Tom");
		$this->assertNull($result);

		$result = $guesser->guess("lkjlkjlkj");
		$this->assertEquals("f", $result['gender']);

		$result = $guesser->guess("Sonja");
		$this->assertNull($result);
		$result = $guesser->guess("Petra");
		$this->assertNull($result);

	}

	public function testGuesserWithEitherWeight()
	{
		$females = array();
		$males = array();
		$matchList = array('either_weight');

		// use with extra names supplied via constructor
		$guesser = new AzineGenderGuesser($females, $males, false, $matchList);

		$result = $guesser->guess("Randy");
		$this->assertEquals("m", $result['gender']);
	}


	public function testGuesserWithRules1()
	{
		$females = array();
		$males = array();
		$matchList = array('v1_rules');

		// use with extra names supplied via constructor
		$guesser = new AzineGenderGuesser($females, $males, false, $matchList);

		$result = $guesser->guess("Randy");
		$this->assertEquals("m", $result['gender']);

		$result = $guesser->guess("Lois");
		$this->assertEquals("f", $result['gender']);
	}

	public function testGuesserWithRules2()
	{
		$females = array();
		$males = array();
		$matchList = array('v2_rules');

		// use with extra names supplied via constructor
		$guesser = new AzineGenderGuesser($females, $males, false, $matchList);

		$result = $guesser->guess("John");
		$this->assertEquals("m", $result['gender']);
		$result = $guesser->guess("Anfernee");
		$this->assertEquals("m", $result['gender']);


		$result = $guesser->guess("Trish");
		$this->assertEquals("f", $result['gender']);
		$result = $guesser->guess("Ellie");
		$this->assertEquals("f", $result['gender']);

	}

	public function testGuesserWithMetaPhone()
	{
		$females = array();
		$males = array();
		$matchList = array('one_only_metaphone');

		// use with extra names supplied via constructor
		$guesser = new AzineGenderGuesser($females, $males, true, $matchList);

		$result = $guesser->guess("Patric");
		$this->assertEquals("m", $result['gender']);
		$result = $guesser->guess("Tomàs");
		$this->assertEquals("m", $result['gender']);

		$result = $guesser->guess("Sonja");
		$this->assertEquals("f", $result['gender']);
	}

	public function testGuesserWithMetaPhone2()
	{
		$females = array();
		$males = array();
		$matchList = array('either_weight_metaphone');

		// use with extra names supplied via constructor
		$guesser = new AzineGenderGuesser($females, $males, true, $matchList);

		$result = $guesser->guess("Patric");
		$this->assertEquals("m", $result['gender']);
		$result = $guesser->guess("Tomàs");
		$this->assertEquals("m", $result['gender']);

		$result = $guesser->guess("Sonja");
		$this->assertEquals("f", $result['gender']);
	}

}