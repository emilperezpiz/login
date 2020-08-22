<?php

namespace App\Controller\Backend;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{

    /**
     * @Route({
     *     "pt":"/admin",
     *     "es":"/admin/",
     *     "en":"/admin"
     * }, name="backend_homepage")
     */
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!');
        /*if ($this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }*/
        $usuarioActual = $this->container->get('security.token_storage')->getToken()->getUser();
        //throw new \Exception(var_dump($usuarioActual->getRoles()));
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!');
        $this->denyAccessUnlessGranted('ROLE_ADMIN_LIST', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("Select o From App:User o 
                                      JOIN o.userRoles ur 
                                      WHERE ur.role = :permiso 
                                      AND o.isActive = :activo 
                                      ORDER BY o.updatedAt DESC");
        $query->setParameter('permiso', "ROLE_PROPERTY");
        $query->setParameter('activo', true);

        $cantClients = count($query->getResult());

        $query->setParameter('permiso', "ROLE_ADMIN_LIST");

        $cantAdmin = count($query->getResult());

        $bussinesCant = count($this->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findBy(array(
                'isActive' => true
            )));

        $bussinesActiveCant = count($this->get('doctrine_mongodb')
            ->getRepository('App:Business')
            ->findBy(array(
                'isLocked' => true
            )));

        return $this->render('backend/index/index.html.twig', [
            'controller_name' => 'IndexController',
            'cantClients' => $cantClients,
            'cantAdmin' => $cantAdmin,
            'bussinesCant' => $bussinesCant,
            'bussinesActiveCant' => $bussinesActiveCant,
            'route' => $request->getRequestUri(),
        ]);
    }
}
