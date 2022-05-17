<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry ;
use App\Entity\Users;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use App\Security\EmailVerifier;



class ProfilController extends AbstractController
{   
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){

        $this->entityManager = $entityManager;
        
    }

    #[Route('/profil', name: 'app_profil')]
    public function index(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, EmailVerifier $emailVerifier): Response
    {
        // dd($this->getUser()->getEmail());
        // dd($request->getMethod());


        if($this->getUser() == null){

            return $this->render('security/login.html.twig', [
                'controller_name' => 'AccueilController',
                'categories' => $categorieRepository->findBy([], ["id" => "asc"]),
                'request' => $request,
                'this' => $this,
                'error' => false
            ]);
        }

    if($this->entityManager->getRepository(Users::class)->findOneBy(['isVerified' => 1,'id' => $this->getUser()->getId() ]) == true){

        if ($request->getMethod() === "GET") {
            return $this->render('profil/index.html.twig', [
                'controller_name' => 'ProfilController',
                'email'=>$this->getUser()->getEmail(),
                'password'=>$this->getUser()->getPassword()
                
            ]);          
        } else if ($request->getMethod() === "POST") {

            return $this->editProfile($request, $doctrine, $passwordHasher, $emailVerifier);
        }

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
    
    public function editProfile ( $request, $doctrine, $passwordHasher, $emailVerifier): Response
    {     
        $entityManager = $doctrine->getManager();

        $users = $this->getUser();
        $users->getId();
        $users->setEmail($_POST['email']);

        $hashedPassword = $passwordHasher->hashPassword(
            $users,
            $_POST['password']
        );
        $users->setPassword($hashedPassword);

        $entityManager->persist($users);
        
        $entityManager->flush();

        $this->emailVerifier = $emailVerifier;

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $users,
        (new TemplatedEmail())
            ->from('maria.co.dev@gmail.com')
            ->to($users->getEmail())
            ->subject('Pinky Quizz - Changement de mail')
            ->htmlTemplate('registration/renvoie.html.twig')
        );

        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController'
        ]);

    }

}
