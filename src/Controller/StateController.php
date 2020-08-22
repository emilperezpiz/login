<?php

namespace App\Controller;

use App\Entity\State;
use App\Form\StateType;
use App\Repository\StateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route({
 *
 *     "pt": "/admin/cidad",
 *     "es": "/admin/ciudad",
 *     "en": "/admin/city",
 * })
 */
class StateController extends Controller
{
    /**
     * @Route("/", name="state_index", methods="GET")
     */
    public function index(Request $request, StateRepository $stateRepository): Response
    {

        $this->denyAccessUnlessGranted('ROLE_LOCATION_LIST', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();

        $search = $request->query->get('search', null);
        $countryIdentificador = $request->query->get('country', null);
        $cityIdentificador = $request->query->get('city', null);
        $expresion = ' ';
        // si no se recibe el parametro esperado por GET
        if (!$countryIdentificador) {

            // se obtiene la primera entrada de la tabla
            $state = $em->getRepository("App:State")->findOneBy(array(
                //'isLocked' => false
            ));

            // si la tabla no tiene entradas
            if (!$state) {

                $paginator  = $this->get('knp_paginator');

                $consulta = $em->createQuery("Select o From App:State o
                                      ORDER BY o.name ASC");

                // Paginate the results of the query
                $ciudades = $paginator->paginate(
                    // Doctrine Query, not results
                    $consulta,
                    // Define the page parameter
                    $request->query->getInt('page', 1),
                    // Items per page
                    10
                );

                return $this->render('backend/state/index.html.twig', [
                    'states' => $ciudades,
                    'route' => $request->getRequestUri(),
                    'search' => $search,
                    //'country' => null,
                    //'city_state' => null,
                    'page' => $request->query->getInt('page', null),
                ]);
            }

            $city = $state->getCity();
            $country = $city->getCountry();
        }
        // si se recibe el parametro esperado por GET
        else {

            //throw new \Exception($cityIdentificador);
            // se obtiene la primera entrada de la tabla
            $country = $em->getRepository("App:Country")->findOneBy(array(
                'identificador' => trim($countryIdentificador)
            ));

            $city = null;

            if ($cityIdentificador) {

                $city = $em->getRepository("App:City")->findOneBy(array(
                    'identificador' => $cityIdentificador
                ));

                $expresion = ' AND city.identificador = :identificadorCity ';
            }
        }

        $consulta = $em->createQuery("Select o From App:State o
                                      JOIN o.city city
                                      JOIN city.country country
                                      WHERE country.id = :idcountry" .
            $expresion
            . "
                                      ORDER BY o.name ASC");
        // si se esta realizando una busqueda
        if ($search) {

            $consulta = $em->createQuery("Select o From App:State o
                                      JOIN o.city city
                                      JOIN city.country country
                                      WHERE o.name LIKE '%$search%'
                                      AND country.id = :idcountry" .
                $expresion
                . "
                                      ORDER BY o.name ASC");
        }

        $consulta->setParameter('idcountry', $country->getId());

        if ($cityIdentificador) {

            $consulta->setParameter('identificadorCity', $city->getIdentificador());
        }

        // @var $paginator \Knp\Component\Pager\Paginator 
        $paginator  = $this->get('knp_paginator');

        // Paginate the results of the query
        $estados = $paginator->paginate(
            // Doctrine Query, not results
            $consulta->getResult(),
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('backend/state/index.html.twig', [
            'states' => $estados,
            'route' => $request->getRequestUri(),
            'search' => $search,
            'country' => $country,
            'city_state' => $city,
            'page' => $request->query->getInt('page', null),
        ]);
    }

    /**
     * @Route({
     *
     *     "pt": "/nuevo",
     *     "es": "/nuevo",
     *     "en": "/new",
     * }, name="state_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LOCATION_CREATE', null, 'Unable to access this page!');

        $state = new State();
        $form = $this->createForm(StateType::class, $state, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'state')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // obtiene el identificador enviado desde el formulario
            $identificador = $request->request->get('select_ciudad', null);

            // obtiene el pais
            $city = $em->getRepository("App:City")->findOneBy(array(
                'identificador' => $identificador
            ));

            $state->setCity($city);

            $em->persist($state);
            $em->flush();

            $message = $this->get('translator')->trans(
                'state.message.success_1',
                array(),
                'ubicacion'
            );

            $this->get("session")->getFlashBag()->add("success", $message);
            return $this->redirectToRoute('state_index');
        }

        return $this->render('backend/state/new.html.twig', [
            'state' => $state,
            'form' => $form->createView(),
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{id}", name="state_show", methods="GET")
     */
    public function show(State $state): Response
    {
        return $this->render('state/show.html.twig', ['state' => $state]);
    }

    /**
     * @Route("/{identificador}/", name="state_edit", methods="GET|POST")
     */
    public function edit(Request $request, State $state): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LOCATION_EDIT', null, 'Unable to access this page!');

        $form = $this->createForm(StateType::class, $state, array(
            'attr' => array('class' => 'form-horizontal', 'id' => 'state')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // obtiene el identificador enviado desde el formulario
            $identificador = $request->request->get('select_ciudad', null);

            // obtiene el pais
            $city = $em->getRepository("App:City")->findOneBy(array(
                'identificador' => $identificador
            ));

            $state->setCity($city);

            $em->persist($state);
            $em->flush();

            $message = $this->get('translator')->trans(
                'state.message.success_2',
                array(),
                'ubicacion'
            );

            $this->get("session")->getFlashBag()->add("success", $message);
            return $this->redirectToRoute('state_index');
        }

        return $this->render('backend/state/edit.html.twig', [
            'state' => $state,
            'form' => $form->createView(),
            'route' => $request->getRequestUri()
        ]);
    }

    /**
     * @Route("/{identificador}/delete/all", name="state_delete", methods="DELETE")
     */
    public function delete(Request $request, State $state): Response
    {

        $this->denyAccessUnlessGranted('ROLE_LOCATION_DELETE', null, 'Unable to access this page!');

        if ($this->isCsrfTokenValid('delete' . $state->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($state);
            $em->flush();
        }

        return $this->redirectToRoute('state_index');
    }
}
