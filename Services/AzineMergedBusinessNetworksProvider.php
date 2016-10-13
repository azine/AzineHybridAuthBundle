<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

use Symfony\Component\HttpFoundation\Session\Session;

class AzineMergedBusinessNetworksProvider {

	/**
	 * @var AzineHybridAuth
	 */
	private $hybridAuth;

	/**
	 * @var array
	 */
	private $contacts;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var array of provider ids
	 */
	private $providers;

	/**
	 * @var config of the providers
	 */
	private $providersConfig;

	/**
	 * @var array of provider ids that are loaded already
	 */
	private $loadedProviders;
	
	/**
	 * @var ContactSorter
	 */
	private $sorter;
	
	/**
	 * @var ContactMerger
	 */
	private $merger;

	/**
	 * @var GenderGuesser
	 */
	private $genderGuesser;

	/**
	 * @var string
	 */
	const CONTACTS_SESSION_NAME = "hybrid_auth_contacts";
	const LOADED_PROVIDERS_NAME = "hybrid_auth_loaded_providers";

	/**
	 * Get the contacts from all configured providers
	 * @param AzineHybridAuth $hybridAuth
	 * @param Session $session
	 * @param array $providers
	 */
	public function __construct(AzineHybridAuth $hybridAuth, Session $session, ContactSorter $sorter, ContactMerger $merger, GenderGuesser $genderGuesser, ContactFilter $contactFilter, array $providers){
		$this->hybridAuth = $hybridAuth;
		$this->sorter = $sorter;
		$this->merger = $merger;
		$this->contacts = $session->get(self::CONTACTS_SESSION_NAME, array());
		$this->loadedProviders = $session->get(self::LOADED_PROVIDERS_NAME, array());
		$this->providers = array_keys($providers);
		$this->providersConfig = $providers;
		$this->session = $session;
		$this->genderGuesser = $genderGuesser;
		$this->contactFilter = $contactFilter;
	}

    /**
     * Get user-profiles from xing and linked-in
     * @param int $pageSize
     * @param int $offset
     * @param array $filterParams
     * @return array
     */
	public function getContactProfiles($pageSize = 50, $offset = 0, $filterParams = array()){

		// check if the contacts are loaded already
		if(sizeof($this->providers) != sizeof($this->loadedProviders)){
			$this->getAllContacts();
		}

		// filter according to the $filterParams
		$contacts = $this->contactFilter->filter($this->contacts, $filterParams);

		// return one page
		$contacts =  array_slice($contacts, $offset, $pageSize, true);
		return $contacts;
	}

	/**
	 * Fetch all contacts from the networks
	 */
	private function getAllContacts(){
		$newContactsCount = 0;
		foreach ($this->providers as $provider){
			$connected = $this->hybridAuth->getProvider(null, $provider, false)->isUserConnected();
			if($connected && (!array_key_exists($provider, $this->loadedProviders) || sizeof($this->loadedProviders[$provider]) == 0)){
				$newContacts = $this->getUserContactsFor($provider);
				$this->loadedProviders[$provider] = $newContacts;
				$this->session->set(self::LOADED_PROVIDERS_NAME, $this->loadedProviders);
				$this->session->save();
				$newContactsCount += sizeof($newContacts);
			}
		}

		if($newContactsCount > 0) {
			// merge the old and new contacts
			$this->contacts = $this->merger->merge($this->loadedProviders);

			// sort all contacts
			usort($this->contacts, array($this->sorter, 'compare'));

			$this->session->set(self::CONTACTS_SESSION_NAME, $this->contacts);
			$this->session->save();
		}
	}

	/**
	 * Get ALL xing contacts of the current user
	 * @param $provider
	 * @return array
	 * @throws \Exception
	 */
	public function getUserContactsFor($provider){
		if($provider == "Xing"){
			return $this->getXingContacts();
		} elseif ($provider == "LinkedIn"){
			return $this->getLinkedInContacts();
		}

		$userContacts = array();
		foreach ($this->hybridAuth->getProvider(null, $provider)->getUserContacts() as $next){
			$nextContact = new UserContact($provider);
			$nextContact->identifier	= $next->identifier;
			$nextContact->profileURL	= $next->profileURL;
			$nextContact->firstName 	= $next->firstName;
			$nextContact->lastName		= $next->lastName;
			$nextContact->displayName	= $nextContact->firstName." ".$nextContact->lastName;
			$nextContact->description	= $next->description;
			$nextContact->email			= $next->email;
		}
		return $userContacts;
	}

    /**
     * Get ALL xing contacts of the current user
     * @throws \Exception
     * @return array of UserContact
     */
	public function getXingContacts(){
		$api = $this->hybridAuth->getXingApi();
		$userFields = implode(',', $this->providersConfig['xing']['fields']);
		$fetchSize = 100;
		$fetchOffset = 0;
		$fetchMore = true;
		$users = array();
		try {
			while ($fetchMore){
				$uri = sprintf('users/me/contacts?limit=%1$s&user_fields=%2$s&offset=%3$s', $fetchSize, $userFields, $fetchOffset);
				$oResponse = $api->get($uri);
				if(isset($oResponse->error_name)){
					throw new \Exception($oResponse->error_name." : ".$oResponse->message);
				}
				$users = array_merge($users, $oResponse->contacts->users);
				$fetchOffset = $fetchOffset + $fetchSize;
				$fetchMore = $fetchSize == sizeof($oResponse->contacts->users);
			}
		}
		catch(\Exception $e) {
			throw new \Exception('Could not fetch contacts. Xing returned an error.', $e->getCode(), $e);
		}


		// Create the contacts array.
		$xingContacts = array();
		foreach($users as $connection) {
			$xingContacts[] = $this->createUserContactFromXingProfile($connection);
		}

		return $xingContacts;
	}

