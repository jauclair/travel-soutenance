<?php
namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Entity\Accommodation;
use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Country;
use App\Entity\Flight;
use App\Entity\Order;
use App\Entity\Tour;
use \Swift_Mailer;
use \Swift_Message;
use App\Service\Recaptcha;


class MainController extends AbstractController{
/**
* @Route("/", name="home")
* Page d'accueil du site
*/
public function home(){
    

    return $this->render('home.html.twig');
}

/**
 * @Route("/personaliser-votre-voyage/", name="custom-travel")
 * Page de voyage personaliser
 */
public function customTravel(Request $request, Swift_Mailer $mailer){

    //Si le formulaire a été cliqué
    if($request->isMethod('POST')){
    
    //récuperation des données POST du formulaire
    $email = $request->request->get('email');
    $country = $request->request->get('country');
    $departureDate = $request->request->get('departureDate');
    $returnDate = $request->request->get('returnDate');
    $groupNbr = $request->request->get('groupNbr');
    $minPrice = $request->request->get('minPrice');
    $maxPrice = $request->request->get('maxPrice');
    $message = $request->request->get('message');
    
        //Bloc des vérifs
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors['invalidEmail'] = true;
        }
        
        if(empty($country)){
            $errors['invalidCountry'] = true;
        }

        if(empty($departureDate)){
            $errors['invalidDepartureDate'] = true;
        }

        if(empty($returnDate)){
            $errors['invalidReturnDate'] = true;
        }

        if(empty($groupNbr)){
            $errors['invalidGroupNbr'] = true;
        }
        
        if(!preg_match('#^[0-9]+(\.[0-9]{2})?$#', $minPrice)){
            $errors['invalidMinPrice'] = true;
        }

        if(!preg_match('#^\d{2,10}(?:\.\d{1,4})?$#', $maxPrice)){
            $errors['invalidMaxPrice'] = true;
        }

        if(!preg_match('#^.{0,8000}$#', $message)){
            $errors['invalideMessage'] = true;
        }
        
        //Si erreur
        if(isset($errors)){
            //retour a la page custom-travel avec les erreurs 
            return $this->render('custom-travel.html.twig', array('errorsList' => $errors));
        
        //Sinon envoie du msg
        }else{    
            //Création du msg de la demande de séjour 
            //msg avec HTML
            $message = (new Swift_Message('Demande de séjour personalisé'))
                ->setFrom($email)
                ->setTo('admin@monsite.com')
                ->setBody(
                    $this->renderView('emails/custom-email.html.twig', ['country' => $country,'departureDate' => $departureDate,'returnDate' => $returnDate,'groupNbr' => $groupNbr,'minPrice' => $minPrice,'maxPrice' => $maxPrice,'message' => $message] ),
                    'text/html'
                )
                //msg TXT
                ->addPart(
                    $this->renderView('emails/custom-email.txt.twig', ['country' => $country,'departureDate' => $departureDate,'returnDate' => $returnDate,'groupNbr' => $groupNbr,'minPrice' => $minPrice,'maxPrice' => $maxPrice,'message' => $message]),
                    'text/plain'
                )
            ;
            //Envoie du msg a l'admin
            $mailer->send($message);
            //retour de la page avec msg de succès
            return $this->render('custom-travel.html.twig', array('success' => true));
        }
      
    } 
    //page par défaut 
    return $this->render('custom-travel.html.twig');
}

/**
* @Route("/choisissez-votre-pays/", name="travel-list")
* Page d'accueil du site
*/
public function travelList(){

    return $this->render('travel-list.html.twig');
}


