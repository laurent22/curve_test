<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class ContributorDistancesController extends Controller
{

    /**
     * @Rest\View
     */
    public function getContributor_distancesAction($relationId)
    {
        $u = explode('_', $relationId);
        if (count($u) != 2) throw new \Exception('Relation ID must be in the form <user1>_<user2>');

        $contributorRelationService = $this->get('app.contributor_relation_service');

        $d = $contributorRelationService->getShortestDistance($u[0], $u[1]);

        $response = new Response(json_encode(array('distance' => $d)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
