<?php

namespace App\Controller\API\v1\Gym\Validations\BusinessSetting;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EditBusinessSettingValidate extends Controller
{

    public static function requestIsCorrect($parameters, $context): array
    {
        $parameter = EditBusinessSettingValidate::parameter($parameters);
        $isEmail = EditBusinessSettingValidate::isEmail($parameters['email']);
        $issetEmail = EditBusinessSettingValidate::issetEmail($context, $parameters['email']);

        if ($parameter) {
            return $parameter;
        }

        if ($isEmail) {
            return $isEmail;
        }

        if ($issetEmail) {
            return $issetEmail;
        }

        /*$isValidBirthday = EditUserValidate::isValidBirthday($parameters['birthday']);
        if ($isValidBirthday) {
            return $isValidBirthday;
        }*/
        return array(
            'message' => 'ok',
            'status' => 200
        );
    }

    private static function isValidBirthday($birthday): ?array
    {
        if (strtotime($birthday) === false) {
            return array(
                'message' => 'formatBirthdayIncorrect',
                'status' => 400
            );
        }

        return null;
    }

    private static function issetEmail($context, $email): ?array
    {
        $em = $context->getDoctrine()->getManager();
        /*$user = $em->getRepository("App:User")->findOneBy(array(
            "userName" => trim($email)
        ));

        if ($user && $user->getIdentificador() !== $currentId) {
            return array(
                'message' => 'emailUsed',
                'status' => 400
            );
        }*/

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

        if (!isset($parameters['birthday'])) {
            return array(
                'message' => 'birthdayIsRequired',
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

        if (!isset($parameters['phone'])) {
            return array(
                'message' => 'phoneIsRequired',
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

        return null;
    }
}
