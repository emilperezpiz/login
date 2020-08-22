<?php

namespace App\Controller\API\v1\Gym\Validations\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RemoveUserValidate extends Controller
{
    public static function requestIsCorrect($currentId, $context, $identificador): array
    {
        $issetIdentifier = RemoveUserValidate::issetIdentifier($context, $identificador, $currentId);

        if ($issetIdentifier) {
            return $issetIdentifier;
        }

        RemoveUserValidate::unauthorized($context, $identificador, $currentId);

        return array(
            'message' => 'ok',
            'status' => 200
        );
    }

    private static function issetIdentifier($context, $identificador, $currentId): ?array
    {
        $em = $context->getDoctrine()->getManager();
        $user = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => trim($currentId)
        ));

        if (!$user) {
            return array(
                'message' => 'userNotExist',
                'status' => 400
            );
        }

        return null;
    }

    private static function unauthorized($context, $identificador, $currentId): ?array
    {
        $em = $context->getDoctrine()->getManager();
        $user = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => trim($currentId)
        ));

        if (count($user->getUserRoles()) === 0) {
            return array(
                'message' => 'unauthorized',
                'status' => 400
            );
        }
        
        if ($user->getUserRoles()[0]->getRole() !== "ROLE_WORKER_TEACHER" && $user->getUserRoles()[0]->getRole() !== "ROLE_PROPERTY") {
            return array(
                'message' => 'unauthorized',
                'status' => 400
            );
        }

        return null;
    }
}
