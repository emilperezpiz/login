<?php

namespace App\Controller\API\v1\Amappzing\Impl;

use App\Controller\API\v1\Amappzing\Interfaces\IProfile;
use App\Utils\ActionUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileImpl extends Controller implements IProfile
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    private function cryptPassword($user): void
    {
        $pass = trim($user->getPassword());
        $password = $this->encoder->encodePassword($user, "$pass");
        $user->setPassword($password);
    }

    public function show($context): array
    {
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $dm = $context->get('doctrine_mongodb')->getManager();
        $setting = $context->get('doctrine_mongodb')
            ->getRepository('App:UserSetting')
            ->findOneBy(array(
                'identificador' => trim($currentUser->getIdentificador())
            ));
        $user['email'] = $currentUser->getUserName();
        $user['identifier'] = $currentUser->getIdentificador();
        $user['name'] = $currentUser->getName();
        $user['surname'] = $currentUser->getSurname();
        $user['business'] = $currentUser->getBusiness();
        $user['path'] = $currentUser->getPath();
        $user['phone'] = $currentUser->getPhone();
        $user['cpf'] = $currentUser->getCpf();

        if ($setting && $setting->getPublicBirthday() && $currentUser->getBirthday()) {
            $user['birthday'] = $currentUser->getBirthday();
        }

        if ($currentUser->getIsMale() === true) {
            $user['gender'] = "M";
        } elseif ($currentUser->getIsFemale() === true) {
            $user['gender'] = "F";
        }

        if (count($currentUser->getUserRoles()) > 0) {
            $user['role'] = $currentUser->getUserRoles()[0]->getRole();
        }

        if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        /*$url = $protocol . $_SERVER['HTTP_HOST'] . '/_amappzing/arya/public/';*/
        $url = $protocol . $_SERVER['HTTP_HOST'] . '/arya/public/';

        if ($user) {
            if ($user['path'] != "") {
                $user['path'] = $url . $user['path'];
            }
        }

        return $user;
    }

    public function save($context, $parameters): array
    {
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $actionUtil = new ActionUtil();
        $currentUser->setName($parameters['name']);
        $currentUser->setSurName($parameters['surname']);
        $currentUser->setPhone($parameters['phone']);
        
        if (isset($parameters['password'])) {
            $currentUser->setPassword($parameters['password']);
            $this->cryptPassword($currentUser);
        }

        $package = new Package(new EmptyVersionStrategy());
        $actionUtil->crearCarpeta($package->getUrl("files/img/trabajadores/" . $currentUser->getUsername() . "/profile"));
        
        if ($parameters['img']) {
            $x = (int) $parameters['x'];
            $y = (int) $parameters['y'];
            $width = (int) $parameters['width'];
            $height = (int) $parameters['height'];
            $root = 'files/img/trabajadores';
        
            if (file_exists($currentUser->getPath())) {
                unlink($currentUser->getPath());
            }
        
            $actionUtil->uploadImage($parameters, $currentUser, $root, $x, $y, $width, $height);
        }
        
        $em = $context->getDoctrine()->getManager();
        $em->persist($currentUser);
        $em->flush();
        $user['email'] = $currentUser->getUserName();
        $user['identifier'] = $currentUser->getIdentificador();
        $user['name'] = $currentUser->getName();
        $user['surname'] = $currentUser->getSurname();
        $user['business'] = $currentUser->getBusiness();
        $user['path'] = $currentUser->getPath();
        $user['phone'] = $currentUser->getPhone();
        $user['cpf'] = $currentUser->getCpf();
        
        if ($currentUser->getIsMale() === true) {
            $user['gender'] = "M";
        } elseif ($currentUser->getIsFemale() === true) {
            $user['gender'] = "F";
        }
        
        if (count($currentUser->getUserRoles()) > 0) {
            $user['role'] = $currentUser->getUserRoles()[0]->getRole();
        }
        
        if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        
        $url = $protocol . $_SERVER['HTTP_HOST'] . '/_amappzing/arya/public/';

        if ($user) {
            if ($user['path'] != "") {
                $user['path'] = $url . $user['path'];
            }
        }
        
        return $user;
    }
}
