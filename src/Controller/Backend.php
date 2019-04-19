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
		
		else if($type == 'custorders') 
		{
		//$id = $session->get('id');
			$customer = $session->get('placedby');
			
			$repo = $this->getDoctrine()->getRepository(Orders::class);
            $custorder = $repo->findAll();
			
			$tab = "<h2>Order History for ".ucfirst($customer)."</h2>";
			
			$tab .= '<table><thead><tr><th data-priority="1">Order ID</th><th>Order Details</th><th>Delivering To:</th><th>Order Total</th><th>Order Status</th></tr></thead><tbody>';
			
			foreach($custorder as $o) {
				if($o->getPlacedby() == $customer) {
				    $tab .= "<tr>";
				    $tab .= "<td>".$o->getId()."</td>";
				    
				    $tab .= "<td>".$o->getDetails()."</td>";
				    $tab .= "<td>".$o->getAddress()."</td>";
				    $tab .= "<td>€".$o->getTotal()."</td>";
				    $tab .= "<td>".$o->getStatus()."</td>";
				    $tab .= "</tr>";
				}
		    }
			
			$tab .= "</tbody></table>";			
		
					
			return new Response($tab);
		}		
		
		else if($type == 'vieworders') { //driver orders (leaves out cancelled orders)
			
			$repository = $this->getDoctrine()->getRepository(Orders::class);
            $orders = $repository->findAll();
			
			//inserted javascript here because wasn't functioning otherwise
			$table = '<script>$("[id^=change").click(function(){
                      console.log("Test");
                      var oid = $(this).attr("orderid");           
	
				      $.post( "/backend", { type: "updateStatus", oid: oid}) 
					  .done(function(data) {
				      alert(data);				
				      document.getElementById("vieworders1").click(); //automatically clicks button to refresh this tab				
				      });    
				      });	
					 </script>';
			
			$table .= '<table data-role="table"><thead><tr><th>Order ID</th><th>Customer</th><th>Details</th><th>Delivering To:</th><th>Total</th><th>Status</th></tr></tr></thead><tbody>';
			
			foreach($orders as $order) {
				if($order->getStatus() != "Cancelled") {
				    $table .= "<tr>";
				    $table .= "<td>".$order->getId()."</td>";
				    $table .= "<td>".$order->getPlacedby()."</td>";
				    $table .= "<td>".$order->getDetails()."</td>";
				    $table .= "<td>".$order->getAddress()."</td>";
				    $table .= "<td>€".$order->getTotal()."</td>";
				    $table .= "<td>".$order->getStatus()."</td>";
				    $table .= '<td><button id="change'.$order->getId().'" orderid="'.$order->getId().'">Update</button></td>';
				    $table .= "</tr>";
				}
		    }
			
			$table .= "</tbody></table>";
		
					
			return new Response($table);
		}
		
		else if($type == 'managerorders') {
			
			$placedby = $session->get('placedby');
			
			$repository = $this->getDoctrine()->getRepository(Orders::class);
            $orders = $repository->findAll();
			
			$table = '<script>$("[id^=del").click(function(){
                      console.log("Test");
                      var oid = $(this).attr("orderid");               
	
				       $.post( "/backend", { type: "cancelOrder", oid: oid}) 
					 .done(function(data) {
				     alert(data);				
				     document.getElementById("vieworders").click(); //automatically clicks button to refresh this tab			
				    });    
			 	    });	
					</script>';
			
			$table .= '<table data-role="table"><thead><tr><th>Order ID</th><th>Customer</th><th>Details</th><th>Delivering To:</th><th>Total</th><th>Status</th></tr></tr></thead><tbody>';
			
			foreach($orders as $order) {
				$table .= "<tr>";
				$table .= "<td>".$order->getId()."</td>";
				$table .= "<td>".$order->getPlacedby()."</td>";
				$table .= "<td>".$order->getDetails()."</td>";
				$table .= "<td>".$order->getAddress()."</td>";
				$table .= "<td>€".$order->getTotal()."</td>";
				$table .= "<td>".$order->getStatus()."</td>";
				$table .= '<td><button id="del'.$order->getId().'" orderid="'.$order->getId().'">Update</button></td>';
				$table .= "</tr>";
		    }
			
			$table .= "</tbody></table>";
		
					
			return new Response($table);
		}
		
		else if($type == 'placeorder'){         
        
        // catch the variables we sent from the JavaScript.
        $placedby = $session->get('placedby');
        $ser = $request->request->get('ser', 'this is the default');
        $address = $request->request->get('address', 'this is the default');
		$total = $request->request->get('total');
		$status = $request->request->get('status', 'this is the default');
      
      
      
      
        // Break apart the serialized order
	    $ser = substr($ser, 0, -1);
        $data = explode('=', $ser); //this is what order details look like
        
		
		$details = '';
        foreach($data as $record) {  
			$item = explode('-', $record);	
            
                $details .= "<p>".$item[0] . " x " . $item[1] . "</p>";       
            
        }
    
        
        // to work the objects
        $entityManager = $this->getDoctrine()->getManager();

        // create blank entity of type "Orders"
        $order = new Orders();
        
        $order->setPlacedBy($placedby);
        //$order->setDetails(substr($details, 0, -1));
		$order->setDetails($details);		//knocks last = sign off the end of description
		$order->setAddress($address);
		$order->setTotal($total);
		$order->setStatus($status);


      
        $entityManager->persist($order);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
 

       
        return new Response("Order placed successfully");  
		
		}		
        
		else if($type == 'updateStatus') {
			$oid = $request->request->get('oid', 'none'); 
			
			$entityManager = $this->getDoctrine()->getManager();
			
			$repo = $this->getDoctrine()->getRepository(Orders::class);
            $currentid = $repo->findOneBy(['id' => $oid]);
			
			$testing = $currentid->getStatus();
			
			if($testing == "Awaiting Delivery") {
				$currentid->setStatus("Delivered");
			}
			else {
				$currentid->setStatus("Awaiting Delivery");
			}
			
			$entityManager->flush();
			
			return new Response("Delivery updated!"); 
		}
       
	   else if($type == 'cancelOrder') {
			$oid = $request->request->get('oid', 'none'); 
			
			$entityManager = $this->getDoctrine()->getManager();
			
			$repo = $this->getDoctrine()->getRepository(Orders::class);
            $currentid = $repo->findOneBy(['id' => $oid]);
			
			$testing = $currentid->getStatus();
			
			if($testing == "Awaiting Delivery") {
				$currentid->setStatus("Cancelled");
			}
			else if ($testing == "Delivered"){
				$currentid->setStatus("Cancelled");
			}
			else {
				$currentid->setStatus("Awaiting Delivery");
			}
			
			$entityManager->flush();
			
			return new Response("Delivery updated!"); 
		}
		
		else if($type == "orderstats") {
			
			$repository = $this->getDoctrine()->getRepository(Orders::class);
            $orders = $repository->findAll();
			
			$counter = 0;
			$total = 0;
			
			foreach($orders as $o) {
				if($o->getStatus() != "Cancelled"){ //doesn't count cancelled orders
				    $counter = $counter + 1;
				    $total = $total + $o->getTotal();
				}
			}
			$stats = "Total orders placed: ".$counter." || Total revenue: €".$total;
			return new Response($stats);
		}    
	}	
}
?>