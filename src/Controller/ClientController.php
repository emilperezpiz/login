<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Filesystem\Filesystem;
use Intervention\Image\ImageManager;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * @Route({
 *
 *     "pt": "/admin/propietarios",
 *     "es": "/admin/propietarios",
 *     "en": "/admin/propietarios",
 * })
 */
class ClientController extends Controller
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
     * @Route("/", name="client_index")
     */
    public function index(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_USER_PROPERTY_LIST', null, 'Unable to access this page!');

        $search = $request->query->get('search', null);

        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery("Select o From App:User o 
                                      JOIN o.userRoles ur 
                                      WHERE ur.role = :permiso 
                                      AND o.isActive = :activo
                                      AND o.isDelete = :delete 
                                      ORDER BY o.updatedAt DESC");

        if ($search) {
            $consulta = $em->createQuery("Select o From App:User o
                                          JOIN o.userRoles ur
                                          WHERE ur.role = :permiso 
                                          AND o.isActive = :activo
                                          AND o.isDelete = :delete
                                          AND (
                                          o.name LIKE '%$search%'
                                          OR o.surname LIKE '%$search%'
                                          OR o.userName LIKE '%$search%'
                                          )
                                          ORDER BY o.updatedAt DESC");
        }
        $consulta->setParameter('permiso', "ROLE_PROPERTY");
        $consulta->setParameter('activo', true);
        $consulta->setParameter('delete', false);

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

        return $this->render('backend/client/index.html.twig', [
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
     * }, name="client_new", methods="GET|POST")
     */
    public function new(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_PROPERTY_CREATE', null, 'Unable to access this page!');

        $user = new User();
        $user->setIsActive(true);
        $form = $this->createForm(UserType::class, $user, array(
            'attr' => array(
                'class' => 'form-horizontal',
                'id' => 'user',
                'autocomplete' => 'off'
            )
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $genero = $request->request->get('radio2');

            // asigna los permisos sobre los administradores
            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => 'ROLE_PROPERTY'
            ));
            $user->setUserRoles($role);

            // indica el genero
            if ($genero == 1) {
                $user->setIsFemale(true);
            } elseif ($genero == 2) {
                $user->setIsMale(true);
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
                'admin.message.success_2',
                array(),
                'propietarios'
            );
            $this->get("session")->getFlashBag()->add("success", $message);
            return $this->redirectToRoute('client_index');
        }

        return $this->render('backend/client/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'route' => $request->getRequestUri(),
        ]);
    }

    /**
     * @Route({
     *
     *     "pt": "/{identificador}/detalles/",
     *     "es": "/{identificador}/detalles/",
     *     "en": "/{identificador}/show/",
     * }, name="client_show", methods="GET")
     */
    public function show(Request $request, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_PROPERTY_SHOW', null, 'Unable to access this page!');

        // si el usuario que se pretende mostrar no es administrador
        if (!$user->hasRole('ROLE_PROPERTY')) {

            throw $this->createAccessDeniedException('403');
        }

        return $this->render('backend/client/show.html.twig', [
            'user' => $user,
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{identificador}/", name="client_edit", methods="GET|POST")
     */
    public function edit(Request $request, User $user)
    {

        $this->denyAccessUnlessGranted('ROLE_USER_PROPERTY_EDIT', null, 'Unable to access this page!');

        // si el usuario que se pretende mostrar no es propietario
        if (!$user->hasRole('ROLE_PROPERTY')) {

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

            $genero = $request->request->get('radio2');

            // asigna los permisos sobre los administradores
            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => 'ROLE_PROPERTY'
            ));
            $user->setUserRoles($role);

            // indica el genero
            if ($genero == 1) {
                $user->setIsFemale(true);
                $user->setIsMale(false);
            } elseif ($genero == 2) {
                $user->setIsFemale(false);
                $user->setIsMale(true);
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
                'propietarios'
            );

            $this->get("session")->getFlashBag()->add("success", $message);
            return $this->redirectToRoute('client_index');
        }

        return $this->render('backend/client/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{identificador}/eliminar", name="client_delete", methods="DELETE")
     */
    public function delete(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER_PROPERTY_DELETE', null, 'Unable to access this page!');

        $dm = $this->get('doctrine_mongodb')->getManager();
        $em = $this->getDoctrine()->getManager();

        $business = $this->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findBy(array(
                'user' => $user->getIdentificador()
            ));

        if ($business) {

            $user->setIsDelete(true);

            foreach ($business as $item) {
                $item->setIsDelete(true);
                $dm->persist($item);
            }
            $dm->flush();

            $em->persist($user);
            $em->flush();

            return $this->json([]);
        }

        $currentImage = $user->getPath();
        $username = $user->getUsername();

        if (file_exists($currentImage)) {

            unlink($currentImage);
        }

        if (file_exists('files/img/usuarios/' . $username . "/profile/")) {
            rmdir('files/img/usuarios/' . $username . "/profile/");
        }

        if (file_exists('files/img/usuarios/' . $username)) {
            rmdir('files/img/usuarios/' . $username);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([]);
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