    /**
     * Get ALL linkedin contacts of the current user
     * @throws \Exception
     * @return array of UserContact
     */
	public function getLinkedInContacts(){
		$api = $this->hybridAuth->getLinkedInApi();
		$userFields = implode(',', $this->providersConfig['linkedin']['fields']);
		$fetchSize = 500;
		$fetchMore = true;
		$fetchOffset = 0;
		$users = array();

		try{
			while ($fetchMore){
				$uri = sprintf('~/connections:(%1$s)?start=%2$s&count=%3$s', $userFields, $fetchOffset, $fetchSize);
				$response = $api->profile($uri);
				$connectionsXml = new \SimpleXMLElement( $response['linkedin'] );
				foreach ($connectionsXml->person as $person){
					$users[] = $person;
				}
				$fetchMore = $fetchSize == sizeof($connectionsXml->person);
				$fetchOffset = $fetchOffset + $fetchSize;
			}
		}
		catch( \LinkedInException $e ){
			throw new \Exception( "User contacts request failed! {$this->providerId} returned an error.", $e->getCode(), $e );
		}

		$contacts = array();
		foreach( $users as $connection ) {
            $contacts[] = $this->createUserContactFromLinkedInProfile($connection);
		}

		return $contacts;
	}
	
	/**
	 * Get the basic profile of the current users contact with the given user id.
	 * @param string $provider
	 * @param string $contactId
	 * @return UserContact
	 */
	public function getUserContactBasicProfile($provider, $contactId){
		if(!array_key_exists($provider, $this->loadedProviders)){
			$this->loadedProviders[$provider] = $this->getUserContactsFor($provider);
			$this->session->set(self::LOADED_PROVIDERS_NAME, $this->loadedProviders);
			$this->session->save();
		}
		
		foreach ($this->loadedProviders[$provider] as $userContact){
			if($userContact->identifier == $contactId){
				return $userContact;
			}
		}
		return null;
	}

    /**
     * Get the basic profile of the user with the given profileUrl
     * @param string $profileUrl
     * @throws \Exception
     * @return UserContact
     */
    public function getUserProfileByUrl($profileUrl){
        $matches = array();
        preg_match('/https?:\/\/.{0,5}(xing|linkedin)(\.ch|\.com).*/', $profileUrl, $matches);
        $provider = $matches[1];
        if(strpos($provider, "xing") !== false ){
            $matches = array();
            preg_match('/.*\/profile\/([]a-z,A-Z,_,0-9]*).*/', $profileUrl, $matches);
            if(sizeof($matches) != 2){
                return null;
            }
            $profilePage = $matches[1];
            $xingProfiles = $this->hybridAuth->getXingApi()->get("users/$profilePage.json");
            return $this->createUserContactFromXingProfile($xingProfiles->users[0]);

        } elseif (strpos($provider, "linkedin") !== false){
            $profileUrl = urlencode($profileUrl);
            try{
                $response = $this->hybridAuth->getLinkedInApi()->connections("url=$profileUrl:(id,first-name,last-name,picture-url,public-profile-url,summary,headline,specialities,email-address)");
            } catch( \LinkedInException $e ){
                throw new \Exception( "User profile by url request failed! linkedin returned an error.", $e->getCode(), $e );
            }
            $connectionsXml = new \SimpleXMLElement( $response['linkedin'] );
            return $this->createUserContactFromLinkedInProfile($connectionsXml);
        }
    }

	/**
	 * @param $xingProfile
	 * @return UserContact
	 */
    private function createUserContactFromXingProfile($xingProfile){
				$newContact = new UserContact("Xing");

				foreach ($xingProfile as $key => $value)  {
						$newContact->setField($key, $value);
				}

        return $newContact;
    }

	/**
	 * @param $linkedinProfile
	 * @return UserContact
	 */
    private function createUserContactFromLinkedInProfile($linkedinProfile){
        $newContact = new UserContact("LinkedIn");
        $newContact->identifier  = (string) $linkedinProfile->id;
        $newContact->firstName   = (string) $linkedinProfile->{'first-name'};
        $newContact->lastName    = (string) $linkedinProfile->{'last-name'};
        $newContact->displayName = (string) $linkedinProfile->{'first-name'} . " " . $linkedinProfile->{'last-name'};
        $newContact->profileURL  = (string) $linkedinProfile->{'public-profile-url'};
        if($newContact->profileURL == null) {
            $newContact->profileURL = (string)$linkedinProfile->{'site-standard-profile-request'};
        }
        $newContact->photoURL    = (string) $linkedinProfile->{'picture-url'};
        $newContact->description = (string) $linkedinProfile->{'summary'};
        $newContact->description .= $newContact->description == "" ? (string) $linkedinProfile->{'specialities'} : "\n". (string) $linkedinProfile->{'specialities'};;
		if($linkedinProfile->{'email-address'}) {
			$newContact->email = (string)$linkedinProfile->{'email-address'};
		}
        $newContact->gender 	 = $this->genderGuesser->gender($newContact->firstName, 5);
        $headline	             = (string) $linkedinProfile->{'headline'};
        $newContact->headline = str_replace(" at ", " @ ", $headline);

        return $newContact;
    }
}
