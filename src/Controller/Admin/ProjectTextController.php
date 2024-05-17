<?php

namespace App\Controller\Admin;

use App\Entity\Admin\ProjectText;
use App\Form\Admin\ProjectTextType;
use App\Repository\Admin\ProjectTextRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/project/text')]
class ProjectTextController extends AbstractController
{
    #[Route('/', name: 'app_admin_project_text_index', methods: ['GET'])]
    public function index(
        ProjectTextRepository $projectTextRepository,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/project_text/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_project_texts', [], 'project'),
            'project_texts' => $projectTextRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_project_text_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $projectText = new ProjectText();
        $form = $this->createForm(ProjectTextType::class, $projectText);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projectText);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_project_text_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_text/new.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_project_text', [], 'project'),
            'project_text' => $projectText,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_text_show', methods: ['GET'])]
    public function show(
        ProjectText $projectText,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/project_text/show.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.project_text_detail', [], 'project'),
            'project_text' => $projectText,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_text_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ProjectText $projectText,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $form = $this->createForm(ProjectTextType::class, $projectText);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_project_text_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_text/edit.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_project_text', [], 'project'),
            'project_text' => $projectText,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_text_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ProjectText $projectText,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$projectText->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projectText);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_project_text_index', [], Response::HTTP_SEE_OTHER);
    }
}
