<?php
namespace Azine\HybridAuthBundle\Entity;


class UserContact {

	/* The Unique contact user ID */
	public $identifier = NULL;
	
	/* User website, blog, web page */
	public $webSiteURL = NULL;
	
	/* URL link to profile page on the IDp web site */
	public $profileURL = NULL;
	
	/* URL link to user photo or avatar */
	public $photoURL = NULL;
	
	/* Gender */
	public $gender = null;
	
	/* The users last name */
	public $firstName = null;

	/* The users first name */
	public $lastName = null;

	/* User displayName provided by the IDp or a concatenation of first and last name */
	public $displayName = NULL;
	
	/* A short about_me */
	public $description = NULL;
	
	/* User email. Not all of IDp grant access to the user email */
	public $email = NULL;
	
	/* Prvider id */
	public $provider = NULL;
	
	public function __construct($provider,
								$gender = null,
								$firstName = null, 
								$lastName = null,
								$identifier = null, 
								$webSiteURL = null, 
								$profileURL = null, 
								$photoURL = null, 
								$description = null, 
								$email = null
							){
		$this->provider = $provider;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
	    $this->identifier = $identifier;
		$this->webSiteURL = $webSiteURL;
		$this->profileURL = $profileURL;
		$this->photoURL = $photoURL;
		$this->displayName = $firstName." ".$lastName;
		$this->description = $description;
		$this->email = $email;

	}

}

