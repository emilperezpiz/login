<?php

namespace App\Controller\API\v1\Amappzing\Validations;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CommonValidate extends Controller
{

    private $request;
    private $code;

    public function __construct($request = null, $code = null)
    {
        $this->request = $request;
        $this->code = $code;
    }

    public function codeIsValid($context)
    {
        $codeClient = $this->request->headers->get('Accept-Code');
        
        foreach ($this->code as $code) {
            if ($codeClient === $code) {
                return true;
            }
        }
        return array(
            'status' => 401,
            'message' => 'codeError',
        );
    }
}
