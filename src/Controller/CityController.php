<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route({
 *
 *     "pt": "/admin/estado",
 *     "es": "/admin/estado",
 *     "en": "/admin/state",
 * })
 */
class CityController extends Controller
{
    /**
     * @Route("/", name="city_index", methods="GET")
     */
    public function index(Request $request, CityRepository $cityRepository): Response
    {

        $this->denyAccessUnlessGranted('ROLE_LOCATION_LIST', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $search = $request->query->get('search', null);
        $countryIdentificador = $request->query->get('country', null);

        // si no se recibe el parametro esperado por GET
        if (!$countryIdentificador) {

            // se obtiene la primera entrada de la tabla
            $country = $em->getRepository("App:Country")->findOneBy(array(
                //'isLocked' => false
            ));

            $consulta = $em->createQuery("Select o From App:City o");

            /* @var $paginator \Knp\Component\Pager\Paginator */
            $paginator  = $this->get('knp_paginator');

            // Paginate the results of the query
            $ciudades = $paginator->paginate(
                // Doctrine Query, not results
                $consulta->getResult(),
                // Define the page parameter
                $request->query->getInt('page', 1),
                // Items per page
                10
            );

            // si la tabla no tiene entradas
            if (!$country) {

                return $this->render('backend/city/index.html.twig', [
                    'cities' => $ciudades,
                    'route' => $request->getRequestUri(),
                    'search' => $search,
                    'page' => $request->query->getInt('page', null),
                ]);
            }
        }
        // si se recibe el parametro esperado por GET
        else {

            // se obtiene la primera entrada de la tabla
            $country = $em->getRepository("App:Country")->findOneBy(array(
                'identificador' => trim($countryIdentificador)
            ));
        }

        $consulta = $em->createQuery("Select o From App:City o
                                      JOIN o.country c
                                      WHERE c.id = :id  
                                      ORDER BY o.name ASC");

        // si se esta realizando una busqueda
        if ($search) {

            $consulta = $em->createQuery("Select o From App:City o
                                      JOIN o.country c 
                                      WHERE o.name LIKE '%$search%'
                                      AND c.id = :id  
                                      ORDER BY o.name ASC");
        }

        $consulta->setParameter('id', $country->getId());

        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');

        // Paginate the results of the query
        $ciudades = $paginator->paginate(
            // Doctrine Query, not results
            $consulta->getResult(),
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('backend/city/index.html.twig', [
            'cities' => $ciudades,
            'route' => $request->getRequestUri(),
            'search' => $search,
            'country' => $country,
            'page' => $request->query->getInt('page', null),
        ]);
    }

    /**
     * @Route({
     *
     *     "pt": "/nuevo",
     *     "es": "/nuevo",
     *     "en": "/new",
     * }, name="city_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LOCATION_CREATE', null, 'Unable to access this page!');

        $city = new City();
        $form = $this->createForm(CityType::class, $city, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'city')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // obtiene el identificador enviado desde el formulario
            $identificador = $request->request->get('select_pais', null);

            // obtiene el pais
            $country = $em->getRepository("App:Country")->findOneBy(array(
                'identificador' => $identificador
            ));

            $city->setCountry($country);

            $em->persist($city);
            $em->flush();

            $message = $this->get('translator')->trans(
                'city.message.success_1',
                array(),
                'ubicacion'
            );

            $this->get("session")->getFlashBag()->add("success", $message);
            return $this->redirectToRoute('city_index');
        }

        return $this->render('backend/city/new.html.twig', [
            'city' => $city,
            'form' => $form->createView(),
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{id}/a/a/a", name="city_show", methods="GET")
     */
    public function show(City $city): Response
    {
        return $this->render('city/show.html.twig', ['city' => $city]);
    }

    /**
     * @Route("/{identificador}/", name="city_edit", methods="GET|POST")
     */
    public function edit(Request $request, City $city): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LOCATION_EDIT', null, 'Unable to access this page!');

        $form = $this->createForm(CityType::class, $city, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'city')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // obtiene el identificador enviado desde el formulario
            $identificador = $request->request->get('select_pais', null);

            // obtiene el pais
            $country = $em->getRepository("App:Country")->findOneBy(array(
                'identificador' => $identificador
            ));

            $city->setCountry($country);

            $em->persist($city);
            $em->flush();

            $message = $this->get('translator')->trans(
                'city.message.success_2',
                array(),
                'ubicacion'
            );

            $this->get("session")->getFlashBag()->add("success", $message);
            return $this->redirectToRoute('city_index');
        }

        return $this->render('backend/city/edit.html.twig', [
            'city' => $city,
            'form' => $form->createView(),
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{identificador}/delete/all", name="city_delete", methods="DELETE")
     */
    public function delete(Request $request, City $city): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LOCATION_DELETE', null, 'Unable to access this page!');

        if ($this->isCsrfTokenValid('delete' . $city->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($city);
            $em->flush();
        }

        return $this->redirectToRoute('city_index');
    }
}
