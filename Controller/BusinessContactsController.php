<?php

namespace Azine\HybridAuthBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BusinessContactsController extends Controller {

    public function mergedContactsAction(Request $request, $pageSize, $offset){
     	$contacts = $this->get("azine_business_networks_provider_service")->getContactProfiles($pageSize, $offset);
    	return new JsonResponse($contacts);
    }


}
