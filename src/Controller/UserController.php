<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    #[Route('/admin/user/{id}/to/editor', name: 'app_user_to_editor')]
    public function toEditor(EntityManagerInterface $entityManager, User $user): Response
    {
        $user->setRoles(['ROLE_EDITOR','ROLE_USER']);
        $entityManager->flush();
        $this->addFlash('success', 'User promoted to editor');
        return $this->redirectToRoute('app_user');
    }    
    #[Route('/admin/user/{id}/remove/editor', name: 'app_user_remove_editor')]
    
    public function removeEditor(EntityManagerInterface $entityManager, $id ,User $user): Response
    {
        $user->setRoles(['ROLE_USER']);
        $entityManager->flush();
        $this->addFlash('success', 'Editor demoted to user');
        return $this->redirectToRoute('app_user');
    } 
    
    #[Route('/admin/user/{id}/remove', name: 'app_user_remove')]
    public function removeUser(EntityManagerInterface $entityManager,$id, UserRepository $userRepository): Response
    {
        $userFind = $userRepository->find($id);
        $entityManager->remove($userFind);
        $entityManager->flush();
        $this->addFlash('Danger', 'User removed');
        return $this->redirectToRoute('app_user');
    }  


}
