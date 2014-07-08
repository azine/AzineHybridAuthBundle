<?php

namespace Azine\HybridAuthBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LinkedinController extends Controller
{

	public function profileAction(Request $request){
		$linkedin = $this->getLinkedinAPI();
		$profile = $linkedin->getUserProfile();
    	return new JsonResponse($profile);
	}

    public function contactsAction(Request $request){
		$linkedin = $this->getLinkedinAPI();
		$contacts = $linkedin->getUserContacts();
    	return new JsonResponse($contacts);
    }


    /**
     * @return \Hybrid_Providers_LinkedIn
     */
    private function getLinkedinAPI(){
    	return $this->get("azine_hybrid_auth_service")->getLinkedIn();
    }
}
