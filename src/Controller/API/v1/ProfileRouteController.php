<?php

namespace App\Controller\API\v1;

use App\Controller\API\v1\Amappzing\Impl\ProfileImpl;
use App\Controller\API\v1\Amappzing\Impl\UserSettingImpl;
use App\Controller\API\v1\Amappzing\Validations\CommonValidate;
use App\Controller\API\v1\Amappzing\Validations\Profile\SaveProfileValidate;
use App\Controller\API\v1\Amappzing\Validations\Setting\SaveSettingValidate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api/v1/gym/admin/profile")
 */
class ProfileRouteController extends Controller
{
    // codigo del acceso de los clientes
    public $encoder;
    private $codes = ["GWE&A"];
    public $serviceProfile;
    public $serviceUserSetting;

    public function __construct(ProfileImpl $_serviceProfile, UserSettingImpl $_serviceUserSetting, UserPasswordEncoderInterface $encoder)
    {
        $this->serviceProfile = $_serviceProfile;
        $this->serviceUserSetting = $_serviceUserSetting;
        $this->encoder = $encoder;
    }

    /**
     * @Route("/get", name="get_profile", methods="GET")
     */
    public function getProfile(Request $request)
    {
        $commonValidate = new CommonValidate($request, $this->codes);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $response = $this->serviceProfile->show($this);

        return $this->json($response);
    }

    /**
     * @Route("/save", name="save_profile", methods="POST")
     */
    public function saveProfile(Request $request)
    {
        $commonValidate = new CommonValidate($request, $this->codes);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $parameters = $request->request->all();
        $parameters['img'] = null;

        foreach ($request->files as $file) {
            $parameters['img'] = $file;
        }

        $saveProfileValidate = SaveProfileValidate::requestIsCorrect($parameters, $this);

        if ($saveProfileValidate['status'] !== 200) {
            return $this->json($saveProfileValidate['message'], $saveProfileValidate['status']);
        }

        $response = $this->serviceProfile->save($this, $parameters);

        return $this->json($response);
    }

    /**
     * @Route("/setting", name="get_setting", methods="GET")
     */
    public function getSetting(Request $request)
    {
        $commonValidate = new CommonValidate($request, $this->codes);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $response = $this->serviceUserSetting->show($this);

        return $this->json($response);
    }

    /**
     * @Route("/saveSetting", name="save_setting", methods="POST")
     */
    public function saveSetting(Request $request)
    {
        $commonValidate = new CommonValidate($request, $this->codes);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $parameters = json_decode($request->getContent(), true);
        $saveSettingValidate = SaveSettingValidate::requestIsCorrect($parameters, $this);

        if ($saveSettingValidate['status'] !== 200) {
            return $this->json($saveSettingValidate['message'], $saveSettingValidate['status']);
        }

        $response = $this->serviceUserSetting->save($this, $parameters);

        return $this->json($response);
    }
}
