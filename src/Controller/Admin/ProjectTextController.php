<?php

namespace App\Controller\Admin;

use App\Entity\Admin\ProjectText;
use App\Form\Admin\ProjectTextType;
use App\Service\Admin\ProjectService;
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

#[Route('/admin/project/text')]
class ProjectTextController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/', name: 'app_admin_project_text_index', methods: ['GET', 'POST'])]
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
                'label' => mb_strtolower($translatorInterface->trans('label.name', [], 'project'), 'UTF-8'),
            ])
            ->add('description', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.description', [], 'project'), 'UTF-8'),
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router, $translatorInterface) {
                    $showUrl = $router->generate('app_admin_project_text_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_project_text_edit', ['id' => $context->getId()]);
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
                'entity' => ProjectText::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('prt')
                        ->from(ProjectText::class, 'prt');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/project_text/index.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_project_texts', [], 'project'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_project_text_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        ProjectService $projectService,
        Request $request,
        TranslatorInterface $translatorInterface,
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
            'projectService' => $projectService->getProject(),
            'project_text' => $projectText,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_text_show', methods: ['GET'])]
    public function show(
        ProjectText $projectText,
        TranslatorInterface $translatorInterface,
    ): Response {
        return $this->render('admin/project_text/show.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.project_text_detail', [], 'project'),
            'project_text' => $projectText,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_text_edit', methods: ['GET', 'POST'])]
    public function edit(
        EntityManagerInterface $entityManager,
        ProjectService $projectService,
        ProjectText $projectText,
        Request $request,
        TranslatorInterface $translatorInterface,
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
            'projectService' => $projectService->getProject(),
            'project_text' => $projectText,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_text_delete', methods: ['POST'])]
    public function delete(
        EntityManagerInterface $entityManager,
        ProjectText $projectText,
        Request $request,
        TranslatorInterface $translatorInterface,
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
