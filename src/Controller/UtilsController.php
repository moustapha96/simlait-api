<?php


namespace App\Controller;

use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;


class UtilsController extends AbstractController
{
    /**
     * @Route("/api/modules/upload/photo", name= "api_upload_photo", methods={"POST"})
     */
    public function uploadPhoto(Request $request)
    {
        $photo = $request->files->get('photo');

        $fileName = md5(uniqid()) . '.' . $photo->guessExtension();
        $url = '/Users/mac/Documents/GitHub/collecte masco/gestioncollectes/web/src/assets';
        try {
            $photo->move(
                $url,
                $fileName
            );
        } catch (FileException $e) {
            print($e);
        }
        return new JsonResponse(['status' => 'success']);
    }
    /**
     * @Route("/api/testerMail", name= "test_mail_send_test", methods={"GET"})
     */
    public function testSendMail(MailerInterface  $mailer): Response
    {

        $email = (new TemplatedEmail())
            ->from(new Address("khouma964@gmail.com", 'PDEPS SIMLAIT'))
            ->to(new Address("khouma964@gmail.com"))
            ->subject('Votre demande de réinitialisation de mot de passe')
            ->htmlTemplate('emails/template_base.html.twig')
            ->context([
                'prenom' => "Al hussein ",
                "nom" => "khouma",
                'message' => 'vous recevez ce mail parce que vous etes parti deu servce',
                'date' =>  new Date('now')
            ]);
        $mailer->send($email);

        return new JsonResponse('mail envoyer');
    }

    /**
     *@Route("/api/sendMail", name= "api_send_mail", methods={"POST"})
     */
    public function sendMail(MailerInterface  $mailer, Request $request): Response
    {

        $data = json_decode($request->getContent(), true);
        $sender = $data['sender'];
        $receiver = $data['receiver'];
        $objet = $data['objet'];
        $message = $data['message'];
        $prenom = $data['prenom'];
        $nom = $data['nom'];

        $email = (new TemplatedEmail())
            ->from(new Address($sender, $prenom . "  " . $nom))
            ->to(new Address($receiver))
            ->subject($objet)
            ->htmlTemplate('emails/mail_template.html.twig')
            ->context([
                'prenom' => $prenom,
                "nom" => $nom,
                'message' => $message,
                'date' =>  new Date('now')
            ]);
        $mailer->send($email);
        return new JsonResponse("mail envoyé avec succé", 200);
    }
}
