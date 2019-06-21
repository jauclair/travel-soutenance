<?php
namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Entity\Tour;


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
    public function contact(){

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