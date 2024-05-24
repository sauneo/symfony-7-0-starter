<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(TranslatorInterface $translatorInterface): Response
    {
        // Získání aktuálního uživatele
        $user = $this->getUser();

        // Kontrola rolí uživatele
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            // Pokud uživatel nemá roli ROLE_ADMIN, přesměrujte ho na vlastní chybovou stránku
            $this->addFlash('error', 'Nemáte dostatečná oprávnění pro přístup do této sekce.');
            return $this->redirectToRoute('access_denied_custom');
        }

        return $this->render('admin/dashboard/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.dashboard', [], 'dashboard'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.dashboard', [], 'dashboard'),
        ]);
    }

    #[Route('/admin/access-denied', name: 'access_denied_custom')]
    public function accessDenied(): Response
    {
        return $this->render('admin/security/access_denied.html.twig', [
            'pageTitle' => 'Přístup odepřen',
            'message' => 'Nemáte dostatečná oprávnění pro přístup do této sekce.',
        ]);
    }
}
