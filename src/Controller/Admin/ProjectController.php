<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Project;
use App\Form\Admin\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    
    #[Route('/', name: 'app_admin_project_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        TranslatorInterface $translatorInterface,
        UrlGeneratorInterface $router,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.id', [], 'project'), 'UTF-8'),
                'className' => 'text-center'
            ])
            ->add('ciName', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.ci_name', [], 'project'), 'UTF-8'),
            ])
            ->add('publication', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.publication'), 'UTF-8'),
                'className' => 'text-center',
                'render' => function ($value, $context) use ($translatorInterface) {
                    $publication = $context->getPublication();
                    if ($publication === '') {
                        return mb_strtolower($translatorInterface->trans('publication.select'), 'UTF-8');
                    } elseif ($publication == 0) {
                        return '<span class="badge bg-danger">' . mb_strtolower($translatorInterface->trans('publication.unpublish'), 'UTF-8') . '</span>';
                    } elseif ($publication == 1) {
                        return '<span class="badge bg-success">' . mb_strtolower($translatorInterface->trans('publication.publish'), 'UTF-8') . '</span>';
                    }
                    return '';
                }
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router, $translatorInterface) {
                    $showUrl = $router->generate('app_admin_project_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_project_edit', ['id' => $context->getId()]);
                    return sprintf('
                        <div class="text-center">
                            <a href="%s" title="%s"><i class="mdi mdi-eye"></i></a>
                            <a href="%s" title="%s"><i class="mdi mdi-pen"></i></a>
                        </div>',
                        $showUrl, mb_strtolower($translatorInterface->trans('action.show'), 'UTF-8'),
                        $editUrl, mb_strtolower($translatorInterface->trans('action.edit'), 'UTF-8')
                    );
                }
            ])
            ->handleRequest($request)
            ->createAdapter(ORMAdapter::class, [
                'entity' => Project::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('pr')
                        ->from(Project::class, 'pr');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/project/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_projects', [], 'project'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_project_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        Request $request,
        TranslatorInterface $translatorInterface,
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
        TranslatorInterface $translatorInterface,
    ): Response {
        return $this->render('admin/project/show.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.project_detail', [], 'project'),
            'project' => $project,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_edit', methods: ['GET', 'POST'])]
    public function edit(
        EntityManagerInterface $entityManager,
        Project $project,
        Request $request,
        TranslatorInterface $translatorInterface,
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
        EntityManagerInterface $entityManager,
        Project $project,
        Request $request,
        TranslatorInterface $translatorInterface,
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
