<?php

namespace Azine\HybridAuthBundle\Tests\Services;

use Azine\HybridAuthBundle\Services\AzineMergedBusinessNetworksProvider;

class AzineMergedBusinessNetworksProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContactProfiles()
    {
        /*
                public function getContactProfiles($pageSize = 50, $offset = 0, $tryToConnect = false, $filterParams = array()){
                    // check if the contacts are loaded already
                    if(sizeof($this->providers) != sizeof($this->loadedProviders)){
                    foreach ($this->providers as $provider){
                        $connected = $this->hybridAuth->getProvider($provider, false)->isUserConnected();
                        if($connected && (!array_key_exists($provider, $this->loadedProviders) || sizeof($this->loadedProviders[$provider]) == 0)){
                            $newContacts = $this->getUserContactsFor($provider);
                            $this->loadedProviders[$provider] = $newContacts;
                            $this->session->set(self::LOADED_PROVIDERS_NAME, $this->loadedProviders);
                            $this->session->save();
                        }
                        // merge the old and new contacts
                        $this->contacts = $this->merger->merge($this->loadedProviders);
        
                        // sort all contacts
                        usort($this->contacts, array($this->sorter, 'compare'));
        
                        $this->session->set(self::CONTACTS_SESSION_NAME, $this->contacts);
                        $this->session->save();
                    }
        
                    // return one page
                    return array_slice($this->contactFilter->filter($this->contacts, $filterParams), $offset, $pageSize, true);
                }
        */
        $providers = array();
        $mocks = $this->getMocks();
        $sessionMock = $mocks['session'];
        $sessionMock->expects($this->exactly(2))->method('get')->will($this->returnValue(array()));
        $filterMock = $mocks['contactFilter'];
        $filterMock->expects($this->exactly(1))->method('filter')->will($this->returnValue(array()));
        $service = $this->getAzineMergedBusinessNetworkProvider($mocks, $providers);
        $service->getContactProfiles();
    }

    public function testGetUserContactsFor()
    {
        /*
            public function getUserContactsFor($provider){
                if($provider == "Xing"){
                    return $this->getXingContacts();
                } elseif ($provider == "LinkedIn"){
                    return $this->getLinkedInContacts();
                }
        
                $userContacts = array();
                foreach ($this->hybridAuth->getProvider($provider)->getUserContacts() as $next){
                    $nextContact = new UserContact($provider);
                    $nextContact->identifier	= $next->identifier;
                    $nextContact->profileURL	= $next->profileURL;
                    $nextContact->firstName 	= $next->firstName;
                    $nextContact->lastName		= $next->lastName;
                    $nextContact->displayName	= $nextContact->firstName." ".$nextContact->lastName;
                    $nextContact->description	= $next->description;
                    $nextContact->email			= $next->email;
                }
                return $usersContacts;
            }
         */
    }

    public function testGetXingContacts()
    {
        /*
            public function getXingContacts(){
                $api = $this->hybridAuth->getXingApi();
                $fetchSize = 100;
                $fetchOffset = 0;
                $fetchMore = true;
                $users = array();
                try {
                    while ($fetchMore){
                        $oResponse = $api->get("users/me/contacts?limit=$fetchSize&user_fields=id,display_name,permalink,web_profiles,photo_urls,first_name,last_name,interests,gender,active_email,professional_experience&offset=$fetchOffset");
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
                    $nextContact = new UserContact("Xing");
                    $nextContact->identifier	= (property_exists($connection, 'id'))          	? $connection->id           : '';
                    $nextContact->profileURL	= (property_exists($connection, 'permalink'))   	? $connection->permalink    : '';
                    $nextContact->firstName 	= (property_exists($connection, 'first_name'))		? $connection->first_name 	: '';
                    $nextContact->lastName		= (property_exists($connection, 'last_name')) 		? $connection->last_name 	: '';
                    $nextContact->displayName	= $nextContact->firstName." ".$nextContact->lastName;
                    $nextContact->description	= (property_exists($connection, 'interests'))   	? $connection->interests    : '';
                    $nextContact->email			= (property_exists($connection, 'active_email'))	? $connection->active_email : '';
                    $nextContact->gender		= (property_exists($connection, 'gender'))			? $connection->gender : '';
        
                    // headline title @ company
                    if(property_exists($connection, 'professional_experience')){
                        $jobTitle = $connection->professional_experience->primary_company->title;
                        $company = $connection->professional_experience->primary_company->name;
                        $nextContact->headline = $jobTitle . " @ " . $company;
                    } else {
                        $nextContact->headline	= "";
                    }
        
                    // My own priority: Homepage, blog, other, something else.
                    if (property_exists($connection, 'web_profiles')) {
                        $nextContact->webSiteURL = (property_exists($connection->web_profiles, 'homepage')) ? $connection->web_profiles->homepage[0] : null;
                        if (null === $nextContact->webSiteURL) {
                            $nextContact->webSiteURL = (property_exists($connection->web_profiles, 'blog')) ? $connection->web_profiles->blog[0] : null;
                        }
                        if (null === $nextContact->webSiteURL) {
                            $nextContact->webSiteURL = (property_exists($connection->web_profiles, 'other')) ? $connection->web_profiles->other[0] : null;
                        }
                        // Just use *anything*!
                        if (null === $nextContact->webSiteURL) {
                            foreach ($connection->web_profiles as $aUrl) {
                                $nextContact->webSiteURL = $aUrl[0];
                                break;
                            }
                        }
                    }
        
                    // We use the largest picture available.
                    if (property_exists($connection, 'photo_urls') && property_exists($connection->photo_urls, 'large')) {
                        $nextContact->photoURL = (property_exists($connection->photo_urls, 'large')) ? $connection->photo_urls->large : '';
                    }
        
                    $xingContacts[] = $nextContact;
                }
        
                return $xingContacts;
            }
        
         */
    }

    public function testGetLinkedInContacts()
    {
        /*
            public function getLinkedInContacts(){
                $api = $this->hybridAuth->getLinkedInApi();
                $fetchSize = 500;
                $fetchMore = true;
                $fetchOffset = 0;
                $users = array();
        
                try{
                    while ($fetchMore){
                        $response = $api->profile("~/connections:(id,first-name,last-name,picture-url,public-profile-url,summary,headline)?start=$fetchOffset&count=$fetchSize");
                        $connectionsXml = new \SimpleXMLElement( $response['linkedin'] );
                        foreach ($connectionsXml->person as $person){
                            $users[] = $person;
                        }
                        $fetchMore = $fetchSize == sizeof($connectionsXml->person);
                        $fetchOffset = $fetchOffset + $fetchSize;
                    }
                }
                catch( \LinkedInException $e ){
                    throw new Exception( "User contacts request failed! {$this->providerId} returned an error.", $e->getCode(), $e );
                }
        
        
                $contacts = array();
        
                foreach( $users as $connection ) {
                    $nextContact = new UserContact("LinkedIn");
        
                    $nextContact->identifier  = (string) $connection->id;
                    $nextContact->firstName = (string) $connection->{'first-name'};
                    $nextContact->lastName = (string) $connection->{'last-name'};
                    $nextContact->displayName = (string) $connection->{'first-name'} . " " . $connection->{'last-name'};
                    $nextContact->profileURL  = (string) $connection->{'site-standard-profile-request'};
                    $nextContact->photoURL    = (string) $connection->{'picture-url'};
                    $nextContact->description = (string) $connection->{'summary'};
                    $nextContact->gender 	 = $this->genderGuesser->gender($nextContact->firstName, 5);
        
                    $nextContact->headline	= (property_exists($connection, 'headline'))			? (string) $connection->headline : '';
                    $nextContact->headline = str_replace(" at ", " @ ", $nextContact->headline);
        
                    $contacts[] = $nextContact;
                }
        
                return $contacts;
            }
         */
    }

    public function testGetUserContactBasicProfile()
    {
        /*
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
        
         */
    }

    private function getAzineMergedBusinessNetworkProvider(array $mocks, array $providers)
    {
        return new AzineMergedBusinessNetworksProvider(
            $mocks['hybridAuth'],
            $mocks['session'],
            $mocks['contactSorter'],
            $mocks['contactMerger'],
            $mocks['genderGuesser'],
            $mocks['contactFilter'],
            $providers
        );
    }

    private function getMocks()
    {
        $mocks = array();
        $mocks['hybridAuth'] = $this->getMockBuilder('Azine\HybridAuthBundle\Services\AzineHybridAuth')->disableOriginalConstructor()->getMock();
        $mocks['session'] = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')->disableOriginalConstructor()->getMock();
        $mocks['contactSorter'] = $this->getMockBuilder('Azine\HybridAuthBundle\Services\ContactSorter')->disableOriginalConstructor()->getMock();
        $mocks['contactMerger'] = $this->getMockBuilder('Azine\HybridAuthBundle\Services\ContactMerger')->disableOriginalConstructor()->getMock();
        $mocks['genderGuesser'] = $this->getMockBuilder('Azine\HybridAuthBundle\Services\GenderGuesser')->disableOriginalConstructor()->getMock();
        $mocks['contactFilter'] = $this->getMockBuilder('Azine\HybridAuthBundle\Services\ContactFilter')->disableOriginalConstructor()->getMock();

        return $mocks;
    }
}
