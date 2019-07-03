<?php
namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
use \DateTime;
use App\Service\FileUploader;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MainController extends AbstractController{


/**
* @Route("/", name="home")
* Page d'accueil du site
*/
public function home(){

    $tourRepo = $this->getDoctrine()->getRepository(Tour::class);

    // $tours = $tourRepo->findAll();
       
    // //  on récupère la liste de tous les voyages en BDD
    // $articleRepo = $this->getDoctrine()->getRepository(Tour::class);
    // $tours = $tourRepo->findOneById(2);
    // dump($tours);


    // On récupère la liste des voyages
    $tours = $tourRepo->findAll();

    // On récupère le nombre de voyages
    $max = count($tours) - 1;
    // Table des id aléatoires
    $randomId = [];
    // Table des tours aléatoires
    $randomTours = [];
    // Nombre de tours aléatoire désirés
    $nbRand = 4;
    if($max > $nbRand){
        // Selection aléatoire de $nbRand voyages
        // $nbRand*4 essais cars rand peut retouner plusieurs
        // fois le même nombre aléatoire
        for ($i=0;$i<$nbRand*4;$i++) {
            $rnd = rand(0, $max);
            // Si l'd aléatoire n'est pas déja dans le tableau
            // on l'ajoute
            if (!in_array($rnd, $randomId)) {
                $randomId[] = $rnd;
                dump($rnd);
                $randomTours[] = $tours[$rnd];
                // Si on a trouvé $nbRand id, on sort de la boucle
                if (count($randomTours) >= $nbRand)
                    break;
            }
        }
    }

    // conversion les centimes en euro
    $tours[0]->setPrice($tours[0]->getPrice()/100);
        
    // Afficher les vols d'une facon aleatoire :   $tours = $tourRepo->findOneById($tours.Id);

        return $this->render('home.html.twig', ['tours' => $tours]);              
}


/**
 * @Route("/personaliser-votre-voyage/", name="custom-travel")
 * Page de voyage personaliser
 */
public function customTravel(Request $request, Swift_Mailer $mailer, Recaptcha $recaptcha){

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
        $recaptchaCode = $request->request->get('g-recaptcha-response');
        
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

            if(!$recaptcha->isValid($recaptchaCode, $request->server->get('REMOTE_ADDR'))){
                $errors['captchaInvalid'] = true;
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
* @Route("/choisissez-votre-pays/{country}/", name="travel-list")
* Page d'accueil du site
*/
public function travelList($country){

    //  on récupère la liste des voyages d'un pays de la BDD
    $tourRepo = $this->getDoctrine()->getRepository(Tour::class);
    //  $country = $this->getDoctrine()->getRepository(Country::class);

    $countryRepo = $this->getDoctrine()->getRepository(Country::class);

    $country = $countryRepo->findOneByCountry($country);

    if($country){
        $tours = $tourRepo->findByCountry($country);  // faut creer une variable d'un pays precis
     
    
        return $this->render('travel-list.html.twig', ['tours' => $tours]);

    } else {
        throw new NotFoundHttpException();
    }

    
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

            $messagereceipt = (new Swift_Message('ASIADREAM: Votre mail a bien été receptionné'))
            ->setFrom('asiadream@gmail.com')
            ->setTo($email)
            ->setBody(
                $this->renderView('emails/receipt-email.html.twig',['content'=>$content, 'subject'=>$subject]),
                'text/html'
            )
            ->addPart(
                $this->renderView('emails/receipt-email.txt.twig', ['content'=>$content , 'subject'=>$subject]),
                'text/plain'
            )
            ;
            $mailer->send($messagereceipt);
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

     //  On récupère les detailsd'un voyage de la BDD
     $tourRepo = $this->getDoctrine()->getRepository(Tour::class);

     $tours = $tourRepo->findOneById('7');  
    //  dump($tours);
   
   
 
         return $this->render('travel-detail.html.twig', ['tour' => $tours]);
}


/**
* @Route("/donnez-votre-avis/", name="review")
* Page du contact du site
*/
public function review(Request $request){

    return $this->render('review.html.twig');

}


/**
* @Route("suivi-vol/{flightid}/", name="flight", defaults={"flightid" =  0})
* Page de suivi de vol (affichage d'une carte et positionnement gps d'un avion)
*/
public function flight ($flightid){

    return $this->render('flight.html.twig', ['flightid' => $flightid]);
}

/**
* @Route("ajout-voyage", name="travel-design")
* Page administrateur pour ajouter un voyage
*/
public function travelDesign(Request $request, FileUploader $fileUploader){
    // Taille maxi des fichiers images
    $maxSize = 200000;

    // Ce formulaire étant accessible uniquement en mode administrateur
    // il n'est pas nécéssaire d'utiliser de captcha
    // Si l'admin n'est pas connecté, on le redirige vers la page d'erreur 403
    $session = $this->get('session');
    if(!$session->has('account')){
        throw new AccessDeniedHttpException('Vous devez être connecté');
    }

    // Valeurs retournées par le fomumlaire ou vide
    $content = $request->getContent();

    // Tableau des noms donnés aux boutons submit pour les discriminer
    $butNames = array(
                "flight" => "addFlight",
                "accommodation" => "addAccomodation",
                "tour" => "addTour",
                "country" => "addCountry",
                "admin" => "addAdmin");

    // Tableau des pays
    $adminRepo = $this->getDoctrine()->getRepository(Country::class);
    $countries = $adminRepo->findAll();

    // Tableau des vols
    $adminRepo = $this->getDoctrine()->getRepository(Flight::class);
    $flights = $adminRepo->findAll();

    // Tableau des hébergements
    $adminRepo = $this->getDoctrine()->getRepository(Accommodation::class);
    $accommodations = $adminRepo->findAll();

    // On est appelé par le formulaire
    if($request->isMethod('POST')){
        // On discrimine le formulaire
        $form;
        // Cas des formulaires sans image
        if($content !== ""){
            foreach ($butNames as $key => $value){
                if (strstr($content, $value)){
                    $form = $key;
                    break;
                }
            }
        }
        // Cas du formulaire avec image
        else{
            $form = "tour";
        }

        // Traitement du fomulaire qui a effectué un post
        switch ($form) {
            // ============== Cas du formulaire flight ===========================================
            case array_keys($butNames)[0]:
                // Recuperation des données POST
                $companyName = $request->request->get('companyName');
                $flightNum = $request->request->get('flightnum');
                $deptAirp = $request->request->get('departureAirport');
                $arrvAirp = $request->request->get('arrivalAirport');
                $deptDate = $request->request->get('departureDate');
                $arrvDate = $request->request->get('arrivalDate');

                // Bloc des verification des champs
                if(!preg_match('#^.{1,50}$#', $companyName)){
                    $errors['companyNameInvalid']=true;
                }

                if(!preg_match('#^.{1,50}$#', $flightNum)){
                    $errors['flightNumInvalid'] = true;
                }

                if(!preg_match('#^.{1,150}$#', $deptAirp)){
                    $errors['departureAirportInvalid'] = true;
                }

                if(!preg_match('#^.{1,150}$#', $arrvAirp)){
                    $errors['arrivalAirportInvalid']=true;
                }

                if(!preg_match('#([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$#', $deptDate)){
                    $errors['departureDateInvalid']=true;
                }

                if(!preg_match('#^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$#', $arrvDate)){
                    $errors['arrivalDateInvalid']=true;
                }

                if($deptDate >= $arrvDate){
                    $errors['datesInvalid']=true;   
                }

                // Si aucune erreurs dans les données en entrée.
                if(!isset($errors)){
                    $adminRepo = $this->getDoctrine()->getRepository(Flight::class);
                    // Si aucun n° de vol identique n'existe, on l'ajoute
                    if(empty($adminRepo->findOneByFlightNumber($flightNum))){
                        // Création et hydratation de l'objet
                        $flight =  new Flight();
                        $flight->setCompanyName($companyName);
                        $flight->setFlightNumber($flightNum);
                        $flight->setDepartureAirport($deptAirp);
                        $flight->setArrivalAirport($arrvAirp);
                        $flight->setDepartureDate(new DateTime($deptDate));
                        $flight->setArrivalDate(new DateTime($arrvDate));
                        // Enregistrement en BDD
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($flight);
                        $em->flush();

                        return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                                    'countries' => $countries,
                                                                    'flights' => $flights,
                                                                    'accommodations' => $accommodations,
                                                                    'successFlight' => true));
                    }
                }

                // Si des erreurs ont été commises, alors retour de la page de ajout-voyage avec le tableau des erreurs.
                if(isset($errors)){
                    $errors['flightForm']=true;
                    return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                                        'countries' => $countries,
                                                                        'flights' => $flights,
                                                                        'accommodations' => $accommodations,
                                                                        'errorsList'=>$errors));
                }

            // ============== Cas du formulaire accommodation ====================================
            case array_keys($butNames)[1]:
                // Recuperation des données POST
                $name = $request->request->get('name');
                $phone = $request->request->get('phone');
                $email = $request->request->get('email');
                $address = $request->request->get('address');
                $country = $request->request->get('country');

                // Bloc des verification des champs
                if(!preg_match('#^.{1,50}$#', $name)){
                    $errors['nameInvalid']=true;
                }

                if(!filter_var($phone, FILTER_SANITIZE_NUMBER_INT)){
                    $errors['phoneInvalid'] = true;
                }

                if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $errors['emailInvalid'] = true;
                }

                if(!preg_match('#^.{1,100}$#', $address)){
                    $errors['addressInvalid']=true;
                }

                if(!preg_match('#^.{1,50}$#', $country)){
                    $errors['countryInvalid']=true;
                }

                // Si aucune erreurs dans les données en entrée.
                if(!isset($errors)){
                    $adminRepo = $this->getDoctrine()->getRepository(Accommodation::class);
                    // Si aucun hébergement avec le même nom et email n'existe, on l'ajoute
                    if(empty($adminRepo->findOneByEmail($email)) and empty($adminRepo->findOneByName($name))){
                        // Création et hydratation de l'objet
                        $accommodation =  new Accommodation();
                        $accommodation->setName($name);
                        $accommodation->setPhone($phone);
                        $accommodation->setEmail($email);
                        $accommodation->setAdress($address);
                        $accommodation->setCountry($country);
                        // Enregistrement en BDD
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($accommodation);
                        $em->flush();

                        return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                                    'countries' => $countries,
                                                                    'flights' => $flights,
                                                                    'accommodations' => $accommodations,
                                                                    'successAccommodation' => true));
                    }
                }

                // Si des erreurs ont été commises, alors retour de la page de ajout-voyage avec le tableau des erreurs.
                if(isset($errors)){
                    $errors['accommodationForm']=true;
                    return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                                        'countries' => $countries,
                                                                        'flights' => $flights,
                                                                        'accommodations' => $accommodations,
                                                                        'errorsList'=>$errors));
                }

            // ============== Cas du formulaire tour ============================================
            case array_keys($butNames)[2]:
                // Recuperation des données POST
                $title = $request->request->get('title');
                $description = $request->request->get('description');
                $deptDate = $request->request->get('departureDate');
                $arrvDate = $request->request->get('arrivalDate');
                $group = $request->request->get('group');
                $country = $request->request->get('country');
                $flight = $request->request->get('flight');
                $accommodation = $request->request->get('accommodation');
                $price = $request->request->get('price');
                $inputFile = $request->files->get('inputFile');

                // Bloc des verification des champs
                if(!preg_match('#^.{1,50}$#', $title)){
                    $errors['titleInvalid']=true;
                }

                if(!preg_match('#^.{1,65535}$#', $description)){
                    $errors['descriptionInvalid'] = true;
                }

                if(!preg_match('#([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$#', $deptDate)){
                    $errors['departureDateInvalid']=true;
                }

                if(!preg_match('#^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$#', $arrvDate)){
                    $errors['arrivalDateInvalid']=true;
                }

                if($group !== null && $group !== "on"){
                    $errors['groupInvalid'] = true;
                }

                if(!preg_match('#^.{1,50}$#', $country)){
                    $errors['countryInvalid']=true;
                }

                if($flight === ""){
                    $errors['flightInvalid']=true;
                }

                if($accommodation === ""){
                    $errors['accommodationInvalid']=true;
                }

                if(!preg_match('#^.{1,50}$#', $country)){
                    $errors['countryInvalid']=true;
                }

                if(!preg_match('#^.{1,150}$#', $price)){
                    $errors['priceInvalid'] = true;
                }

                if($inputFile == null){
                    $errors['inputFileInvalid']=true;
                }

                // Si aucune erreur dans les données en entrée.
                if(!isset($errors)){
                    // Test mime, taille et copie du fichier
                    $fileName = $fileUploader->upload($request->files->get('inputFile'), $maxSize);
                    if($fileName == null){
                        $errors['inputFileError']=true;
                    }
                    // Si le fichier est conforme et à été copié sans erreur
                    else{
                        $adminRepo = $this->getDoctrine()->getRepository(Tour::class);
                        // Si aucun titre de voyage identique n'existe, on l'ajoute
                        if(empty($adminRepo->findOneByTitle($title))){
                            $countryRepo = $this->getDoctrine()->getRepository(Country::class);
                            $countryObj = $countryRepo->findOneByCountry($country);
                            $flightRepo = $this->getDoctrine()->getRepository(Flight::class);
                            $flightObj = $flightRepo->findOneByFlightNumber($flight);
                            $flightAccommod = $this->getDoctrine()->getRepository(Accommodation::class);
                            $accommodObj = $flightAccommod->findOneByName($accommodation);
                            // Création et hydratation de l'objet
                            $tour =  new Tour();
                            $tour->setTitle($title);
                            $tour->setDescription($description);
                            $tour->setDepartureDate(new DateTime($deptDate));
                            $tour->setArrivalDate(new DateTime($arrvDate));
                            $tour->setTravelerGroup(intval($group));
                            $tour->setCountry($countryObj);
                            $tour->addFlight($flightObj);
    //                        $tour->removeFlight($flight);
                            $tour->addAccommodation($accommodObj);
    //                        $tour->removeAccommodation($accomodation);
                            $tour->setPrice($price);
                            $tour->setImage($fileName);
                            // Enregistrement en BDD
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($tour);
                            $em->flush();
                            return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                                                'countries' => $countries,
                                                                                'flights' => $flights,
                                                                                'accommodations' => $accommodations,
                                                                                'successTour' => true));
                        }
                    }
                }

                // Si des erreurs ont été commises, alors retour de la page de ajout-voyage avec le tableau des erreurs.
                if(isset($errors)){
                    $errors['tourForm']=true;
                    return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                                        'countries' => $countries,
                                                                        'flights' => $flights,
                                                                        'accommodations' => $accommodations,
                                                                        'maxSize' => $maxSize,
                                                                        'errorsList'=>$errors));
                }

            // ============== Cas du formulaire country ==========================================
            case array_keys($butNames)[3]:
                // Recuperation des données POST
                $country = $request->request->get('country');

                // Bloc des verification des champs
                if(!preg_match('#^.{1,50}$#', $country)){
                    $errors['countryInvalid']=true;
                }

                // Si aucune erreurs dans les données en entrée.
                if(!isset($errors)){
                    $adminRepo = $this->getDoctrine()->getRepository(Country::class);
                    // Si le pays n'est pas déja en base de données, alors on l'ajoute
                    if(empty($adminRepo->findOneByCountry($country))){
                        // Création et hydratation de l'objet
                        $countryObj =  new Country();
                        $countryObj->setCountry($country);
                        // Enregistrement en BDD
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($countryObj);
                        $em->flush();
                        // Retour de la page travel-design avec le tableau contenant le message de succès d'ajout d'un pays
                        return $this->render('travel-design.html.twig', array('successCountry' => true,
                                                                        'countries' => $countries,
                                                                        'flights' => $flights,
                                                                        'accommodations' => $accommodations,
                                                                        'button' => $butNames));
                    }
                    else{
                        $errors['countryAlready'] = true;
                    }
                }
                // Si des erreurs ont été commises, alors retour de la page de ajout-voyage avec le tableau des erreurs.
                if(isset($errors)){
                    $errors['countryForm'] = true;
                    return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                                        'countries' => $countries,
                                                                        'flights' => $flights,
                                                                        'accommodations' => $accommodations,
                                                                        'errorsList'=>$errors));
                }

            // ============== Cas du formulaire admin ============================================
            case array_keys($butNames)[4]:
                // Recuperation des données POST
                $email = $request->request->get('email');
                $password = $request->request->get('password');
                $confirm = $request->request->get('confirm');

                //bloc des vérifs
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $errors['invalidEmail'] = true;
                }

                if(!preg_match('#^.{8,300}$#', $password)){
                    $errors['invalidPassword'] = true;
                }
                
                if($password !== $confirm){
                    $errors['confirmInvalid'] = true;
                }
                //Si pas d'erreurs
                if(!isset($errors)){

                    //Recherche l'admin avec son adresse email dans la BDD
                    $adminRepo = $this->getDoctrine()->getRepository(Admin::class);
                    $admin = $adminRepo->findOneByEmail($email);
                    
                    //Si l'admin n'a pas été trouvé on peut l'ajoutter
                    if(empty($admin)){
                        // Création et hydratation de l'objet
                        $adminObj =  new Admin();
                        $adminObj->setEmail($email);
                        $adminObj->setPassword(password_hash($password, PASSWORD_BCRYPT));

                        // Enregistrement en BDD
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($adminObj);
                        $em->flush();
                        // Retour de la page travel-design avec le tableau contenant le message de succès d'ajout d'un pays
                        return $this->render('travel-design.html.twig', array('successAdmin' => true,
                                                                        'countries' => $countries,
                                                                        'flights' => $flights,
                                                                        'accommodations' => $accommodations,
                                                                        'button' => $butNames));
                    //Si admin existe déja alors erreur
                    }else{
                        $errors['emailAlready'] = true;
                    }
                }

                // Si des erreurs ont été commises, alors retour de la page de ajout-voyage avec le tableau des erreurs.
                if(isset($errors)){
                    $errors['adminForm'] = true;
                    return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                                        'countries' => $countries,
                                                                        'flights' => $flights,
                                                                        'accommodations' => $accommodations,
                                                                        'errorsList'=>$errors));
                }
        }
    }

    // On est pas appelé par le formulaire
    return $this->render('travel-design.html.twig', array('button' => $butNames,
                                                        'countries' => $countries,
                                                        'flights' => $flights,
                                                        'accommodations' => $accommodations));
}

