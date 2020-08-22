<?php

namespace App\Controller\API\v1\Amappzing\Validations\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SaveProfileValidate extends Controller
{

    public static function requestIsCorrect($parameters, $context): array
    {
        $parameter = SaveProfileValidate::parameter($parameters);
        if ($parameter) {
            return $parameter;
        }
        $unauthorized = SaveProfileValidate::unauthorized($parameters['currentPassword'], $context);
        if ($unauthorized) {
            return $unauthorized;
        }
        return array(
            'message' => 'ok',
            'status' => 200
        );
    }

    private static function parameter($parameters): ?array
    {
        if (count($parameters) === 0) {
            return array(
                'message' => 'noParameters',
                'status' => 400
            );
        }

        if (!isset($parameters['name'])) {
            return array(
                'message' => 'nameIsRequired',
                'status' => 400
            );
        }

        if (!isset($parameters['surname'])) {
            return array(
                'message' => 'surnameIsRequired',
                'status' => 400
            );
        }

        if (!isset($parameters['phone'])) {
            return array(
                'message' => 'phoneIsRequired',
                'status' => 400
            );
        }

        if (!isset($parameters['currentPassword'])) {
            return array(
                'message' => 'currentPasswordIsRequired',
                'status' => 400
            );
        }

        if (isset($parameters['password']) && !isset($parameters['passwordConfirm'])) {
            return array(
                'message' => 'passwordConfirmIsRequired',
                'status' => 400
            );
        }

        if (!isset($parameters['password']) && isset($parameters['passwordConfirm'])) {
            return array(
                'message' => 'passwordIsRequired',
                'status' => 400
            );
        }

        if ((isset($parameters['password']) && isset($parameters['passwordConfirm'])) && ($parameters['password'] !== $parameters['passwordConfirm'])) {
            return array(
                'message' => 'passwordNotEqualpasswordConfirm',
                'status' => 400
            );
        }

        return null;
    }

    private static function unauthorized($password, $context): ?array
    {
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $accept = $context->encoder->isPasswordValid($currentUser, trim($password));
        if (!$accept) {
            return array(
                'message' => 'passwordInvalid',
                'status' => 400
            );
        }
        return null;
    }
}
