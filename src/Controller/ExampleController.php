<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExampleController extends AbstractController
{
    #[Route('/example', name: 'app_example')]
    public function index(): Response
    {
        $exampleText = 'Hello, world!';

        return $this->render('example/index.html.twig', [
            'controller_name' => 'ExampleController',
            'exampleText' => $exampleText,
        ]);
    }
}
