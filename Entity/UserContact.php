<?php

namespace Azine\HybridAuthBundle\Entity;

class UserContact
{
    /* The Unique contact user ID */
    public $identifier = null;

    /* User website, blog, web page */
    public $webSiteURL = null;

    /* URL link to profile page on the IDp web site */
    public $profileURL = null;

    /* URL link to user photo or avatar */
    public $photoURL = null;

    /* Url link to user photo or avatar (bigger)*/
    public $photoUrlBig = null;

    /* Gender */
    public $gender = null;

    /* The users last name */
    public $firstName = null;

    /* The users first name */
    public $lastName = null;

    /* User displayName provided by the IDp or a concatenation of first and last name */
    public $displayName = null;

    /* A short about_me */
    public $description = null;

    /* The users primary work-company */
    public $company = null;

    /* the users title */
    public $title = null;

    /* User email. Not all of IDp grant access to the user email */
    public $email = null;

    /* Prvider id */
    public $provider = null;

    /* For Xing & LinkedIn usually job-title @ main company */
    public $headline = null;

    /* array of tags associated with this user. Null if not yet loaded. */
    public $tags = null;

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
     * @param array | null  $tags        array of strings
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
                            ) {
        $this->provider = $provider;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->identifier = $identifier;
        $this->webSiteURL = $webSiteURL;
        $this->profileURL = $profileURL;
        $this->photoURL = $photoURL;
        $this->displayName = $firstName.' '.$lastName;
        $this->description = $description;
        $this->email = $email;
        $this->headline = $headline;
        $this->company = $company;
        $this->title = $title;
        $this->tags = $tags;
    }
}
