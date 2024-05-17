<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Project;
use App\Form\Admin\ProjectType;
use App\Repository\Admin\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/admin/project')]
class ProjectController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    #[Route('/', name: 'app_admin_project_index', methods: ['GET'])]
    public function index(
        ProjectRepository $projectRepository,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/project/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_projects', [], 'project'),
            'projects' => $projectRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_project_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/new.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_project', [], 'project'),
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_show', methods: ['GET'])]
    public function show(
        Project $project,
        TranslatorInterface $translatorInterface
    ): Response {
        return $this->render('admin/project/show.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.project_detail', [], 'project'),
            'project' => $project,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/edit.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_project', [], 'project'),
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translatorInterface
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();

            $this->addFlash(
                'notice alert alert-success',
                $translatorInterface->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new-ares', name: 'app_admin_project_new_ares', methods: ['GET'])]
    public function newAres(
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {

        $ico = $request->query->get('ico');
        $validator = Validation::createValidator();
        $constraint = new Assert\Length(['min' => 8, 'max' => 8]);
        $constraint = new Assert\Regex(['pattern' => '/^\d{8}$/']);

        $violations = $validator->validate($ico, [
            $constraint,
        ]);

        if (0 !== count($violations)) {

            // V případě chyby validace vrátit chybovou odpověď
            return $this->json(['error' => $translatorInterface->trans('error.in')], Response::HTTP_BAD_REQUEST);
        } else {

            $response = $this->client->request(
                'GET',
                'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/' . $request->query->get('ico'),
                [
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                ]
            );
        }

        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            $data = json_decode($content, true);
            if ($data) {
                return $this->json($data);
            }
        }

        // V případě chyby nebo neexistujících dat vrátit chybovou odpověď
        return $this->json(['error' => $translatorInterface->trans('error.ares_in_required')], Response::HTTP_NOT_FOUND);
    }
}
