<?php

namespace App\Controller;

use App\Entity\Country;
use App\Form\CountryType;
use App\Repository\CountryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route({
 *
 *     "pt": "/admin/pais",
 *     "es": "/admin/pais",
 *     "en": "/admin/country",
 * })
 */
class CountryController extends Controller
{
    /**
     * @Route("/", name="country_index", methods="GET")
     */
    public function index(Request $request, CountryRepository $countryRepository): Response
    {

        $this->denyAccessUnlessGranted('ROLE_LOCATION_LIST', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $consulta = $em->createQuery("Select o From App:Country o 
                                      ORDER BY o.name ASC");

        $search = $request->query->get('search', null);

        // si se esta realizando una busqueda
        if ($search) {

            $consulta = $em->createQuery("Select o From App:Country o
                                      WHERE o.name LIKE '%$search%'   
                                      ORDER BY o.name ASC");
        }


        /* @var $paginator \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');

        // Paginate the results of the query
        $paises = $paginator->paginate(
            // Doctrine Query, not results
            $consulta->getResult(),
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('backend/country/index.html.twig', [
            'countries' => $paises,
            'route' => $request->getRequestUri(),
            'search' => $search,
            'page' => $request->query->getInt('page', null),
        ]);
    }

    /**
     * @Route({
     *
     *     "pt": "/nuevo",
     *     "es": "/nuevo",
     *     "en": "/new",
     * }, name="country_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {

        $this->denyAccessUnlessGranted('ROLE_LOCATION_CREATE', null, 'Unable to access this page!');

        $country = new Country();
        $form = $this->createForm(CountryType::class, $country, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'country')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($country);
            $em->flush();

            $message = $this->get('translator')->trans(
                'country.message.success_1',
                array(),
                'ubicacion'
            );

            $this->get("session")->getFlashBag()->add("success", $message);
            return $this->redirectToRoute('country_index');
        }

        return $this->render('backend/country/new.html.twig', [
            'country' => $country,
            'form' => $form->createView(),
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{identificador}/", name="country_edit", methods="GET|POST")
     */
    public function edit(Request $request, Country $country): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LOCATION_EDIT', null, 'Unable to access this page!');

        $form = $this->createForm(CountryType::class, $country, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'country')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $message = $this->get('translator')->trans(
                'country.message.success_2',
                array(),
                'ubicacion'
            );

            $this->get("session")->getFlashBag()->add("success", $message);
            return $this->redirectToRoute('country_index');
        }

        return $this->render('backend/country/edit.html.twig', [
            'country' => $country,
            'form' => $form->createView(),
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{identificador}/delete/all", name="country_delete", methods="DELETE")
     */
    public function delete(Request $request, Country $country): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LOCATION_DELETE', null, 'Unable to access this page!');

        if ($this->isCsrfTokenValid('delete' . $country->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($country);
            $em->flush();
        }

        return $this->redirectToRoute('country_index');
    }
}
