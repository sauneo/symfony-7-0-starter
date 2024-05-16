<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/example')]
class ExampleController extends AbstractController
{
    #[Route('/', name: 'app_admin_example')]
    public function index(): Response
    {
        $exampleText = 'Hello, admin!';

        return $this->render('admin/example/index.html.twig', [
            'controller_name' => 'ExampleController',
            'exampleText' => $exampleText,
        ]);
    }
}
