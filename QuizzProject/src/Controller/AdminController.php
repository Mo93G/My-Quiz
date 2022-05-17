<?php

namespace App\Controller;

use App\Entity\Quizz;
use App\Entity\Users;
use App\Entity\Categorie;
use App\Entity\Question;
use App\Entity\History;
use App\Entity\Preference;
use App\Entity\Reponse;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comment;
use App\Entity\Conference;

class AdminController extends AbstractDashboardController
{   
    function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
         $this->adminUrlGenerator = $adminUrlGenerator;   
    }
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {   $url = $this->adminUrlGenerator
            ->setController(UsersCrudController::class)
            ->generateUrl();
        return $this->redirect($url);

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        return $this->render('/');
    }

    public function configureDashboard(): Dashboard
    {   
        return Dashboard::new()
            ->setTitle('QuizProject');
    }
    public function configureMenuItems(): iterable
    {  
         yield MenuItem::linktoRoute('Home Pinky Quizz', 'fas fa-list', 'app_accueil');

        yield MenuItem::subMenu("Users", "fa fa-bars")->setSubItems([
            MenuItem::linkToCrud("Add User", "fa fa-plus", Users::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud("Show User", "fa fa-eye", Users::class)
        ]);
        yield MenuItem::subMenu("Quiz", "fa fa-bars")->setSubItems([
            MenuItem::linkToCrud("Add Quiz", "fa fa-plus", Quizz::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud("Show Quiz", "fa fa-eye", Quizz::class),
            MenuItem::linkToCrud("Add Question", "fa fa-plus", Question::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud("Show Question", "fa fa-eye", Question::class),
            MenuItem::linkToCrud("Add Reponse", "fa fa-plus", Reponse::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud("Show Reponse", "fa fa-eye", Reponse::class)

        ]);
        
        yield MenuItem::subMenu("Categorie", "fa fa-bars")->setSubItems([
            MenuItem::linkToCrud("Add Categorie", "fa fa-plus", Categorie::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud("Show Categorie", "fa fa-eye", Categorie::class)
        ]);

        yield MenuItem::subMenu("History", "fa fa-bars")->setSubItems([
            MenuItem::linkToCrud("Add History", "fa fa-plus", History::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud("Show History", "fa fa-eye", History::class)
        ]);

        yield MenuItem::subMenu("Preference", "fa fa-bars")->setSubItems([
            MenuItem::linkToCrud("Add Preference", "fa fa-plus", Preference::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud("Show Preference", "fa fa-eye", Preference::class)
        ]);

        yield MenuItem::linktoRoute('Message', 'fas fa-list', 'app_message');


    }

}
