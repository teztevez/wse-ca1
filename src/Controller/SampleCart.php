<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;   

class SampleCart extends AbstractController
{
    /**
     * @Route("/cart", name="cart") methods={"GET"}
     */
    public function index()
    {
        return $this->render('cart/cart.html.twig', [
            'controller_name' => 'RegisterController',
        ]);
    }
}
