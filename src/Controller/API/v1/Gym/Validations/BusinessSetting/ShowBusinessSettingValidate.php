<?php

namespace App\Controller\API\v1\Gym\Validations\BusinessSetting;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ShowBusinessSettingValidate extends Controller
{

    public static function requestIsCorrect($context): array
    {
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $isLocked = ShowBusinessSettingValidate::isLocked($currentUser);

        if ($isLocked) {
            return $isLocked;
        }

        ShowBusinessSettingValidate::unauthorized($currentUser);

        return array(
            'message' => 'ok',
            'status' => 200
        );
    }

    private static function isLocked($currentUser): ?array
    {
        $isLocked = $currentUser->getIsLocked();

        if ($isLocked) {
            return array(
                'message' => 'userLocked',
                'status' => 400
            );
        }

        return null;
    }

    private static function unauthorized($currentUser): ?array
    {
        if (count($currentUser->getUserRoles()) === 0) {
            return array(
                'message' => 'unauthorized',
                'status' => 400
            );
        }

        if ($currentUser->getUserRoles()[0]->getRole() !== "ROLE_PROPERTY") {
            return array(
                'message' => 'unauthorized',
                'status' => 400
            );
        }

        return null;
    }
}
