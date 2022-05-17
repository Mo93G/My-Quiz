<?php

namespace App\Controller;

use App\Entity\Quizz;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class QuizzCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quizz::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
