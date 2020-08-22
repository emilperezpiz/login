<?php

namespace App\Controller\API\v1;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends Controller
{
    /**
     * @Route("/api/v1/", name="api_v1")
     */
    public function index()
    {

        return $this->json(array('username' => 'emil@gmail.com'));
        return new JsonResponse(['ok']);
    }
}
