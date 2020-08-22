<?php

namespace App\Controller\API\v1\Gym\Validations\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EditUserValidate extends Controller
{

    public static function requestIsCorrect($parameters, $context, $currentId): array
    {
        $parameter = EditUserValidate::parameter($parameters);
        $isEmail = EditUserValidate::isEmail($parameters['email']);
        $issetEmail = EditUserValidate::issetEmail($context, $parameters['email'], $currentId);
        $issetCpf = EditUserValidate::issetCpf($context, $parameters['cpf'], $currentId);

        if ($parameter) {
            return $parameter;
        }

        if ($isEmail) {
            return $isEmail;
        }

        if ($issetEmail) {
            return $issetEmail;
        }

        if ($issetCpf) {
            return $issetCpf;
        }
        
        return array(
            'message' => 'ok',
            'status' => 200
        );
    }

    private static function issetEmail($context, $email, $currentId): ?array
    {
        $em = $context->getDoctrine()->getManager();
        $user = $em->getRepository("App:User")->findOneBy(array(
            "userName" => trim($email)
        ));

        if ($user && $user->getIdentificador() !== $currentId) {
            return array(
                'message' => 'emailUsed',
                'status' => 400
            );
        }

        return null;
    }

    private static function issetCpf($context, $cpf, $currentId): ?array
    {
        $em = $context->getDoctrine()->getManager();
        $user = $em->getRepository("App:User")->findOneBy(array(
            "cpf" => trim($cpf)
        ));

        if ($user && $user->getIdentificador() !== $currentId) {
            return array(
                'message' => 'cpfUsed',
                'status' => 400
            );
        }

        return null;
    }

    private static function isEmail($email): ?array
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return array(
                'message' => 'formatEmailIncorrect',
                'status' => 400
            );
        }

        return null;
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

        if (!isset($parameters['cpf'])) {
            return array(
                'message' => 'cpfIsRequired',
                'status' => 400
            );
        }

        if (!isset($parameters['surname'])) {
            return array(
                'message' => 'surnameIsRequired',
                'status' => 400
            );
        }

        if (!isset($parameters['gender'])) {
            return array(
                'message' => 'genderIsRequired',
                'status' => 400
            );
        }

        if ($parameters['gender'] !== 'F' && $parameters['gender'] !== 'M') {
            return array(
                'message' => 'unauthorizedGender',
                'status' => 400
            );
        }

        if (!isset($parameters['email'])) {
            return array(
                'message' => 'emailIsRequired',
                'status' => 400
            );
        }

        if (!isset($parameters['role'])) {
            return array(
                'message' => 'roleIsRequired',
                'status' => 400
            );
        }

        if ($parameters['role'] !== 'ROLE_WORKER_TEACHER' && $parameters['role'] !== 'ROLE_WORKER_SECRETARY') {
            return array(
                'message' => 'unauthorizedRole',
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
}