/**
 * @Route("/achat-voyage/", name="order")
 * Page d'achat d'un voyage 
 */
public function order(Request $request){
        
        //Si le formulaire a été cliqué
        if($request->isMethod('POST')){
            //recuperation des données POST
            $participate = $request->request->get('participate');
            $groupNbr = $request->request->get('groupNbr');
            $civility = $request->request->get('civility');
            $lastname = $request->request->get('lastname');
            $firstname = $request->request->get('firstname');
            $country = $request->request->get('country');
            $postCode = $request->request->get('postCode');
            $city = $request->request->get('city');
            $adress = $request->request->get('adress');
            $email = $request->request->get('email');
            $confirmEmail = $request->request->get('confirmEmail');
            $phone1 = $request->request->get('phone1');
            $phone2 = $request->request->get('phone2');
            //Données du voyage sélectionner afficher sur la page même en cas d'erreur de remplissage du formulaire
            $tourRepo = $this->getDoctrine()->getRepository(Tour::class);
            $flightRepo = $this->getDoctrine()->getRepository(Flight::class);
            $flight = $flightRepo->findOneById(1);
            $tour = $tourRepo->findOneById(1);

            //bloc des vérifs
            if(is_bool($participate)){
                $errors['invalidParticipate'] = true; 
            }

            if(empty($groupNbr)){
                $errors['invalidGroupNbr'] = true;
            }
    
            if(empty($civility)){
                $errors['invalidCivility'] = true;
            }
            if(!preg_match('#^.{2,50}$#', $lastname)){
                $errors['invalidLastname'] = true; 
            }
            if(!preg_match('#^.{2,50}$#', $firstname)){
                $errors['invalidfirstname'] = true; 
            }
            if(!preg_match('#^.{2,50}$#', $country)){
                $errors['invalidCountry'] = true; 
            }
            if(!preg_match('#^[0-9]{5}$#', $postCode)){
                $errors['invalidPostCode'] = true; 
            }
            if(!preg_match('#^.{2,200}$#', $city)){
                $errors['invalidCity'] = true; 
            }
            if(!preg_match('#^.{2,200}$#', $adress)){
                $errors['invalidAdress'] = true; 
            }
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors['invalidEmail'] = true;
            }
            if($email != $confirmEmail){
                $errors['invalidConfirmEmail'] = true;
            }
            if(!preg_match('#^[0-9]{10}$#', $phone1)){
                $errors['invalidPhone1'] = true;
            }
            if(!preg_match('#^[0-9]{10}$#', $phone2)){
                $errors['invalidPhone2'] = true;
            }
            //Si erreur
            if(isset($errors)){
                //retour de la vu avec les erreurs
                return $this->render('order.html.twig', ['errorsList'=>$errors, 'tour' => $tour, 'flight' => $flight]);
            //Sinon  
            }else{
                //Création d'un nouveau client dans la BDD
                $clientRepo = $this->getDoctrine()->getRepository(Client::class);
                $newClient = new Client();
                $newClient
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setGender($civility)
                ->setPhone($phone1)
                ->setMobile($phone2)
                ->setEmail($email)
                ->setAdress($adress)
                ->setParticipate($participate)
                ->setBirthday(new DateTime())
                ->setTravelerNumber($groupNbr)
                ;
                
                //Envoyer les données en BDD
                $em = $this->getDoctrine()->getManager();
                $em->persist($newClient);
                $em->flush();

                //Création d'une session pour l'envoie de l'email de confirmation pour la commande
                $session = $this->get('session');
                //Ajout de variables dans la session
                $session->set('clientEmail', $email);
                $session->set('clientFirstname', $firstname);
                $session->set('clientLastname', $lastname);
                $session->set('clientCivility', $civility);
                $session->set('clientAdress', $adress);
                $session->set('clientParticipate', $participate);
                $session->set('clientGroupNbr', $groupNbr);
                $session->set('tour', $tour);
                $session->set('flight', $flight);

                //Retour de la vu avec les données renseigner pour confirmation
                return $this->render('order.html.twig', ['success'=> true, 'tour' => $tour, 'flight' => $flight, 'newClient' => $newClient]); 
            }
        }else{
            //Données du voyage sélectionner afficher sur la page par défaut
            $tourRepo = $this->getDoctrine()->getRepository(Tour::class);
            $flightRepo = $this->getDoctrine()->getRepository(Flight::class);
            $flight = $flightRepo->findOneById(1);
            $tour = $tourRepo->findOneById(1);
        
            //Retour de la vu par défaut
            return $this->render('order.html.twig', ['tour' => $tour, 'flight' => $flight]);
        }
}

