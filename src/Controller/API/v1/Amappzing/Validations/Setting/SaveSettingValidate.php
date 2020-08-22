<?php

namespace App\Controller\API\v1\Amappzing\Validations\Setting;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SaveSettingValidate extends Controller
{
    public static function requestIsCorrect($parameters, $context): array
    {
        $parameter = SaveSettingValidate::parameter($parameters);
        if ($parameter) {
            return $parameter;
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

        return null;
    }
}
