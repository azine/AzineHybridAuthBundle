<?php
namespace Azine\HybridAuthBundle\Entity;


class UserContact {

	/* The Unique contact user ID */
	public $identifier = NULL;
	
	/* User website, blog, web page */
	public $webSiteUrl = NULL;
	
	/* Url link to profile page on the IDp web site */
	public $profileUrl = NULL;

    /* Url link to user photo or avatar */
    public $photoUrl = NULL;

    /* Url link to user photo or avatar (bigger)*/
    public $photoUrlBig = NULL;

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
	
	/* For Xing & LinkedIn usually job-title @ main ompany */
	public $headline = NULL;
	
	public function __construct($provider,
								$gender = null,
								$firstName = null, 
								$lastName = null,
								$identifier = null, 
								$webSiteUrl = null, 
								$profileUrl = null, 
								$photoUrl = null, 
								$description = null, 
								$email = null,
								$headline = null
							){
		$this->provider = $provider;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
	    $this->identifier = $identifier;
		$this->webSiteUrl = $webSiteUrl;
		$this->profileUrl = $profileUrl;
		$this->photoUrl = $photoUrl;
		$this->displayName = $firstName." ".$lastName;
		$this->description = $description;
		$this->email = $email;
		$this->headline = $headline;

	}

}

