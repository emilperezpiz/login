<?php

namespace App\Controller\API\v1\Gym\Validations\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ShowUserValidate extends Controller
{

    public static function requestIsCorrect($currentId, $context): array
    {
        $issetIdentifier = ShowUserValidate::issetIdentifier($context, $currentId);

        if ($issetIdentifier) {
            return $issetIdentifier;
        }

        $isLocked = ShowUserValidate::isLocked($context, $currentId);

        if ($isLocked) {
            return $isLocked;
        }

        ShowUserValidate::unauthorized($context, $currentId);

        return array(
            'message' => 'ok',
            'status' => 200
        );
    }

    private static function issetIdentifier($context, $currentId): ?array
    {
        $em = $context->getDoctrine()->getManager();
        $isset = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => trim($currentId)
        ));

        if (!$isset) {
            return array(
                'message' => 'userNotFound',
                'status' => 400
            );
        }

        return null;
    }

    private static function isLocked($context, $currentId): ?array
    {
        $em = $context->getDoctrine()->getManager();
        $user = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => trim($currentId)
        ));
        $isLocked = $user->getIsLocked();

        if ($isLocked) {
            return array(
                'message' => 'userLocked',
                'status' => 400
            );
        }

        return null;
    }

    private static function unauthorized($context, $currentId): ?array
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
