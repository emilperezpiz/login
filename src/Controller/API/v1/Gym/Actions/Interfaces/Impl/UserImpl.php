<?php

namespace App\Controller\API\v1\Gym\Actions\Interfaces\Impl;

use App\Controller\API\v1\Gym\Actions\Interfaces\IUser;
use App\Entity\User;
use App\Utils\ActionUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserImpl extends Controller implements IUser
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    private function cryptPassword($user, $automaticPassword = true): void
    {
        if ($automaticPassword) {
            $pass = trim($user->getCpf());
        } else {
            $pass = trim($user->getPassword());
        }

        $password = $this->encoder->encodePassword($user, "$pass");
        $user->setPassword($password);
    }

    public function list($context, string $identificador): array
    {
        $em = $context->getDoctrine()->getManager();
        $query = $em->createQuery("Select o.identificador as identifier, o.name, 
                                      o.surname, o.userName as username, 
                                      ur.role, o.business, o.path 
                                      From App:User o 
                                      JOIN o.userRoles ur 
                                      WHERE (ur.role = :permiso_one
                                      OR ur.role = :permiso_two) 
                                      AND o.isActive = :activo
                                      AND o.business = :business
                                      ORDER BY o.updatedAt ASC");
        $query->setParameter('permiso_one', "ROLE_WORKER_TEACHER");
        $query->setParameter('permiso_two', "ROLE_WORKER_SECRETARY");
        $query->setParameter('activo', true);
        $query->setParameter('business', $identificador);
        $users = $query->getResult();

        if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        
        $url = $protocol . $_SERVER['HTTP_HOST'] . '/arya/public/';
        $arrayUsers = array();

        foreach ($users as $user) {
            if ($user['path'] != "") {
                $user['path'] = $url . $user['path'];
            }

            $arrayUsers[] = $user;
        }

        return $arrayUsers;
    }

    public function new($context, string $identificador, array $parameters): array
    {
        $em = $context->getDoctrine()->getManager();
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $actionUtil = new ActionUtil();
        $user = new User();
        $user->setIsActive(true);
        $user->setBusiness($identificador);
        $user->setName($parameters['name']);

        if ($parameters['gender'] === "M") {
            $user->setIsFemale(false);
            $user->setIsMale(true);
        } else {
            $user->setIsFemale(true);
            $user->setIsMale(false);
        }

        $user->setSurName($parameters['surname']);
        $user->setUserName($parameters['email']);
        $user->setCpf($parameters['cpf']);
        $user->setPhone($parameters['phone']);
        $role = $em->getRepository("App:Rol")->findOneBy(array(
            "role" => trim($parameters['role'])
        ));

        if (!$role) {
            throw $context->createNotFoundException('Role not found');
        }

        $user->setUserRoles($role);

        if ($currentUser->hasRole('ROLE_PROPERTY')) {
            if ($parameters['password']) {
                $user->setPassword($parameters['password']);
                $this->cryptPassword($user, false);
            }
        } else {
            $this->cryptPassword($user);
        }

        $package = new Package(new EmptyVersionStrategy());
        $actionUtil->crearCarpeta($package->getUrl("files/img/trabajadores/" . $user->getUsername() . "/profile"));
        $x = (int) $parameters['x'];
        $y = (int) $parameters['y'];
        $width = (int) $parameters['width'];
        $height = (int) $parameters['height'];
        $root = 'files/img/trabajadores';
        $actionUtil->uploadImage($parameters, $user, $root, $x, $y, $width, $height);
        $em->persist($user);
        $em->flush();

        return $parameters;
    }

    public function show($context, string $identificador, string $currentId): array
    {
        $em = $context->getDoctrine()->getManager();
        $query = $em->createQuery("Select o.identificador as identifier, o.name, o.surname,
                                      o.userName as email, ur.role, o.business, o.path,
                                      o.phone, o.cpf, o.isActive,
                                      (CASE WHEN o.isMale = :activo THEN 'M'
                                      WHEN o.isFemale = :activo THEN 'F' ELSE '' END) as gender 
                                      From App:User o 
                                      JOIN o.userRoles ur 
                                      WHERE (ur.role = :permiso_one
                                      OR ur.role = :permiso_two)
                                      AND o.isLocked = :desactivado
                                      AND o.identificador = :id 
                                      AND o.isActive = :activo
                                      AND o.business = :business 
                                      ORDER BY o.updatedAt DESC");
        $query->setParameter('permiso_one', "ROLE_WORKER_TEACHER");
        $query->setParameter('permiso_two', "ROLE_WORKER_SECRETARY");
        $query->setParameter('activo', true);
        $query->setParameter('desactivado', false);
        $query->setParameter('business', $identificador);
        $query->setParameter('id', $currentId);
        $user = $query->getSingleResult();

        if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        
        $url = $protocol . $_SERVER['HTTP_HOST'] . '/arya/public/';

        if ($user) {
            if ($user['path'] != "") {
                $user['path'] = $url . $user['path'];
            }
        }

        return $user;
    }

    public function edit($context, string $identificador, string $currentId, array $parameters): array
    {
        $em = $context->getDoctrine()->getManager();
        $currentUser = $context->container->get('security.token_storage')->getToken()->getUser();
        $actionUtil = new ActionUtil();
        $user = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => $currentId,
            "business" => $identificador
        ));
        $user->setIsActive(true);
        $user->setBusiness($identificador);
        $user->setName($parameters['name']);

        if ($parameters['gender'] === "M") {
            $user->setIsFemale(false);
            $user->setIsMale(true);
        } else {
            $user->setIsFemale(true);
            $user->setIsMale(false);
        }

        $user->setSurName($parameters['surname']);
        $user->setUserName($parameters['email']);
        $user->setCpf($parameters['cpf']);
        $user->setPhone($parameters['phone']);
        $role = $em->getRepository("App:Rol")->findOneBy(array(
            "role" => trim($parameters['role'])
        ));

        if (!$role) {
            throw $context->createNotFoundException('Role not found');
        }

        $user->setUserRoles($role);

        if ($currentUser->hasRole('ROLE_PROPERTY')) {
            if ($parameters['password']) {
                $user->setPassword($parameters['password']);
                $this->cryptPassword($user, false);
            }
        }

        $package = new Package(new EmptyVersionStrategy());
        $actionUtil->crearCarpeta($package->getUrl("files/img/trabajadores/" . $user->getUsername() . "/profile"));
        if ($parameters['img']) {
            $x = (int) $parameters['x'];
            $y = (int) $parameters['y'];
            $width = (int) $parameters['width'];
            $height = (int) $parameters['height'];
            $root = 'files/img/trabajadores';
            if (file_exists($user->getPath())) {
                unlink($user->getPath());
            }
            $actionUtil->uploadImage($parameters, $user, $root, $x, $y, $width, $height);
        }

        $em->persist($user);
        $em->flush();

        return $parameters;
    }

    public function remove($context, string $identificador, string $currentId): array
    {
        $em = $context->getDoctrine()->getManager();
        $user = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => $currentId,
            "business" => $identificador
        ));
        $parameters['identificador'] = $currentId;
        $parameters['name'] = $user->getName();
        $parameters['surname'] = $user->getSurname();
        $parameters['cpf'] = $user->getCpf();
        $parameters['username'] = $user->getUserName();
        $em->remove($user);
        $em->flush();

        return $parameters;
    }
}
