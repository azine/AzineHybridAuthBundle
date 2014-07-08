<?php

namespace Azine\HybridAuthBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class XingController extends Controller {


	public function profileAction(Request $request){
		$xing = $this->getXingAPI();
		$profile = $xing->getUserProfile();
		return new JsonResponse($profile);
	}

    public function contactsAction(Request $request){
		$xing = $this->getXingAPI();
		$contacts = $xing->getUserContacts();
    	return new JsonResponse($contacts);
    }

    /**
     * @return \Hybrid_Providers_XING
     */
    private function getXingAPI(){
    	return $this->get("azine_hybrid_auth_service")->getXing();
    }
}
