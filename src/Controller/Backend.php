<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Login;
use App\Entity\Orders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Backend extends AbstractController
{
	private $session;
		
	public function __construct(SessionInterface $session) {
		$this->session = $session;
	}
	/**
	* @Route("/backend", name="catch") methods=("GET", "POST")
	*/
	public function index(SessionInterface $session)
	{		
		$request = Request::createFromGlobals(); //the envelope, and we're looking inside it
		
		$type = $request->request->get('type', 'none'); // to send ourselves in different directions
		
		if($type == 'register')
		{
			//perform register process
			
			//get the variables
			$username = $request->request->get('username', 'none');
			$password = $request->request->get('password', 'none');
			$acctype = $request->request->get('acctype', 'none');
			
			//put it in the database
			$entityManager = $this->getDoctrine()->getManager(); //make a manager
			
			$login = new Login(); //make object
			$login->setUsername($username); 
			$login->setPassword($password);
			$login->setAcctype($acctype);
			
			$entityManager->persist($login);
			//execute query and insert into db
			$entityManager->flush();
			
			//return new Response('Register page was called"');
		}
		
		else if($type == 'login') //if we had a login
		{			
			//get username and password
			$username = $request->request->get('username', 'none');
			$password = $request->request->get('password', 'none');		
			
			$repo = $this->getDoctrine()->getRepository(Login::class); //type of entityManager
			
			$person = $repo->findOneBy(['username' => $username, 'password' => $password]);

			//$session->set('id', $repo->findOneBy(['username' => $username])->getId());	//set session id to the id of the user that has logged in		
			$session->set('placedby', $username);
			
			return new Response($person->getAcctype());
			
		}
		
		
		
		else if($type == 'vieworders') {
			//$id = $session->get('id');
			$placedby = $session->get('placedby');
			
			$string1 = "";
			
			$repository = $this->getDoctrine()->getRepository(Orders::class);
            //$orders = $repository->findAll()->get($placedby);
			
			//foreach($orders as $order => $x) {
			//	$string1 .= "<p>".$x."</p>";
			//}
		
					
			return new Response($placedby);
		}
		
		else if($type == 'placeorder'){         
        
        // catch the variables we sent from the JavaScript.
        $placedby = $session->get('placedby');
        $ser = $request->request->get('ser', 'this is the default');
        $address = $request->request->get('address', 'this is the default');
		$status = $request->request->get('status', 'this is the default');
      
      
      
      
        // Break apart the serialized order
	    $ser = substr($ser, 0, -1);
        $data = explode('=', $ser); //this is what order details look like
        
		
		$details = '';
        foreach($data as $record) {  
			$item = explode('-', $record);
			
            
                $details .= $item[0] . ' x ' . $item[1] . "|";
			
            
            
        }
    
        
        // to work the objects
        $entityManager = $this->getDoctrine()->getManager();

        // create blank entity of type "Orders"
        $order = new Orders();
        
        $order->setPlacedBy($placedby);
        //$order->setDetails(substr($details, 0, -1));
		$order->setDetails($details);		//knocks last = sign off the end of description
		$order->setAddress($address);
		$order->setStatus($status);


      
        $entityManager->persist($order);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
 

       
        return new Response(
            'all ok' . $details
        );  
		
		}		
        
       
    
	}
	
}
?>