<?php

namespace App\Controller\Backend;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProfileController extends Controller
{

    /**
     * @Route({
     *     "pt":"/admin/meu-perfil",
     *     "es":"/admin/mi-perfil",
     *     "en":"/admin/my-profile"
     * }, name="backend_my_profile")
     */
    public function profile(Request $request)
    {
        
        /*if ($this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }*/
        
        //throw new \Exception(var_dump($usuarioActual->getRoles()));
        //$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Unable to access this page!');

        $usuarioActual = $this->container->get('security.token_storage')->getToken()->getUser();

        //throw new \Exception("Error Processing Request", 1);

        return $this->render('backend/profile/profile.html.twig', [
            
            'route' => $request->getRequestUri(),
        ]);
    }
}
