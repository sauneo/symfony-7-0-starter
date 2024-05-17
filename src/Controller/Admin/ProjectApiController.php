<?php

namespace App\Controller\Admin;

use App\Entity\Admin\ProjectApi;
use App\Form\Admin\ProjectApiType;
use App\Repository\Admin\ProjectApiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/project-api')]
class ProjectApiController extends AbstractController
{
    #[Route('/', name: 'app_admin_project_api_index', methods: ['GET'])]
    public function index(
        ProjectApiRepository $projectApiRepository,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/project_api/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_apis', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_project_apis', [], 'project'),
            'project_apis' => $projectApiRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_project_api_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $projectApi = new ProjectApi();
        $form = $this->createForm(ProjectApiType::class, $projectApi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projectApi);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_project_api_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_api/new.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_apis', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_project_api', [], 'project'),
            'project_api' => $projectApi,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_api_show', methods: ['GET'])]
    public function show(
        ProjectApi $projectApi,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/project_api/show.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_apis', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.project_api_detail', [], 'project'),
            'project_api' => $projectApi,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_api_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ProjectApi $projectApi,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $form = $this->createForm(ProjectApiType::class, $projectApi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_project_api_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_api/edit.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_apis', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_project_api', [], 'project'),
            'project_api' => $projectApi,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_api_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ProjectApi $projectApi,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$projectApi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projectApi);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_project_api_index', [], Response::HTTP_SEE_OTHER);
    }
}
