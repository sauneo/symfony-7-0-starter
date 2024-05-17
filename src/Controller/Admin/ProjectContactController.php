<?php

namespace App\Controller\Admin;

use App\Entity\Admin\ProjectContact;
use App\Form\Admin\ProjectContactType;
use App\Repository\Admin\ProjectContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/project/contact')]
class ProjectContactController extends AbstractController
{
    #[Route('/', name: 'app_admin_project_contact_index', methods: ['GET'])]
    public function index(
        ProjectContactRepository $projectContactRepository,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/project_contact/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_contacts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_project_contacts', [], 'project'),
            'project_contacts' => $projectContactRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_project_contact_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $projectContact = new ProjectContact();
        $form = $this->createForm(ProjectContactType::class, $projectContact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projectContact);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_project_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_contact/new.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_contacts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_project_contact', [], 'project'),
            'project_contact' => $projectContact,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_contact_show', methods: ['GET'])]
    public function show(
        ProjectContact $projectContact,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/project_contact/show.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_contacts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.project_contact_detail', [], 'project'),
            'project_contact' => $projectContact,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_contact_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ProjectContact $projectContact,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $form = $this->createForm(ProjectContactType::class, $projectContact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_project_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_contact/edit.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_contacts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_project_contact', [], 'project'),
            'project_contact' => $projectContact,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_contact_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ProjectContact $projectContact,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$projectContact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projectContact);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_project_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
