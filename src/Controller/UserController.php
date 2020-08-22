<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Filesystem\Filesystem;
use Intervention\Image\ImageManager;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * @Route({
 *
 *     "pt": "/admin/administradores",
 *     "es": "/admin/administradores",
 *     "en": "/admin/administradores",
 * })
 */
class UserController extends Controller
{

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    //encripta la contrasenna
    private function cryptPassword($user)
    {
        $pass = trim($user->getPassword());
        $password = $this->encoder->encodePassword($user, "$pass");
        $user->setPassword($password);
    }

    /**
     * @Route("/", name="user_index", methods="GET")
     */
    public function index(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN_LIST', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $search = $request->query->get('search', null);
        $consulta = $em->createQuery("Select o From App:User o 
                                      JOIN o.userRoles ur 
                                      WHERE ur.role = :permiso 
                                      ORDER BY o.updatedAt DESC");

        // si se esta realizando una busqueda
        if ($search) {
            $consulta = $em->createQuery("Select o From App:User o
                                      JOIN o.userRoles ur  
                                      WHERE ur.role = :permiso 
                                      AND (
                                      o.name LIKE '%$search%'
                                      OR o.surname LIKE '%$search%'
                                      OR o.userName LIKE '%$search%'
                                      )   
                                      ORDER BY o.updatedAt ASC");
        }

        $consulta->setParameter('permiso', "ROLE_ADMIN_LIST");
        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        // Paginate the results of the query
        $usuarios = $paginator->paginate(
            // Doctrine Query, not results
            $consulta->getResult(),
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('backend/user/index.html.twig', [
            'users' => $usuarios,
            'route' => $request->getRequestUri(),
            'search' => $search,
            'page' => $request->query->getInt('page', null),
        ]);
    }

    /**
     * @Route({
     *
     *     "pt": "/nuevo/",
     *     "es": "/nuevo/",
     *     "en": "/new/",
     * }, name="user_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN_CREATE', null, 'Unable to access this page!');
        $user = new User();
        $user->setIsActive(true);
        $form = $this->createForm(UserType::class, $user, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'user')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $genero = $request->request->get('radio2');
            $permisosAdmin = $request->request->get('checkboxes1', null);
            // asigna los permisos sobre los administradores
            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => 'ROLE_ADMIN_LIST'
            ));
            $user->setUserRoles($role);

            if ($permisosAdmin) {

                foreach ($permisosAdmin as $permiso) {
                    $role = $em->getRepository("App:Rol")->findOneBy(array(
                        "role" => $permiso
                    ));

                    if ($role) {
                        $user->setUserRoles($role);
                    }
                }
            }

            $permisosProperty = $request->request->get('checkboxes2', null);
            // asigna los permisos sobre los propietarios
            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => 'ROLE_USER_PROPERTY_LIST'
            ));
            $user->setUserRoles($role);

            if ($permisosProperty) {
                foreach ($permisosProperty as $permiso) {
                    $role = $em->getRepository("App:Rol")->findOneBy(array(
                        "role" => $permiso
                    ));

                    if ($role) {
                        $user->setUserRoles($role);
                    }
                }
            }

            $permisosLocation = $request->request->get('checkboxes3', null);
            // asigna los permisos sobre la localizacion
            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => 'ROLE_LOCATION_LIST'
            ));
            $user->setUserRoles($role);

            if ($permisosLocation) {

                foreach ($permisosLocation as $permiso) {
                    $role = $em->getRepository("App:Rol")->findOneBy(array(
                        "role" => $permiso
                    ));

                    if ($role) {
                        $user->setUserRoles($role);
                    }
                }
            }

            // indica el genero
            if ($genero == 1) {
                $user->setIsFemale(true);
                $user->setIsMale(false);
            } elseif ($genero == 2) {
                $user->setIsMale(true);
                $user->setIsFemale(false);
            }

            // dimensiones de la imagen
            $x = (int) $form->get('x')->getData();
            $y = (int) $form->get('y')->getData();
            $width = (int) $form->get('width')->getData();
            $height = (int) $form->get('height')->getData();
            $rotate = (int) $form->get('rotate')->getData();
            $scaleX = (int) $form->get('scaleX')->getData();
            $scaleY = (int) $form->get('scaleY')->getData();

            //para proximamente obtener la ruta publica del proyecto
            $package = new Package(new EmptyVersionStrategy());
            //si no existe la carpeta donde se guardaran las imagenes la crea
            $this->crearCarpeta($package->getUrl("files/img/usuarios/" . $user->getUsername() . "/profile"));
            $this->subirFoto($user, $user->getUsername() . "/profile", $x, $y, $width, $height);
            // encripta el password
            $this->cryptPassword($user);

            $em->persist($user);
            $em->flush();

            $message = $this->get('translator')->trans(
                'admin.message.success_1',
                array(),
                'administradores'
            );
            $this->get("session")->getFlashBag()->add("success", $message);

            return $this->redirectToRoute('user_index');
        }

        return $this->render('backend/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'route' => $request->getRequestUri(),
        ]);
    }

    /**
     * @Route("/validate", name="user_validate", methods="GET")
     */
    public function validate(Request $request)
    {
        $mail = $request->query->get('email');
        $identificador = $request->query->get('identificador', null);
        $em = $this->getDoctrine()->getManager();
        $existeEmail = $em->getRepository("App:User")->findOneBy(array(
            'userName' => trim($mail)
        ));
        $message = $this->get('translator')->trans(
            'action.validate_1',
            array(),
            'administradores'
        );

        if ($existeEmail) {
            // si existe el identificador es porq es la vista de edicion
            if ($identificador) {
                if ($existeEmail->getIdentificador() == $identificador) {
                    return new JsonResponse([]);
                }
            }

            return new JsonResponse([
                'state' => '200',
                'message' => $message
            ]);
        }

        return new JsonResponse([]);
    }

    /**
     * @Route({
     *
     *     "pt": "/{identificador}/detalles/",
     *     "es": "/{identificador}/detalles/",
     *     "en": "/{identificador}/show/",
     * }, name="user_show", methods="GET")
     */
    public function show(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN_SHOW', null, 'Unable to access this page!');
        // si el usuario que se pretende mostrar no es administrador
        if (!$user->hasRole('ROLE_ADMIN_LIST')) {
            throw $this->createAccessDeniedException('403');
        }

        return $this->render('backend/user/show.html.twig', [
            'user' => $user,
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{identificador}/", name="user_edit", methods="GET|POST")
     */
    public function edit(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN_EDIT', null, 'Unable to access this page!');
        // si el usuario que se pretende mostrar no es administrador
        if (!$user->hasRole('ROLE_ADMIN_LIST')) {
            throw $this->createAccessDeniedException('403');
        }

        $form = $this->createForm(UserType::class, $user, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'user')
        ));

        //obtiene la contrasenna actual del usuario
        $contrasenaAnt = $user->getPassword();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // elimina todos los permisos del usuario actual, para mas adelante volverselos a asociar
            foreach ($user->getUserRoles() as $rol) {
                $user->removeUserRoles($rol);
            }

            $genero = $request->request->get('radio2');
            $permisosAdmin = $request->request->get('checkboxes1', null);

            // asigna los permisos sobre los administradores
            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => 'ROLE_ADMIN_LIST'
            ));
            $user->setUserRoles($role);

            if ($permisosAdmin) {
                foreach ($permisosAdmin as $permiso) {
                    $role = $em->getRepository("App:Rol")->findOneBy(array(
                        "role" => $permiso
                    ));

                    if ($role) {
                        $user->setUserRoles($role);
                    }
                }
            }

            $permisosProperty = $request->request->get('checkboxes2', null);
            // asigna los permisos sobre los propietarios
            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => 'ROLE_USER_PROPERTY_LIST'
            ));
            $user->setUserRoles($role);

            if ($permisosProperty) {
                foreach ($permisosProperty as $permiso) {
                    $role = $em->getRepository("App:Rol")->findOneBy(array(
                        "role" => $permiso
                    ));

                    if ($role) {
                        $user->setUserRoles($role);
                    }
                }
            }

            $permisosLocation = $request->request->get('checkboxes3', null);
            // asigna los permisos sobre la localizacion
            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => 'ROLE_LOCATION_LIST'
            ));
            $user->setUserRoles($role);

            if ($permisosLocation) {
                foreach ($permisosLocation as $permiso) {
                    $role = $em->getRepository("App:Rol")->findOneBy(array(
                        "role" => $permiso
                    ));

                    if ($role) {
                        $user->setUserRoles($role);
                    }
                }
            }

            // indica el genero
            if ($genero == 1) {
                $user->setIsFemale(true);
                $user->setIsMale(false);
            } elseif ($genero == 2) {
                $user->setIsMale(true);
                $user->setIsFemale(false);
            }

            // dimensiones de la imagen
            $x = (int) $form->get('x')->getData();
            $y = (int) $form->get('y')->getData();
            $width = (int) $form->get('width')->getData();
            $height = (int) $form->get('height')->getData();
            $rotate = (int) $form->get('rotate')->getData();
            $scaleX = (int) $form->get('scaleX')->getData();
            $scaleY = (int) $form->get('scaleY')->getData();

            //para proximamente obtener la ruta publica del proyecto
            $package = new Package(new EmptyVersionStrategy());
            //si no existe la carpeta donde se guardaran las imagenes la crea
            $this->crearCarpeta($package->getUrl("files/img/usuarios/" . $user->getUsername() . "/profile"));

            if ($user->getFoto()) {
                if (file_exists($user->getPath())) {

                    unlink($user->getPath());
                }
            }

            $this->subirFoto($user, $user->getUsername() . "/profile", $x, $y, $width, $height);

            if ($user->getPassword() == '') {
                //mantiene el password
                $user->setPassword($contrasenaAnt);
            } else {
                // encripta el password
                $this->cryptPassword($user);
            }

            $em->persist($user);
            $em->flush();

            $message = $this->get('translator')->trans(
                'admin.message.success_2',
                array(),
                'administradores'
            );
            $this->get("session")->getFlashBag()->add("success", $message);

            return $this->redirectToRoute('user_index');
        }

        return $this->render('backend/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/delete/{identificador}/all", name="user_delete", methods="DELETE")
     */
    public function delete(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN_DELETE', null, 'Unable to access this page!');
        // si el usuario que se pretende mostrar no es administrador
        if (!$user->hasRole('ROLE_ADMIN_LIST')) {
            throw $this->createAccessDeniedException('403');
        }

        return $this->json(['implementar'], 400);
    }

    public function crearCarpeta($ruta)
    {
        $fs = new Filesystem;

        if (!$fs->exists($ruta)) {
            $fs->mkdir($ruta);
        }
    }

    public function subirFoto($entidad, $ruta, $x, $y, $width, $height)
    {
        if (NULL === $entidad->getFoto()) {
            return;
        }

        // create an image manager instance with favored driver
        //$manager = new ImageManager(array('driver' => 'imagick'));
        $manager = new ImageManager();
        // to finally create image instances
        $img = $manager->make($entidad->getFoto()->getRealPath());
        $img->orientate();

        $nombreFoto = $entidad->getFoto()->getClientOriginalName();
        $extensionFoto = $entidad->getFoto()->getClientOriginalExtension();
        $nombre = uniqid() . ".$extensionFoto";
        $directorioDestino = "files/img/usuarios/$ruta";
        //$this->foto->move($directorioDestino,$nombre);
        $img->crop($width, $height, $x, $y)
            ->resize(150, 150, function ($constraint) {
                $constraint->aspectRatio();
            })->save($directorioDestino . '/' . $nombre);
        $dir = "files/img/usuarios/$ruta";
        $entidad->setPath("$dir/$nombre");
    }
}
