<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Quizz;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Repository\CategorieRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizzRepository;
use App\Repository\ReponseRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;

class QuizzController extends AbstractController
{   
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){

        $this->entityManager = $entityManager;
        
    }

    #[Route('/quiz', name: 'app_quizz')]
    public function index(Request $request): Response
    {  
         if($this->getUser() == null){

        return $this->render('security/login.html.twig', [
            'controller_name' => 'AccueilController',
            'request' => $request,
            'this' => $this,
            'error' => false
        ]);
        }

        if($this->entityManager->getRepository(Users::class)->findOneBy(['isVerified' => 1,'id' => $this->getUser()->getId() ]) == true){

            return $this->render('quizz/index.html.twig', [
                'controller_name' => 'QuizzController',
                'request' => $request->request->all(),
            ]);

        } else {

            return $this->render('security/email.html.twig', [
                'controller_name' => 'ProfilController',
                'email'=>$this->getUser()->getEmail(),
                'password'=>$this->getUser()->getPassword(),
                'request' => $request,
                'this' => $this,
                'error' => false
            ]);
        }

    }

    #[Route('/quiz/resolve/{idQuiz}-{idQuestion}-{try}', name: 'app_quizz_resolve')]
    public function resolve($idQuiz, $idQuestion, $try, QuizzRepository $quizzRepository, QuestionRepository $questionRepository, ReponseRepository $reponseRepository, Request $request): Response
    {   
        $objQuiz = $quizzRepository->findBy(["id" => $idQuiz])[0]; // Quiz actuel 
        $objQuestion = $questionRepository->findBy(['quizz' => $objQuiz]); // Question actuel
        $objReponse = $reponseRepository->findBy(['question' => $objQuestion[$idQuestion -1]]); // Reponse actuel
        $reponse_array_fetch = []; // Tableau avec les reponse possible de la question actuelle
        $session = $request->getSession(); // Variable de la session permetant d'enregistrer l'historique 
        
        $result_flip = array_flip($request->request->all());

        $biggest_try =  0;
        if($idQuestion <= 2 && isset($result_flip['on']) ){
            
            // Empeche l'ecrasement de donné si on effectue le meme Quiz
            $patern = "/id_quiz_".$idQuiz."_try_\d/";
            foreach($session->all() as $key_session => $value_session){
                if ( preg_match($patern, $key_session, $matches)){
                    foreach($matches as $value_matches){
                        $explode = explode("id_quiz_".$idQuiz."_try_", $value_matches);
                        if($biggest_try <= intval($explode[1])){
                            $try = intval($explode[1]) + 1;
                        }
                    }
                }
            }
            // Recupere le resultat de la question precedente true ===  1 AND false ==== 0
            $previously_response_result  = $reponseRepository->findBy(['reponse'=>  trim(str_replace('_', " ", $result_flip['on']) ) ])[0]->getReponseExpected();
            
            // Crée une attribut de session valide (non existant)
            $session->set('id_quiz_' . $idQuiz .'_try_' . $try , $previously_response_result);
        }
        elseif($idQuestion > 2 && isset($result_flip['on']) ){

            // Recupere le resultat de la question precedente true ===  1 AND false ==== 0
            $previously_response_result  = $reponseRepository->findBy(['reponse'=>  trim (str_replace('_', " ", $result_flip['on'])) ])[0]->getReponseExpected();
            
            // Additione le score du quiz  du total 
            $session->set('id_quiz_' . $idQuiz .'_try_' . $try , $session->get('id_quiz_' . $idQuiz .'_try_' . $try ) + $previously_response_result);
        }

        // Remplis le tableau $reponse_array_fetch avec les reponse de la Question actue
        foreach ($objReponse as $key => $value){
            array_push ($reponse_array_fetch , $value->getReponse());
        }
        // Melange le tableau $reponse_array_fetch
        shuffle($reponse_array_fetch);

        return $this->render('quizz/resolve.html.twig', [
            'controller_name' => 'QuizzController',
            'history_array' => $session->all(), 
            'idQuiz' => $idQuiz, 
            "user"=> $this->getUser(),
            'quiz_name' => $objQuiz->getName(),
            'question_name' => $objQuestion[$idQuestion -1]->getQuestion(),
            'question_number' => $idQuestion,
            'count_question' => count($objQuestion),
            'reponse_fetch' => $reponse_array_fetch,
            'try' => $try,
        ]);
    }

    
    #[Route('/quiz/create', name: 'app_quizz_create')]
    public function create(Request $request, ManagerRegistry $doctrine, CategorieRepository $categorieRepository): Response
    {   
        
        $idQuestionQuiz = 0; // Version string du tableau $arrayIdQuestion

        $entityManager = $doctrine->getManager();

        // Initialisation de variable
        $categorie = $request->request->get('nameCategorieSetting'); // Nom de la categorie
        $nameQuiz = $request->request->get('nameQuizzSetting'); // Nom du Quiz
        $idUser = $this->getUser()->getId(); // Id de l'utilisateur connecter

        $categorieCheck = $categorieRepository->findBy(['name' => $categorie]); // verifie si la categorie existe dejà

        // Crée une nouvelle catégorie si elle n'existe pas
        if(empty($categorieCheck)){
            $objCategorie = new Categorie();
            $objCategorie->setName(trim($categorie));

            $entityManager->persist($objCategorie);
            
            $entityManager->flush();
            
            $idCategorie = $objCategorie->getId(); // Id de la nouvelle catégorie
        }
        else{   
            $idCategorie = $categorieCheck[0]->getId(); // Id de la catégorie
            $objCategorie =  $categorieCheck[0];
        } 

        // Création du Quiz

        $objQuiz = new Quizz(); 
        $objQuiz->setName(trim($nameQuiz));
        $objQuiz->setIdQuestion($idQuestionQuiz);
        $objQuiz->setUser($this->getUser());
        
        $objQuiz->setCreatedAt(new DateTime());
        $entityManager->persist($objQuiz); 
        $entityManager->flush();
        
        // $lastObj = new Quizz();
        // $lastObj->setId($objQuiz->getId());

        // Boucle le tableau avec toute les questions et reponse, integrant les question dans la db et completant le tableau $arrayIdQuestion
        foreach($request->request->all() as $key => $value){
            if(preg_match( '/question{1}\d{1,}/', $key )){
                // Création d'une entrée de question 
                $objQuestion = new Question();
                $objQuestion->setQuestion($value);
                $objQuestion->setCategorie($objCategorie);
                $objQuestion->setQuizz($objQuiz);
                $objQuestion->setUser($this->getUser());

                $entityManager->persist($objQuestion); 
                $entityManager->flush();

                $objQuiz->addQuestion($objQuestion);
                $entityManager->persist($objQuiz); 

                // Sauvegarder l'id de la question dans la variable $IdQuestion et push dans le tableau $arrayIdQuestion
                $idQuestion = $objQuestion->getId();
                // array_push($arrayIdQuestion, $idQuestion);
            }
            elseif((preg_match( '/[a-zA-Z]{1,}_response{1}\d{1,}/', $key ) )) {
                // Création d'une entrée de reponse
                $objReponse = new Reponse();
                $objReponse->setIdQuestion($idQuestion);
                $objReponse->setQuestion($objQuestion);
                $objReponse->setReponse(trim($value));
                $objReponse->setUser($this->getUser());

                preg_match('/^true_[a-zA-Z]{1,}/', $key) ? $a  = 1 : $a = 0;
                $objReponse->setReponseExpected($a);
                      
                $entityManager->persist($objReponse); 
                $entityManager->flush();
            }
            
            
            
        }
        $entityManager->flush();

        return $this->render('quizz/create.html.twig', [
            'controller_name' => 'QuizzController',
            'quiz_name' => $objQuiz->getName(),
            'name_user' => $this->getUser()->getEmail(),
        ]);
    }
}