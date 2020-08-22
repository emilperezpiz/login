<?php

namespace App\Controller\API\v1\Gym;

use App\Controller\API\v1\Gym\Actions\Interfaces\IUser;
use App\Controller\API\v1\Gym\Validations\CommonValidate;
use App\Controller\API\v1\Gym\Validations\User\CommonUserValidate;
use App\Controller\API\v1\Gym\Validations\User\EditUserValidate;
use App\Controller\API\v1\Gym\Validations\User\NewUserValidate;
use App\Controller\API\v1\Gym\Validations\User\RemoveUserValidate;
use App\Controller\API\v1\Gym\Validations\User\ShowUserValidate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/v1/gym/admin")
 */
class WorkerGymRouteController extends Controller
{
    // codigo del acceso de los clientes
    private $codes = ["GWE&A"];
    public $serviceUser;

    public function __construct(IUser $_serviceUser)
    {
        $this->serviceUser = $_serviceUser;
    }

    /**
     * @Route("/{identificador}/user", name="npl_user_list", methods="GET")
     */
    public function userList(Request $request, $identificador)
    {
        CommonUserValidate::_isGranted($this, $identificador);
        $commonValidate = new CommonValidate($request, $this->codes[0]);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $response = $this->serviceUser->list($this, $identificador);
        return $this->json($response);
    }

    /**
     * @Route("/{identificador}/user/new", name="npl_user_new", methods="POST")
     */
    public function userNew(Request $request, $identificador)
    {
        CommonUserValidate::_isGranted($this, $identificador);
        $commonValidate = new CommonValidate($request, $this->codes[0]);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $parameters = $request->request->all();

        foreach ($request->files as $file) {
            $parameters['img'] = $file;
        }

        $newUserValidate = NewUserValidate::requestIsCorrect($parameters, $this);

        if ($newUserValidate['status'] !== 200) {
            return $this->json($newUserValidate['message'], $newUserValidate['status']);
        }

        $response = $this->serviceUser->new($this, $identificador, $parameters);

        return $this->json($response);
    }

    /**
     * @Route("/{identificador}/user/{currentId}/show", name="npl_user_show", methods="GET")
     */
    public function userShow(Request $request, $identificador, $currentId)
    {
        CommonUserValidate::_isGranted($this, $identificador);
        $commonValidate = new CommonValidate($request, $this->codes[0]);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $showUserValidate = ShowUserValidate::requestIsCorrect($currentId, $this);

        if ($showUserValidate['status'] !== 200) {
            return $this->json($showUserValidate['message'], $showUserValidate['status']);
        }

        $response = $this->serviceUser->show($this, $identificador, $currentId);

        return $this->json($response);
    }

    /**
     * @Route("/{identificador}/user/{currentId}/edit", name="npl_user_edit", methods="POST")
     */
    public function userEdit(Request $request, $identificador, $currentId)
    {
        CommonUserValidate::_isGranted($this, $identificador);
        $commonValidate = new CommonValidate($request, $this->codes[0]);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $parameters = $request->request->all();
        $parameters['img'] = null;

        foreach ($request->files as $file) {
            $parameters['img'] = $file;
        }

        $editUserValidate = EditUserValidate::requestIsCorrect($parameters, $this, $currentId);

        if ($editUserValidate['status'] !== 200) {
            return $this->json($editUserValidate['message'], $editUserValidate['status']);
        }

        $response = $this->serviceUser->edit($this, $identificador, $currentId, $parameters);

        return $this->json($response);
    }

    /**
     * @Route("/{identificador}/user/{currentId}/delete", name="npl_user_remove", methods="DELETE")
     */
    public function userRemove(Request $request, $identificador, $currentId)
    {
        CommonUserValidate::_isGranted($this, $identificador);
        $commonValidate = new CommonValidate($request, $this->codes[0]);
        $response = $commonValidate->codeIsValid($this);

        if ($response !== true) {
            return $this->json($response['message'], $response['status']);
        }

        $removeUserValidate = RemoveUserValidate::requestIsCorrect($currentId, $this, $identificador);

        if ($removeUserValidate['status'] !== 200) {
            return $this->json($removeUserValidate['message'], $removeUserValidate['status']);
        }

        $response = $this->serviceUser->remove($this, $identificador, $currentId);
        
        return $this->json($response);
    }
}
