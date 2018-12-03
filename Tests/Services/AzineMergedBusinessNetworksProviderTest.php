<?php

namespace Azine\HybridAuthBundle\Tests\Services;

use Azine\HybridAuthBundle\Services\AzineMergedBusinessNetworksProvider;

class AzineMergedBusinessNetworksProviderTest extends \PHPUnit\Framework\TestCase
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
