<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Language;
use App\Form\Admin\LanguageType;
use App\Repository\Admin\LanguageRepository;
use App\Service\Admin\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/language')]
class LanguageController extends AbstractController
{
    #[Route('/', name: 'app_admin_language_index', methods: ['GET'])]
    public function index(
        ProjectService $projectService,
        LanguageRepository $languageRepository,
        TranslatorInterface $translatorInterface,
    ): Response {
        return $this->render('admin/language/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.languages'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_languages'),
            'projectService' => $projectService->getProject(), // dočasně
            'languages' => $languageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_language_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($language);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/language/new.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.languages'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_language'),
            'language' => $language,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_language_show', methods: ['GET'])]
    public function show(
        Language $language,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/language/show.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.languages'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.language_detail'),
            'language' => $language,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_language_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Language $language,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/language/edit.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.languages'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_language'),
            'language' => $language,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_language_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Language $language,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $language->getId(), $request->request->get('_token'))) {
            $entityManager->remove($language);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_language_index', [], Response::HTTP_SEE_OTHER);
    }
}
