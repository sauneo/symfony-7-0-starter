<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin')]
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_admin_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        TranslatorInterface $translatorInterface,
    ): Response {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/security/login.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.login', [], 'security'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.login', [], 'security'),
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_admin_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/logged-out', name: 'app_admin_logged_out')]
    public function loggedOut(TranslatorInterface $translatorInterface,): Response
    {
        return $this->render('admin/security/logged-out.html.twig', [
            'pageTitle' => $translatorInterface->trans('breadcrumbs.successful_logout', [], 'security'),
        ]);
    }
}