/**
 * @Route("/donner-banquaire/", name="bankData")
 * Page infos bancaire
 */
public function bankData(){
     //Retour de la vu 
    return $this->render('bankData.html.twig');
}

/** 
 * @Route("/confirmation-commande/", name="confirmOrder")
 * Page confirmation de commande
 */
public function confirmOrder(Swift_Mailer $mailer){
    
    //Récuperation de l'objet session
    $session = $this->get('session');

    //Récuperation des variables
    $clientEmail = $session->get('clientEmail');
    $clientFirstname = $session->get('clientFirstname');
    $clientLastname = $session->get('clientLastname');
    $clientCivility = $session->get('clientCivility');
    $clientAdress = $session->get('clientAdress');
    $clientParticipate = $session->get('clientParticipate');
    $clientGroupNbr = $session->get('clientGroupNbr');
    $clientTour = $session->get('tour');
    $clientFlight = $session->get('flight');

    //Crétion de l'email de confirmation
    $message = (new Swift_Message('Confirmation et récapitulatif de votre commande'))
                ->setFrom($clientEmail)
                ->setTo('monsite@gmail.com')
                ->setBody(
                    $this->renderView('emails/order-email.html.twig',['clientEmail'=>$clientEmail, 'clientFirstname' => $clientFirstname, 'clientLastname' => $clientLastname, 'clientCivility' => $clientCivility, 'clientAdress' => $clientAdress, 'clientParticipate' => $clientParticipate, 'clientGroupNbr' => $clientGroupNbr, 'tour' => $clientTour, 'flight' => $clientFlight]),
                    'text/html'
                )
                ->addPart(
                    $this->renderView('emails/order-email.txt.twig',['clientEmail'=>$clientEmail, 'clientFirstname' => $clientFirstname, 'clientLastname' => $clientLastname, 'clientCivility' => $clientCivility, 'clientAdress' => $clientAdress, 'clientParticipate' => $clientParticipate, 'clientGroupNbr' => $clientGroupNbr, 'tour' => $clientTour, 'flight' => $clientFlight]),
                    'text/plain'
                )
                ;
    //Envoie de l'email
    $mailer->send($message);
    //Destruction de toute les variables dans la session
    $session->clear();
    //Retour de la vu            
    return $this->render('confirmOrder.html.twig');
}
}
