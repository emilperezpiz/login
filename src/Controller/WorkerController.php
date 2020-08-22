<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\WorkerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Intervention\Image\ImageManager;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route({
 *
 *     "pt": "/admin/empresas",
 *     "es": "/admin/negocios",
 *     "en": "/admin/business",
 * })
 */
class WorkerController extends Controller
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    //encripta la contrasenna
    private function cryptPassword($user, $password = null)
    {
        //$pass = trim("@@AmappzingTMArya");
        if ($password) {
            $pass = $password;
        } else {
            $pass = trim($user->getCpf());
        }

        $password = $this->encoder->encodePassword($user, "$pass");
        $user->setPassword($password);
    }

    /**
     * @Route({
     *     "pt": "/{identificador}/trabalhadores",
     *     "es": "/{identificador}/trabajadores",
     *     "en": "/{identificador}/worker",
     * }, name="worker_index")
     */
    public function index($identificador, Request $request)
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!');
        $dm = $this->get('doctrine_mongodb')->getManager();
        $search = $request->query->get('search', null);
        $business = $this->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findOneBy(array(
                'identificador' => $identificador
            ));
        $em = $this->getDoctrine()->getManager();
        $consulta = $em->createQuery("Select o From App:User o 
            JOIN o.userRoles ur 
            WHERE o.business = :business
            AND (ur.role <> :permiso1)
            AND (ur.role <> :permiso2) 
            ORDER BY o.updatedAt DESC");
        $consulta->setParameter('business', $business->getIdentificador());
        $consulta->setParameter('permiso1', "ROLE_PROPERTY");
        $consulta->setParameter('permiso2', "ROLE_GYM_CLIENT");

        if ($search) {
        }

        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        // Paginate the results of the query
        $workers = $paginator->paginate(
            // Doctrine Query, not results
            $consulta->getResult(),
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('backend/worker/index.html.twig', [
            'search' => $search,
            'route' => $request->getRequestUri(),
            'business' => $business,
            'workers' => $workers,
        ]);
    }

    /**
     * @Route({
     *
     *     "pt": "/{identificador}/trabalhadores/novo",
     *     "es": "/{identificador}/trabajadores/nuevo",
     *     "en": "/{identificador}/worker/new",
     * }, name="worker_new", methods="GET|POST")
     */
    public function new($identificador, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUSINESS_CREATE', null, 'Unable to access this page!');
        $dm = $this->get('doctrine_mongodb')->getManager();
        $business = $this->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findOneBy(array(
                'identificador' => $identificador
            ));
        $user = new User();
        $form = $this->createForm(WorkerType::class, $user, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'worker', 'autocomplete' => 'off')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setBusiness($business->getIdentificador());
            $genero = $request->request->get('radio2');
            $getRole = $request->request->get('role');

            // indica el genero
            if ($genero == 1) {
                $user->setIsFemale(true);
                $user->setIsMale(false);
            } elseif ($genero == 2) {
                $user->setIsMale(true);
                $user->setIsFemale(false);
            }

            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => $getRole
            ));

            if (!$role) {
                throw $this->createNotFoundException('El rol no existe');
            }

            $user->setUserRoles($role);
            // dimensiones de la imagen
            $x = (int) $form->get('x')->getData();
            $y = (int) $form->get('y')->getData();
            $width = (int) $form->get('width')->getData();
            $height = (int) $form->get('height')->getData();
            $rotate = (int) $form->get('rotate')->getData();
            $scaleX = (int) $form->get('scaleX')->getData();
            $scaleY = (int) $form->get('scaleY')->getData();
            $package = new Package(new EmptyVersionStrategy());
            $this->crearCarpeta($package->getUrl("files/img/trabajadores/" . $user->getUsername() . "/profile"));
            $this->subirFoto($user, $user->getUsername() . "/profile", $x, $y, $width, $height);
            $this->cryptPassword($user);
            $em->persist($user);
            $em->flush();

            $message = $this->get('translator')->trans(
                'worker.message.success_1',
                array(),
                'trabajadores'
            );
            $this->get("session")->getFlashBag()->add("success", $message);

            return $this->redirectToRoute('worker_index', ['identificador' => $business->getIdentificador()]);
        }

        return $this->render('backend/worker/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'business' => $business,
            'route' => $request->getRequestUri(),
        ]);
    }

    /**
     * @Route({
     *
     *     "pt": "/{identificador}/trabalhadores/{currentId}/detalhes",
     *     "es": "/{identificador}/trabajadores/{currentId}/detalles",
     *     "en": "/{identificador}/worker/{currentId}/show",
     * }, name="worker_show", methods="GET|POST")
     */
    public function show($identificador, $currentId, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUSINESS_SHOW', null, 'Unable to access this page!');
        $dm = $this->get('doctrine_mongodb')->getManager();
        $em = $this->getDoctrine()->getManager();
        $business = $this->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findOneBy(array(
                'identificador' => $identificador
            ));

        if (!$business) {
            throw $this->createNotFoundException('404');
        }

        $user = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => $currentId
        ));

        if (!$user) {
            throw $this->createNotFoundException('404');
        }

        return $this->render('backend/worker/show.html.twig', [
            'user' => $user,
            'business' => $business,
            'route' => $request->getRequestUri(),
        ]);
    }

    /**
     * @Route({
     *
     *     "pt": "/{identificador}/trabalhadores/{currentId}/editar",
     *     "es": "/{identificador}/trabajadores/{currentId}/editar",
     *     "en": "/{identificador}/worker/{currentId}/edit",
     * }, name="worker_edit", methods="GET|POST")
     */
    public function edit($identificador, $currentId, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUSINESS_EDIT', null, 'Unable to access this page!');
        $dm = $this->get('doctrine_mongodb')->getManager();
        $em = $this->getDoctrine()->getManager();
        $business = $this->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findOneBy(array(
                'identificador' => $identificador
            ));

        if (!$business) {
            throw $this->createNotFoundException('404');
        }

        $user = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => $currentId,
            "business" => $business->getIdentificador()
        ));

        if (!$user) {
            throw $this->createNotFoundException('404');
        }

        $form = $this->createForm(WorkerType::class, $user, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'worker', 'autocomplete' => 'off')
        ));

        $form->handleRequest($request);

        //obtiene la contrasenna actual del usuario
        $contrasenaAnt = $user->getPassword();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setBusiness($business->getIdentificador());
            $genero = $request->request->get('radio2');
            $getRole = $request->request->get('role');

            // indica el genero
            if ($genero == 1) {
                $user->setIsFemale(true);
                $user->setIsMale(false);
            } elseif ($genero == 2) {
                $user->setIsMale(true);
                $user->setIsFemale(false);
            }

            // elimina todos los permisos del usuario actual, para mas adelante volverselos a asociar
            foreach ($user->getUserRoles() as $rol) {
                $user->removeUserRoles($rol);
            }

            $role = $em->getRepository("App:Rol")->findOneBy(array(
                "role" => $getRole
            ));

            if (!$role) {
                throw $this->createNotFoundException('El rol no existe');
            }

            $user->setUserRoles($role);
            // dimensiones de la imagen
            $x = (int) $form->get('x')->getData();
            $y = (int) $form->get('y')->getData();
            $width = (int) $form->get('width')->getData();
            $height = (int) $form->get('height')->getData();
            $rotate = (int) $form->get('rotate')->getData();
            $scaleX = (int) $form->get('scaleX')->getData();
            $scaleY = (int) $form->get('scaleY')->getData();
            $package = new Package(new EmptyVersionStrategy());
            $this->crearCarpeta($package->getUrl("files/img/trabajadores/" . $user->getUsername() . "/profile"));

            if ($user->getFoto()) {
                if (file_exists($user->getPath())) {
                    unlink($user->getPath());
                }
            }

            $this->subirFoto($user, $user->getUsername() . "/profile", $x, $y, $width, $height);
            $user->setPassword($contrasenaAnt);
            $em->persist($user);
            $em->flush();
            $message = $this->get('translator')->trans(
                'worker.message.success_2',
                array(),
                'trabajadores'
            );
            $this->get("session")->getFlashBag()->add("success", $message);

            return $this->redirectToRoute('worker_index', ['identificador' => $business->getIdentificador()]);
        }

        return $this->render('backend/worker/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'business' => $business,
            'route' => $request->getRequestUri(),
        ]);
    }

    /**
     * @Route("/{identificador}/trabalhadores/{currentId}/eliminar/all", name="worker_delete", methods="DELETE")
     */
    public function delete($identificador, $currentId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BUSINESS_SHOW', null, 'Unable to access this page!');

        $dm = $this->get('doctrine_mongodb')->getManager();
        $em = $this->getDoctrine()->getManager();

        $business = $this->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findOneBy(array(
                'identificador' => $identificador
            ));

        if (!$business) {
            throw $this->createNotFoundException('404');
        }

        $user = $em->getRepository("App:User")->findOneBy(array(
            "identificador" => $currentId,
            "business" => $business->getIdentificador()
        ));

        if (!$user) {
            throw $this->createNotFoundException('404');
        }

        $currentImage = $user->getPath();
        $username = $user->getUsername();

        if (file_exists($currentImage)) {
            unlink($currentImage);
        }

        if (file_exists('files/img/trabajadores/' . $username . "/profile/")) {
            rmdir('files/img/trabajadores/' . $username . "/profile/");
        }

        if (file_exists('files/img/trabajadores/' . $username)) {
            rmdir('files/img/trabajadores/' . $username);
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
        $directorioDestino = "files/img/trabajadores/$ruta";
        //$this->foto->move($directorioDestino,$nombre);
        $img->crop($width, $height, $x, $y)
            ->resize(150, 150, function ($constraint) {
                $constraint->aspectRatio();
            })->save($directorioDestino . '/' . $nombre);
        $dir = "files/img/trabajadores/$ruta";
        $entidad->setPath("$dir/$nombre");
    }
}
