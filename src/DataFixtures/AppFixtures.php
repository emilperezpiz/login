<?php

namespace App\DataFixtures;

use App\Entity\Rol;
use App\Entity\User;
use App\Document\BussinesClasification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        /*$bussinesClasification = new BussinesClasification();
        $bussinesClasification->setName("Loja");
        $bussinesClasification->setIdentificador("100");
        // lo guarda en BD
        $manager->persist($bussinesClasification);
        $manager->flush();*/

        /*$bussinesClasification = new App\Document\BussinesClasification();
        $bussinesClasification->setName("Restaurant");
        $bussinesClasification->setIdentificador("200");
        // lo guarda en BD
        $manager->persist($bussinesClasification);
        $manager->flush();

        $bussinesClasification = new App\Document\BussinesClasification();
        $bussinesClasification->setName("Barber Shop");
        $bussinesClasification->setIdentificador("300");
        // lo guarda en BD
        $manager->persist($bussinesClasification);
        $manager->flush();

        $bussinesClasification = new App\Document\BussinesClasification();
        $bussinesClasification->setName("Dealer");
        $bussinesClasification->setIdentificador("400");
        // lo guarda en BD
        $manager->persist($bussinesClasification);
        $manager->flush();*/

        $role = new Rol();
        $role->setRole("ROLE_ADMIN");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // crea un usuario
        $user = new User();
        $user->setUserName('admin');
        // codifica el password
        $password = $this->encoder->encodePassword($user, 'administrator');
        $user->setPassword($password);
        $user->setUserRoles($role);
        $user->setName("Emil");
        $user->setSurName("Perez Piz");
        $user->setAddress("Sao Paulo");
        $user->setIsActive(true);
        // lo guarda en BD
        $manager->persist($user);
        $manager->flush();

        // se crean los roles

        // para manejar los usuarios admin
        $role = new Rol();
        $role->setRole("ROLE_ADMIN_LIST");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_ADMIN_CREATE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_ADMIN_SHOW");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_ADMIN_EDIT");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_ADMIN_DELETE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // para manejar los usuarios admin con permisos sobre los propietarios
        $role = new Rol();
        $role->setRole("ROLE_USER_PROPERTY_LIST");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_USER_PROPERTY_CREATE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_USER_PROPERTY_SHOW");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_USER_PROPERTY_EDIT");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_USER_PROPERTY_DELETE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // para manejar las localizaciones
        $role = new Rol();
        $role->setRole("ROLE_LOCATION_LIST");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_LOCATION_CREATE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_LOCATION_SHOW");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_LOCATION_EDIT");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_LOCATION_DELETE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        //permiso de adminitracion de negocios
        // para manejar las localizaciones
        $role = new Rol();
        $role->setRole("ROLE_LOCATION_LIST");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_BUSINESS_LIST");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_BUSINESS_CREATE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_BUSINESS_SHOW");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_BUSINESS_EDIT");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_BUSINESS_DELETE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        //permiso de adminitracion de negocios
        // para manejar las localizaciones
        $role = new Rol();
        $role->setRole("ROLE_LOCATION_LIST");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_BUSINESS_LIST");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_PRODUCT_CREATE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_PRODUCT_SHOW");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_PRODUCT_EDIT");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        $role = new Rol();
        $role->setRole("ROLE_PRODUCT_DELETE");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // permiso de propietario
        $role = new Rol();
        $role->setRole("ROLE_PROPERTY");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // permiso de cliente gym
        $role = new Rol();
        $role->setRole("ROLE_GYM_CLIENT");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // permiso de 
        $role = new Rol();
        $role->setRole("ROLE_WORKER_ADMIN");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // permiso de 
        $role = new Rol();
        $role->setRole("ROLE_WORKER_ACCOUNTANT");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // permiso de 
        $role = new Rol();
        $role->setRole("ROLE_WORKER_KITCHEN");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // permiso de 
        $role = new Rol();
        $role->setRole("ROLE_WORKER_DEPENDENT");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // permiso de 
        $role = new Rol();
        $role->setRole("ROLE_WORKER_TEACHER");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

        // permiso de 
        $role = new Rol();
        $role->setRole("ROLE_WORKER_SECRETARY");
        // lo guarda en BD
        $manager->persist($role);
        $manager->flush();

    }
}
