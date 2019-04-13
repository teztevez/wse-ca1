<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Orders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;        
        
class CatchTheCart extends AbstractController
{
    /**
     * @Route("/catchTheCart", name="catch2") methods={"GET","POST"}
     */
    public function index()
    {
        $request = Request::createFromGlobals(); // the envelope, and were looking inside it.

        
        
        
        
        // catch the variables we sent from the JavaScript.
        $placedby = $request->request->get('placedby', 'this is the default');
        $ser = $request->request->get('ser', 'this is the default');
        $address = $request->request->get('address', 'this is the default');
		$status = $request->request->get('status', 'this is the default');
      
      
      
      
      // Break apart the serialized order
      // $data = explode('=', 'cookies-2=pizza-2='); <--- this is what order details look like
        /*
        foreach($data as $record) {    
           

            $item = explode('-',$record);
            echo 'Item: ' . $item[0] . '<br>';
            echo 'Qty: ' . $item[1] . '<br>';
            
            
            
        }
        */
    
        
        // to work the objects
        $entityManager = $this->getDoctrine()->getManager();

        // create blank entity of type "Orders"
        $order = new Orders();
        
        $order->setPlacedBy($placedby);
        $order->setDetails(substr($ser, 0, -1)); //knocks last = sign off the end of description
		$order->setAddress($address);
		$order->setStatus($status);


      
        $entityManager->persist($order);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

 
 

       
        return new Response(
            'all ok' . $ser
        );

     
        
       
    }
}
