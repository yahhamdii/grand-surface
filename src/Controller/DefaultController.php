<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{

     /**
     * @Route("")
     */
    public function index()
    {
        return $this->render('Default/index.html.twig');
    }

}
