<?php

namespace Azine\HybridAuthBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Azine\HybridAuthBundle\Services\AzineHybridAuth;

use Azine\HybridAuthBundle\Services\AzineMergedBusinessNetworksProvider;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AzineHybridAuthJsonController extends Controller {

    /**
     * Check if the user is connected to the requested provider.
     * @param Request $request
     * @param unknown_type $provider
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function isConnectedAction(Request $request, $provider){
    	$connected = $this->getAzineHybridAuthService()->isConnected($provider);
    	return new JsonResponse(array('connected' => $connected));
    }

    /**
     * Try to connect to the provider
     * @param Request $request
     * @param string $provider
     */
    public function connectUserAction(Request $request, $provider){
    	$hybridAuth = $this->getAzineHybridAuthService()->getInstance();

    	if(!$hybridAuth->isConnectedWith($provider)){
    		try {
    			$adapter = $hybridAuth->getAdapter($provider);
    			$adapter->login();
    		} catch (\Exception $e) {
    			throw new \Exception("Unable to create adapter for provider '$provider'. Is it configured properly?", $e->getCode(), $e);
    		}
    	} else {
    		return new JsonResponse(array('connected' => true));
    	}
    }

    /**
     * Get the users Profile for the requested provider
     * @param Request $request
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function profileAction(Request $request, $provider){
    	$profile = $this->getAzineHybridAuthService()->getProvider($provider)->getUserProfile();
    	return new JsonResponse(array('profile' => $profile));
    }

    /**
     * Get all users contacts for the requested provider
     * @param Request $request
     * @param unknown_type $provider
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function contactsAction(Request $request, $provider){
    	$contacts = $this->getAzineHybridAuthService()->getProvider($provider)->getUserContacts();
    	return new JsonResponse(array('contacts' => $contacts));
    }

    /**
     * Get all contacts from Xing and LinkedIn
     * @param Request $request
     * @param integer $pageSize
     * @param integer $offset
     */
    public function mergedContactsAction(Request $request, $pageSize, $offset){
    	$contacts = $this->getBusinessNetworkProviderService()->getContactProfiles($pageSize, $offset);
    	return new JsonResponse(array('contacts' => $contacts));
    }

    /**
     * @return AzineMergedBusinessNetworksProvider
     */
    private function getBusinessNetworkProviderService(){
    	return $this->get("azine_business_networks_provider_service");
    }

    /**
     * @return AzineHybridAuth
     */
    private function getAzineHybridAuthService(){
    	return $this->get("azine_hybrid_auth_service");
    }
}
