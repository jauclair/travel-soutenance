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
    * @Route("accueil", name="home")
    * Page d'accueil du site
    */
    public function home(){
      

        return $this->render('home.html.twig');
    }

    /**
    * @Route("personnalisez-votre-voyage", name="custom-travel")
    * Page formulaire de demande de voyage personnalisé
    */
    public function customTravel(){

        return $this->render('custom-travel.html.twig');
    }

    /**
    * @Route("choisissez-votre-voyage", name="travel-list")
    * Page d'accueil du site
    */
    public function travelList(){

        return $this->render('travel-list.html.twig');
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
    * @Route("connectez-vous", name="connection")
    * Page du connexion administrateur
    */
    public function connection(){

        return $this->render('connection.html.twig');
    }

    /**
    * @Route("sejour", name="travel-detail")
    * Page d'affichge du détail d'un voyage
    */
    public function travelDetail(){

        return $this->render('travel-detail.html.twig');
    }

    /**
    * @Route("donnez-votre-avis", name="review")
    * Page permettant de donner un avis sur un voyage
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