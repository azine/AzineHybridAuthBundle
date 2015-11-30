<?php

namespace Azine\HybridAuthBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Azine\HybridAuthBundle\Services\AzineHybridAuth;

use Azine\HybridAuthBundle\Services\AzineMergedBusinessNetworksProvider;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AzineHybridAuthJsonController extends Controller {

    /**
     * Check if the user is connected to the requested provider.
     * @param Request $request
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function isConnectedAction(Request $request, $provider){
    	try {
	    	$connected = $this->getAzineHybridAuthService()->isConnected($request, $provider);
	    	return new JsonResponse(array('connected' => $connected));
    	} catch (\Exception $e) {
    		return new JsonResponse(array('connected' => false, 'message' => $e->getMessage()."\n\n\n".$e->getTraceAsString()));
    	}    	
    }

	/**
	 * Try to connect to the provider
	 * @param Request $request
	 * @param string $provider
	 * @param null $callbackRoute
	 * @return RedirectResponse
	 * @throws \Exception e.g. if the api-connection is invalid
	 */
    public function connectUserAction(Request $request, $provider, $callbackRoute = null){
		$deleteSessionData = $request->query->get('force', false);
		$cookieName = $this->getAzineHybridAuthService()->getCookieName($provider);
		if($deleteSessionData){
			$this->getAzineHybridAuthService()->deleteSession($provider);
		}
		try {
	    	$hybridAuth = $this->getAzineHybridAuthService()->getInstance($request->cookies->get($cookieName), $provider);
	    	$connected = $hybridAuth->isConnectedWith($provider);
    	} catch (\Exception $e) {
			$response = new RedirectResponse($this->generateUrl($callbackRoute));
			if($deleteSessionData){
				$response->headers->clearCookie($cookieName, '/', $request->getHost(), $request->isSecure(), true);
			}
			return $response;
    	}    	

    	if(!$connected || $deleteSessionData){
    		try {
    			$adapter = $hybridAuth->getAdapter($provider);
				setcookie($cookieName, null, -1, '/', $request->getHost(), $request->isSecure(), true);
				$adapter->login();
    		} catch (\Exception $e) {
    			throw new \Exception("Unable to create adapter for provider '$provider'. Is it configured properly?", $e->getCode(), $e);
    		}
    	}

		$params = $request->query->all();
		$callbackUrl = $this->generateUrl($callbackRoute, $params);
		if(!$callbackUrl){
			throw new \Exception("Callback route not defined");
		}
		$response = new RedirectResponse($callbackUrl);
		if($deleteSessionData){
			$response->headers->clearCookie($cookieName, '/', $request->getHost(), $request->isSecure(), true);
		}
		return $response;

	}


    /**
     * Get the users Profile for the requested provider
     * @param Request $request
     * @param string $provider
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @throws \Exception e.g. if the api-connection is invalid
     */
    public function profileAction(Request $request, $provider, $userId = null){

    	if($userId == null){
			$cookieName = $this->getAzineHybridAuthService()->getCookieName($provider);
	    	$profile = $this->getAzineHybridAuthService()->getProvider($request->cookies->get($cookieName), $provider)->getUserProfile();
    	} else {
    		$profile = $this->getBusinessNetworkProviderService()->getUserContactBasicProfile($provider, $userId);
    	}
    	return new JsonResponse(array('profile' => $profile));
    }

	/**
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @throws \Exception e.g. if the api-connection is invalid
	 */
	public function profileByUrlAction(Request $request){
		$profileUrl = $request->get("searchByUrl");
		$profile = $this->getBusinessNetworkProviderService()->getUserProfileByUrl($profileUrl);
		return new JsonResponse(array('profile' => $profile));
	}


	/**
     * Get all users contacts for the requested provider
     * @param Request $request
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @throws \Exception e.g. if the api-connection is invalid
     */
    public function contactsAction(Request $request, $provider){
		$cookieName = $this->getAzineHybridAuthService()->getCookieName($provider);
    	$contacts = $this->getAzineHybridAuthService()->getProvider($request->cookies->get($cookieName), $provider)->getUserContacts();
    	return new JsonResponse(array('contacts' => $contacts));
    }

	/**
     * Get all contacts from Xing and LinkedIn
     * @param Request $request
     * @param integer $pageSize
     * @param integer $offset
     * @return JsonResponse
	 * @throws \Exception e.g. if the api-connection is invalid
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
