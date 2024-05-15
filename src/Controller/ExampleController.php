<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\Routing\Attribute\Route;

class ExampleController extends AbstractController
{
    #[Route('/example', name: 'app_example')]
    public function index(): Response
    {
        $mail = new PHPMailer(true);

        try {
            // Nastavení PHPMailer pro použití funkce mail()
            $mail->isMail();
            
            // Nastavení e-mailu
            $mail->setFrom('info@sauneo.cz', 'Mailer');
            $mail->addAddress('petr@sauneo.cz', 'Petr Novák');
            $mail->isHTML(true);
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return new Response('Message has been sent');
        } catch (Exception $e) {
            return new Response("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        /*$exampleText = 'Hello, world!';

        return $this->render('example/index.html.twig', [
            'controller_name' => 'ExampleController',
            'exampleText' => $exampleText,
        ]);*/
    }
}
