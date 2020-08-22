<?php

namespace App\Controller\API\v1\Gym\Validations\User;

use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CommonUserValidate extends Controller
{

    public static function _isGranted($context, $identificador): bool
    {
        $context->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!');
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $business = new stdClass();
        $business->clasificationIdentifier = 300;
        $business->identifier = "NBL";

        if (!$business) {
            throw $context->createNotFoundException('404');
        }

        if ($currentUser->hasRole('ROLE_PROPERTY')) {
            
        } else {
            if (!$currentUser->hasRole('ROLE_WORKER_TEACHER') && !$currentUser->hasRole('ROLE_WORKER_SECRETARY')) {
                //throw $context->createAccessDeniedException('403');
            }
            
            if ($business->clasificationIdentifier !== 300 || $currentUser->getBusiness() !== $business->identifier) {
                throw $context->createAccessDeniedException('403');
            }
        }

        return true;
    }
}
