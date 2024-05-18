<?php

namespace App\Controller\Admin;

use App\Entity\Admin\ProjectApi;
use App\Form\Admin\ProjectApiType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/project-api')]
class ProjectApiController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/', name: 'app_admin_project_api_index', methods: ['GET', 'POST'])]
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
            ->add('name', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.ci_name', [], 'project'), 'UTF-8'),
            ])
            ->add('apiKey', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.api_key', [], 'project'), 'UTF-8'),
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router, $translatorInterface) {
                    $showUrl = $router->generate('app_admin_project_api_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_project_api_edit', ['id' => $context->getId()]);
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
                'entity' => ProjectApi::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('pra')
                        ->from(ProjectApi::class, 'pra');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/project_api/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_apis', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_project_apis', [], 'project'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_project_api_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        Request $request,
        TranslatorInterface $translatorInterface,
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
        TranslatorInterface $translatorInterface,
    ): Response {
        return $this->render('admin/project_api/show.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_apis', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.project_api_detail', [], 'project'),
            'project_api' => $projectApi,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_api_edit', methods: ['GET', 'POST'])]
    public function edit(
        EntityManagerInterface $entityManager,
        ProjectApi $projectApi,
        Request $request,
        TranslatorInterface $translatorInterface,
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
        EntityManagerInterface $entityManager,
        ProjectApi $projectApi,
        Request $request,
        TranslatorInterface $translatorInterface,
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
