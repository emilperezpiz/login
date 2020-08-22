<?php

namespace App\Controller\API\v1\Amappzing\Impl;

use App\Controller\API\v1\Amappzing\Interfaces\IUserSetting;
use App\Document\UserSetting;
use App\Utils\ActionUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserSettingImpl extends Controller implements IUserSetting
{

    public function show($context): array
    {
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $dm = $context->get('doctrine_mongodb')->getManager();
        $setting = $context->get('doctrine_mongodb')
            ->getRepository('App:UserSetting')
            ->findOneBy(array(
                'identificador' => trim($currentUser->getIdentificador())
            ));

        if (!$setting) {
            $utils = new ActionUtil();
            $setting = new UserSetting();
            $setting->setIdentificador(trim($currentUser->getIdentificador()));
            $setting->setLanguage($utils->defaultLanguage);
            $setting->setMode($utils->defaultTheme);
            $dm->persist($setting);
            $dm->flush();
        }

        $response['language'] = $setting->getLanguage();
        $response['mode'] = $setting->getMode();
        $response['publicBirthday'] = $setting->getPublicBirthday() ? "S" : "N";

        return $response;
    }

    public function save($context, $parameters): array
    {
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $dm = $context->get('doctrine_mongodb')->getManager();
        $setting = $context->get('doctrine_mongodb')
            ->getRepository('App:UserSetting')
            ->findOneBy(array(
                'identificador' => trim($currentUser->getIdentificador())
            ));

        if (isset($parameters['language'])) {
            $setting->setLanguage($parameters['language']);
        }

        if (isset($parameters['mode'])) {
            $setting->setMode($parameters['mode']);
        }

        if (isset($parameters['publicBirthday'])) {
            $publicBirthday = $parameters['publicBirthday'] === 'S' ? true : false;
            $setting->setPublicBirthday($publicBirthday);
        }

        if (isset($parameters['automaticPayment'])) {
            $automaticPayment = $parameters['automaticPayment'] === 'S' ? true : false;
            $setting->setAutomaticPayment($automaticPayment);
        }

        if (isset($parameters['paymentDay'])) {
            $setting->setPaymentDay($parameters['paymentDay']);
        }

        $dm->persist($setting);
        $dm->flush();
        $response['language'] = $setting->getLanguage();
        $response['mode'] = $setting->getMode();
        $response['publicBirthday'] = $setting->getPublicBirthday() ? "S" : "N";

        return $response;
    }
}
