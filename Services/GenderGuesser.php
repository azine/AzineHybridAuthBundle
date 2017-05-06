<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

use Symfony\Component\HttpFoundation\Session\Session;

use Azine\HybridAuthBundle\DependencyInjection\AzineHybridAuthExtension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface GenderGuesser {

	/**
	 * Guess the gender for a given firstname. The guesser will return an array with 
	 * the sex (m or f) and a confidence indicator (float 0.0 - 1.0, 0 = no confidence, 1 = sure)
	 * 
	 * @param \string $firstName
	 * @param int $looseness
	 * @return array(sex => confidence);
	 */
	public function guess($firstName, $looseness=1);

	/**
	 * Guess the gender for a given firstname. The best guess is returned, ignoring the low confidence level.
	 * 
	 * @param \string $firstName
	 * @param int $looseness
	 * @return \string sex => m | f | ''
	 */
	public function gender($firstName, $looseness=1);	
}