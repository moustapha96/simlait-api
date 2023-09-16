<?php


namespace App\Controller;

use App\Repository\CollecteRepository;
use App\Repository\UnitesRepository;
use App\Repository\UserMobileRepository;
use App\service\OrangeSMSService;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Serializer\SerializerInterface;

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
            ->from(new Address("pdepssimlait@gmail.com", 'PDEPS SIMLAIT'))
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

    /**
     *@Route("/api/privacy", name= "api_privacy_save", methods={"POST"})
     */
    public function savePolitiqueConfidentiality(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $texte = $data['texte'];

        $file = 'config/privacy.txt';
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($file, $texte);

        try {
            $fileR = new File($file);
        } catch (FileNotFoundException $e) {
            return new Response('File not found', Response::HTTP_NOT_FOUND);
        } catch (FileException $e) {
            return new Response('An error occured while reading the file', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse($fileR->getContent(), 200);
    }

    /**
     *@Route("/api/privacy", name= "api_privacy_get", methods={"GET"})
     */
    public function getPolitiqueConfidentiality(SerializerInterface $serializer): JsonResponse
    {

        $fileDir = 'config/privacy.txt';
        try {
            $file = new File($fileDir);
        } catch (FileNotFoundException $e) {
            return new Response('File not found', Response::HTTP_NOT_FOUND);
        } catch (FileException $e) {
            return new Response('An error occured while reading the file', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse($file->getContent(), 200);
    }


    /**
     *@Route("/api/unitesSize", name= "api_unites_size", methods={"GET"})
     */
    public function getSizeUnites(UnitesRepository $unitesRepository): JsonResponse
    {
        $unites = count($unitesRepository->findAll());
        return new JsonResponse($unites, 200);
    }

    /**
     *@Route("/api/collectesSize", name= "api_collectes_size", methods={"GET"})
     */
    public function getSizeCollecte(CollecteRepository $collecteRepository): JsonResponse
    {
        $collecte = count($collecteRepository->findAll());
        return new JsonResponse($collecte, 200);
    }

    /**
     *@Route("/api/user_mobilesSize", name= "api_user_mobile_size", methods={"GET"})
     */
    public function getSizeUser(UserMobileRepository $u): JsonResponse
    {
        $users = count($u->findAll());
        return new JsonResponse($users, 200);
    }
}