/**
* @Route("/connectez-vous/", name="connection")
* Page de connexion admin
*/
public function connection(Request $request){

//Si l'admin est déjà connectée, on le redirige vers la page d'ajout de séjour
$session = $this->get('session');
if($session->has('account')){
    return $this->redirectToRoute('travel-design');
}

//Si le formulaire a été cliqué
if($request->isMethod('POST')){

    //Recupération des données du formulaire avec l'objet $request
    $email = $request->request->get('email');
    $password = $request->request->get('password');

    //bloc des vérifs
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors['invalidEmail'] = true;
    }

    if(!preg_match('#^.{8,300}$#', $password)){
        $errors['invalidPassword'] = true;
    }
    
    //Si pas d'erreurs
    if(!isset($errors)){

        //Recherche l'admin avec son adresse email dans la BDD
        $adminRepo = $this->getDoctrine()->getRepository(Admin::class);
         $admin = $adminRepo->findOneByEmail($email);
        
        //Si l'admin a été trouvé, c'est que le compte existe
         if(!empty($admin)){

            //Vérif si le mot de passe est bon 
          if(password_verify($password, $admin->getPassword())){

                //Connexion de l'admin
                $session->set('account', $admin);

                //Si connexion réussit, charge de la vue avec msg succès
                return $this->render('connection.html.twig', array('success' => true));
            
            //Si mauvais mot de passe alors erreur
            }else{
                $errors['badPassword'] = true;
            }
        
        //Si admin n'existe pas alors erreur
        }else{
            $errors['notExist'] = true;
        }
    }

}

    //Si erreurs, charge de la vue en lui envoyant ces erreurs en paramètre
    if(isset($errors)){
        return $this->render('connection.html.twig', array('errorsList' => $errors));
    }

    //Chargement de la vue par défaut (si pas d'erreurs et pas succès)
    return $this->render('connection.html.twig');
}

/**
 * @Route("/deconnexion/", name="logout")
 * Page de déconnexion
 */
public function logout(){

    //Si l'admin n'est pas connecté, on le redirige vers la page de connexion
    $session = $this->get('session');
    if(!$session->has('account')){
        return $this->redirectToRoute('connection');
    }

    //Suppression de la variable account ce qui provoque la déconnexion de l'admin
    $session->remove('account');

    //Appel de la vue déconnexion qui affiche un msg indiquant la réussite de la déconnexion
    return $this->render('logout.html.twig');

}

/**
* @Route("contactez-nous", name="contact")
* Page du contact du site
*/
public function contact(Request $request, Swift_Mailer $mailer, Recaptcha $recaptcha){
    

    if($request->isMethod('POST')){

        //recuperation des données POST
        $email = $request->request->get('email');
        $subject = $request->request->get('subject');
        $content = $request->request->get('message');
        $recaptchaCode = $request->request->get('g-recaptcha-response');

        //bloc des verification des champs
        if(!filter_var($email , FILTER_VALIDATE_EMAIL)){
            $errors['emailInvalid'] = true;
        }
        if(!preg_match('#^.{5,120}$#', $subject)){
            $errors['subjectInvalid']=true;
        }
        if(!preg_match('#^.{5,4000}$#', $content)){
            $errors['messageInvalid']=true; 
        }
        if(!$recaptcha->isValid($recaptchaCode, $request->server->get('REMOTE_ADDR'))){
            $errors['captchaInvalid'] = true;
        }
        //Si des erreurs ont été commises, alors retour de la page de contact avec le tableau des erreurs.
        if(isset($errors)){
            return $this->render('contact.html.twig', array('errorsList'=>$errors));
        }else{
            //Si il n'y a pas d'erreurs, alors envoi du mail à l'adresse mail du site... 
            $message = (new Swift_Message('Formulaire de contact venant du site'))
                ->setFrom($email)
                ->setTo('monsite@gmail.com')
                ->setBody(
                    $this->renderView('emails/contact-email.html.twig',['content'=>$content, 'subject'=>$subject]),
                    'text/html'
                )
                ->addPart(
                    $this->renderView('emails/contact-email.txt.twig', ['content'=>$content , 'subject'=>$subject]),
                    'text/plain'
                )
            ;
            $mailer->send($message);
            //retour de la page contact avec le tableau contenant le message de succès d'envoi de mail
            return $this->render('contact.html.twig', array('success' => true));
        }  
    }
    
    return $this->render('contact.html.twig');
}

/**
* @Route("/sejour/", name="travelDetail")
* Page du contact du site
*/
public function travelDetail(){

    return $this->render('travel-detail.html.twig');
}

/**
* @Route("/donnez-votre-avis/", name="review")
* Page du contact du site
*/
public function review(){

    return $this->render('review.html.twig');
}


/**
* @Route("suivi-vol", name="flight ")
* Page de suivi de vol (affichage d'une carte et positionnement gps d'un avion)
*/
public function flight (){

    return $this->render('flight.html.twig');
}

/**
* @Route("ajout-voyage", name="travel-design")
* Page administrateur pour ajouter un voyage
*/
public function travelDesign(){

    return $this->render('travel-design.html.twig');
}

}