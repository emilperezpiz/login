<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MongoRegex;

class AuxController extends Controller
{
    private $maxResults = 5;

    /**
     * @Route("/admin/aux", name="aux_search")
     */
    public function search(Request $request)
    {
        //obtiene el usuario actual
        $usuarioActual = $this->container->get('security.token_storage')->getToken()->getUser();

        //si la peticion no se realiza por ajax lanza una excepcion
        if ($request->isXmlHttpRequest() == FALSE) {
            throw $this->createAccessDeniedException('403');
        }

        $em = $this->getDoctrine()->getManager();

        //si se recibe por GET  el parametro pais
        if ($request->query->get('pais')) {

            $search = $request->query->get('pais');
            $consulta = $em->createQuery("Select o From App:Country o WHERE o.name LIKE '%$search%' ");
            //si search vale un caracter vacio, devuelve los primeros 5 objetos
            if ($search == ' ') {
                $consulta = $em->createQuery("Select o From App:Country o ");
                $consulta->setMaxResults($this->maxResults);
            }
            //$consulta->setParameter('bloqueo',false);
            $resultados = $consulta->getResult();
            $data = array();

            foreach ($resultados as $result) {
                $data[] = array(
                    "id" => $result->getIdentificador(),
                    "text" => $result->getName()
                );
            }

            return new JsonResponse($data);
        }
        // si recibe por GET el parametro ciudad
        if ($request->query->get('ciudad')) {
            $search = $request->query->get('ciudad');
            $pais = $request->query->get('pais_ciudad', null);
            
            if (!$pais) {
                $consulta = $em->createQuery("Select o From App:City o WHERE o.name LIKE '%$search%' ");
                //si search vale un caracter vacio, devuelve los primeros 5 objetos
                if ($search == ' ') {
                    $consulta = $em->createQuery("Select o From App:City o");
                    $consulta->setMaxResults($this->maxResults);
                }
            }
            // si se busca la ciudad relacionado con el pais
            else {
                $consulta = $em->createQuery("Select o From App:City o
                                              JOIN o.country country
                                              WHERE o.name LIKE '%$search%' 
                                              AND country.identificador = :identificador");
                //si search vale un caracter vacio, devuelve los primeros 5 objetos
                if ($search == ' ') {
                    $consulta = $em->createQuery("Select o From App:City o 
                                                  JOIN o.country country
                                                  WHERE country.identificador = :identificador");
                    $consulta->setMaxResults($this->maxResults);
                }

                $consulta->setParameter('identificador', trim($pais));
            }

            //$consulta->setParameter('bloqueo',false);
            $resultados = $consulta->getResult();
            $data = array();

            foreach ($resultados as $result) {

                $data[] = array(
                    "id" => $result->getIdentificador(),
                    "text" => $result->getName()
                );
            }

            return new JsonResponse($data);
        }
        // si recibe por GET el parametro estado
        if ($request->query->get('estado')) {

            $search = $request->query->get('estado');
            $pais = $request->query->get('pais_ciudad', null);
            $ciudad = $request->query->get('ciudad_estado', null);

            if (!$pais || !$ciudad) {
                $consulta = $em->createQuery("Select o From App:State o WHERE o.name LIKE '%$search%'");
                //si search vale un caracter vacio, devuelve los primeros 5 objetos
                if ($search == ' ') {
                    $consulta = $em->createQuery("Select o From App:State o");
                    $consulta->setMaxResults($this->maxResults);
                }
            }
            // si se busca la ciudad relacionado con el pais
            else {
                $consulta = $em->createQuery("Select o From App:State o
                                              JOIN o.city city
                                              JOIN city.country country
                                              WHERE o.name LIKE '%$search%'
                                              AND country.identificador = :identificadorCountry
                                              AND city.identificador = :identificadorCity
                                              ");
                //si search vale un caracter vacio, devuelve los primeros 5 objetos
                if ($search === ' ') {
                    $consulta = $em->createQuery("
                                                  Select o From App:State o 
                                                  JOIN o.city city
                                                  JOIN city.country country
                                                  WHERE country.identificador = :identificadorCountry
                                                  AND city.identificador = :identificadorCity
                                                  ");
                    $consulta->setMaxResults($this->maxResults);
                }

                $consulta->setParameter('identificadorCountry', trim($pais));
                $consulta->setParameter('identificadorCity', trim($ciudad));
            }

            $resultados = $consulta->getResult();
            $data = array();

            foreach ($resultados as $result) {

                $data[] = array(
                    "id" => $result->getIdentificador(),
                    "text" => $result->getName()
                );
            }

            return new JsonResponse($data);
        }
        //si se recibe por GET  el parametro propietario
        if ($request->query->get('propietario')) {

            $search = $request->query->get('propietario');
            $consulta = $em->createQuery("Select o From App:User o
                                          JOIN o.userRoles ur 
                                          WHERE ur.role = :permiso 
                                          WHERE o.name LIKE '%$search%' 
                                          ");
            //si search vale un caracter vacio, devuelve los primeros 5 objetos
            if ($search == ' ') {
                $consulta = $em->createQuery("Select o From App:User o 
                                              JOIN o.userRoles ur 
                                              WHERE ur.role = :permiso");
                $consulta->setMaxResults($this->maxResults);
            }

            $consulta->setParameter('permiso', "ROLE_PROPERTY");
            $resultados = $consulta->getResult();
            $data = array();

            foreach ($resultados as $result) {

                $data[] = array(
                    "id" => $result->getIdentificador(),
                    "text" => $result->getName()
                );
            }

            return new JsonResponse($data);
        }

        if ($request->query->get('category')) {

            $categories = $this->get('doctrine_mongodb')
                ->getRepository('App:BussinesClasification')
                ->findBy(array(
                    'isActive' => true
                ));
            $data = array();

            foreach ($categories as $category) {

                $data[] = array(
                    "id" => $category->getId(),
                    "text" => $category->getName()
                );
            }

            return new JsonResponse($data);
        }

        if ($request->query->get('role')) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $search = $request->query->get('role');
            $db = $dm->createQueryBuilder("App:Rol");
            $db->field('role')->equals(new MongoRegex("/.*$search*/i"));

            if ($search === " ") {
                $db = $dm->createQueryBuilder("App:Rol");
                $notRole = ["ROLE_ADMIN", "ROLE_PROPERTY"];
                $db->field('role')->notIn($notRole);
                //$db->field('role')->notEqual("ROLE_PROPERTY");
            }

            $consulta = $db->getQuery();
            $roles = $consulta->execute();
            $data = array();

            foreach ($roles as $role) {

                $data[] = array(
                    "id" => $role->getIdentificador(),
                    "text" => $role->getRole()
                );
            }

            return new JsonResponse($data);
        }

        if ($request->query->get('setting_gym_role')) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $em = $this->getDoctrine()->getManager();
            $search = $request->query->get('setting_gym_role');
            $db = $dm->createQueryBuilder("App:SettingGym");
            //$db->field('settingRole')->equals(new MongoRegex("/.*$search*/i"));
            if ($search === " ") {
                $db = $dm->createQueryBuilder("App:SettingGym");
                //$db->select('settingRole');
                //$db->field('role')->notEqual("ROLE_PROPERTY");
            }

            $query = $db->getQuery();
            $result = $query->getSingleResult();
            $data = array();

            foreach ($result->getSettingRole() as $role) {
                $currentRole = $em->getRepository("App:Rol")->findOneBy(array(
                    "role" => trim($role->getRole())
                ));

                if ($currentRole) {
                    $data[] = array(
                        "id" => $role->getRole(),
                        "text" => $role->getRole()
                    );
                }
            }

            return new JsonResponse($data);
        }

        if ($request->query->get('productClasification')) {
            $identificador = $request->query->get('business', null);

            if (!$identificador) {
                throw $this->createNotFoundException('404');
            }

            $business = $this->get('doctrine_mongodb')
                ->getRepository('App:Business')
                ->findOneBy(array(
                    'identificador' => $identificador
                ));
            $clasificationsProducts = $this->get('doctrine_mongodb')
                ->getRepository('App:ProductClasification')
                ->findBy(array(
                    'bussines' => $business
                ));
            $data = array();

            foreach ($clasificationsProducts as $clasification) {
                $data[] = array(
                    "id" => $clasification->getId(),
                    "text" => $clasification->getName()
                );
            }
            
            return $this->json($data);
        }
    }
}
