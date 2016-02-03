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

	/* The users primary work-company */
	public $company = NULL;

	/* the users title */
	public $title = NULL;
	
	/* User email. Not all of IDp grant access to the user email */
	public $email = NULL;
	
	/* Prvider id */
	public $provider = NULL;
	
	/* For Xing & LinkedIn usually job-title @ main company */
	public $headline = NULL;

	/* array of tags associated with this user. Null if not yet loaded. */
	public $tags = NULL;

    /**
     * @param $provider
     * @param string | null $gender
     * @param string | null $firstName
     * @param string | null $lastName
     * @param string | null $identifier
     * @param string | null $webSiteURL
     * @param string | null $profileURL
     * @param string | null $photoURL
     * @param string | null $description
     * @param string | null $email
     * @param string | null $headline
     * @param array | null $tags array of strings
     */
    public function __construct($provider,
								$gender = null,
								$firstName = null, 
								$lastName = null,
								$identifier = null, 
								$webSiteURL = null,
								$profileURL = null,
								$photoURL = null,
								$description = null, 
								$email = null,
								$headline = null,
								$company = null,
								$title = null,
								array $tags = null
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
		$this->headline = $headline;
		$this->company = $company;
		$this->title = $title;
		$this->tags = $tags;

	}

}

