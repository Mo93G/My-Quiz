<?php

namespace App\Controller;

use App\Entity\Reponse;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ReponseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reponse::class;
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
