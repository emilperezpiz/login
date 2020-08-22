<?php

namespace App\Controller\API\v1\Gym\Validations\BusinessSetting;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CommonBusinessSettingValidate extends Controller
{

    public static function _isGranted($context, $identificador): bool
    {
        $context->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!');
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $business = $context->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findOneBy(array(
                'identificador' => $identificador
            ));

        if (!$business) {
            throw $context->createNotFoundException('404');
        }

        if ($currentUser->hasRole('ROLE_PROPERTY')) {
            if ($currentUser->getIdentificador() !== $business->getUser() && $business->getIdentificador() !== $currentUser->getBusiness()) {
                throw $context->createAccessDeniedException('403');
            }
        } else {
            throw $context->createAccessDeniedException('403');
        }

        return true;
    }
}
