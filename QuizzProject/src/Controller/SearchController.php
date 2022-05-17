<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizzRepository;
use App\Repository\ReponseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search/{categorie}', name: 'app_search')]
    public function index($categorie , CategorieRepository $categorieRepository, QuizzRepository $quizzRepository, ReponseRepository $reponseRepository, QuestionRepository $questionRepository): Response
    {       
        $objCategorie  = $categorieRepository->findBy(["name" => $categorie]);
        $objQuestion = $questionRepository->findBy(["categorie" => $objCategorie]);
        $array_quiz = [];
        $array_quiz_all = [];
        $objtQuiz = '';
        foreach($objQuestion as $value){
            $objtQuiz = $value->getQuizz();
            if(!in_array($objtQuiz, $array_quiz)){
                array_push( $array_quiz ,$objtQuiz);
            }
        }
        $objtQuiz_all = $quizzRepository->findAll();
        foreach ($objtQuiz_all as $value){
            $categorieName = $questionRepository->findBy(['quizz' => $value])[0]->getCategorie()->getName() ;               

            $array_quiz_all[$value->getId()] = [ 'name' => $value->getName(), "date" => $value->getCreatedAt()->format('H:i - d/m/Y'), "categorie" => $categorieName , "id" => $value->getId() ];

        }
        // $quizzRepository->findBy(["reponse" => $reponseRepository->findBy([], null, 1)[0]->getQuestion()->findAll()
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'categories' => $categorieRepository->findBy([], ['id' => 'asc']),
            'cat' => $categorie,
            'quiz' => $objtQuiz,
            'quiz_array' => $array_quiz,
            'cateroi' => $objCategorie,
            'ObjQuiz' => $array_quiz_all,
        ]);
    }
}
