<?php

namespace App\Controller;

use App\Entity\History;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Repository\CategorieRepository;
use App\Repository\HistoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizzRepository;
use App\Repository\ReponseRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class HistoryController extends AbstractController
{

    #[Route('/history', name: 'app_history')]
    public function index(Request $request, HistoryRepository $historyRepository, QuizzRepository $quizzRepository, QuestionRepository $questionRepository , ReponseRepository $reponseRepository, ManagerRegistry $doctrine): Response
    {   
        $session = $request->getSession(); // Variable de la session permetant d'enregistrer l'historique 
        $entityManager = $doctrine->getManager();
        $history_array = [];
        // $session->remove('id_quiz_3_try_1');
        
        // Condition qui verifie si le user  === Guest 
        $test = [];
        foreach($session->all() as $key => $value){
            if( preg_match( "/id_quiz_\d_try_\d/", $key, $matche) ){
                array_push( $test, $matche[0]);
            }
        }

        // Remplis le tableau $history_array et enregistre les donnés des utilisateur connecter 
        foreach($test as $value){
            
            $result_quiz = $session->get($value);
            $value_explode = explode("_", $value);
            
            // Quiz actuel 
            $objQuiz = $quizzRepository->findBy(['id' => intval($value_explode[2]) ])[0];

            // Nom du Quiz
            $name_quiz = $objQuiz->getName();
            // Categorie du Quiz
            $count_question = count($questionRepository->findBy(['quizz' => $objQuiz]));
            $categorieName = $questionRepository->findBy(['quizz' => $objQuiz], null , 1)[0]->getCategorie()->getName() ;               
            // Création d'une represantation du en tableau "Objet"
            $history_array[$name_quiz . "_try_" . $value_explode[4]] =  [ "name" => $name_quiz, "score" =>  round((20 * $result_quiz) / $count_question, 2) , "categorie" => $categorieName];

            if ($this->getUser() != null){
                // Subscibe User code

                // Création d'un objet History
                $objHistory = new History();
                $objHistory->setIdQuizz($objQuiz->getId());
                $objHistory->setScore($history_array[$name_quiz . "_try_" . $value_explode[4]]["score"]);
                $objHistory->setDate(new DateTime());
                $objHistory->setUser($this->getUser());

                // Enregistrement de l'objet History
                $entityManager->persist($objHistory);
                $entityManager->flush();
                // Suppression de la colone actuel du tableau $history_array pour eviter les doublons
                $session->remove($value);
            }
        }
        // Uniquement si il est connecter
        if ($this->getUser() != null ){
            $history_user = $historyRepository->findAll(["user" => $this->getUser()], ["date" => "asc"]);
            foreach ($history_user as $history){
                $objQuiz = $quizzRepository->findBy(["id" => $history->getIdQuizz()])[0];
                $categorieName = $questionRepository->findBy(['quizz' => $objQuiz], null , 1)[0]->getCategorie()->getName() ;               
                $history_array[$history->getId()] = ["name" => $objQuiz->getName(), "score" => $history->getScore(), "categorie" => $categorieName, "date" => $history->getDate()->format('H:i - d/m/Y')];
            }
        }
        return $this->render('history/index.html.twig', [
            'controller_name' => 'HistoryController',
            'history'   => $history_array,
            'count_history' => count($history_array),
            // 'session_history' => $objHistory,
            // 'hey' => array_keys ($session->all()),
            // 'test' => $historyRepository->findAll(),
            'user' => $this->getUser(),
            // 'user_his' => $history_user,
            
        ]);

    }
}
