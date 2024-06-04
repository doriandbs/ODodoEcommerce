<?php

namespace App\Controller\UserControllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

#[Route('/account')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'app_account_myself')]
    public function myself(): Response
    {
        $user = $this->getUser();
        
        $roles = $this->transformRoles($user->getRoles());

    
        return $this->render('user/account/account.html.twig', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    private function transformRoles(array $roles): array
    {
        $rolesOK = [];
        foreach ($roles as $role) {
            switch ($role) {
                case 'ROLE_ADMIN':
                    $rolesOK[] = 'Administrateur';
                    break;
                case 'ROLE_USER':
                    $rolesOK[] = 'Utilisateur';
                    break;
            }
        }
        return $rolesOK;
    }
    
    
}
