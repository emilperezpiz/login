<?php

/**
 * Created by PhpStorm.
 * User: Dr
 * Date: 13/10/17
 * Time: 23:42
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Role;
use App\Utils\ActionUtil;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends Controller
{

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @Route("/api/login", name="api_login", methods="POST")
     */
    public function apiLogin(Request $request, AuthenticationSuccessHandler $authenticationSuccessHandler)
    {
        $codeClient = $request->headers->get('Accept-Code');
        $language = $request->headers->get('Accept-Language');
        $modeTheme = "";
        if ($language === "es" || $language === "en" || $language === "pt") {
            $request->setLocale($language);
        }

        $em = $this->getDoctrine()->getManager();

        if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        $username = $request->request->get('username', null);
        $password = $request->request->get('password', null);
        
        if (!$username || !$password) {

            $parameters = json_decode($request->getContent(), true);

            if (!$username && !isset($parameters['username'])) {
                return new JsonResponse(['usuario o contraseña no pueden ser vacíos'], 403);
            }

            if (!$password && !isset($parameters['password'])) {
                return new JsonResponse(['usuario o contraseña no pueden ser vacíos'], 403);
            }

            $username = $parameters['username'];
            $password = $parameters['password'];
        }

        if ($codeClient === "GWE&A") {
            $consulta = $em->createQuery("Select o From App:User o 
                                      JOIN o.userRoles ur 
                                      WHERE o.userName = :username 
                                      AND o.isActive = :isActive");
            $consulta->setParameter('username', $username);
            $consulta->setParameter('isActive', true);
            $user = $consulta->getResult();

            if (count($user) < 1) {
                return new JsonResponse(['usuario o contraseña incorrectos 1'], 403);
            }

            $user = $consulta->getSingleResult();
            $password = $this->encoder->isPasswordValid($user, trim($password));

            if (!$password) {
                return new JsonResponse(['usuario o contraseña incorrectos'], 403);
            }

            if ($user->getCurrentRoles() && $user->getCurrentRoles()[0]) {
                $currentRole = $user->getCurrentRoles()[0]->getRole();
            }

            $jwt = $this->container->get('lexik_jwt_authentication.jwt_manager')->create($user);
            

            $response = array(
                'token' => $jwt,
                'profile' => array(
                    'fullname' => $user->getName() . ' ' . $user->getSurName(),
                    'img' => $protocol . $request->getHttpHost() . '/arya/public/' . $user->getPath(),
                    'workPosition' => $this->getPosition($currentRole, $request->getLocale()),
                    'role' => $currentRole
                ),
                'setting' => array(
                    'language' => $language,
                    'modeTheme' => $modeTheme
                ),
                'business' => array(
                    "id" => "NPL",
                    "name" => "NPL Brasil",
                    "about" => "A NPL Brasil é uma gestora independente de créditos corporativos inadimplidos (non perfoming loans - NPL)",
                    "logo" => "https://amappzing.com.br/bundles/app/frontend/img/Recurso1.png"
                )
            );

            return $this->json($response);
        }

        return $this->json(['usted no tiene permisos para acceder al recurso solicitado'], 401);
    }

    public function getPosition(string $role, string $locale): string
    {
        if ($role === "ROLE_WORKER_TEACHER") {
            $this->get('translator')->setLocale($locale);
            $position = $this->get('translator')->trans(
                'workposition.teacher',
                array('en'),
                'messages'
            );

            return $position;
        }
        if ($role === "ROLE_WORKER_SECRETARY") {
            $this->get('translator')->setLocale($locale);
            $position = $this->get('translator')->trans(
                'workposition.secretary',
                array('en'),
                'messages'
            );

            return $position;
        }
        return "";
    }
}
