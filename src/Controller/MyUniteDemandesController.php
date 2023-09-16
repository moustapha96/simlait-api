<?php


namespace App\Controller;

use App\Entity\UnitesDemandeSuivi;
use App\Repository\UnitesAutreRepository;
use App\Repository\UnitesDemandeRepository;
use App\Repository\UnitesRepository;
use App\service\ConfigurationService;
use Doctrine\ORM\EntityManagerInterface;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsTextualMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Component\Routing\Annotation\Route;




class MyUniteDemandesController extends AbstractController
{


    private $entityManager;
    private $passwordEncoder;
    private $config;

    public function __construct(ConfigurationService $config, EntityManagerInterface $entityManager)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
    }



    /**
     * @Route("api/unites_demande_suivis/sendMessage", name="api_unite_demande_suivi_send_message", methods={"POST"} )
     */
    public function sendMessage(Request $request, MailerInterface  $mailer): Response
    {
        $data = json_decode($request->getContent(), true);
        $desti = $data['destination'];
        $objet = $data['objet'];
        $messageOri = $data['message'];
        $email_to = $data['email'];
        // 4. send message an email
        $methode_send = $this->config->get('sendMail');
        $email_site = $this->config->get('email');

        if ($methode_send == "MAIL" || $methode_send == "mail") {

            try {
                $email = (new TemplatedEmail())
                    ->from(new Address($email_site != '' ? $email_site : 'simlait-api@pdeps.sn'))
                    ->to(new Address($email_to))
                    ->subject($objet)
                    ->htmlTemplate('emails/mail_template.html.twig')
                    ->context([
                        'message' => $messageOri,
                    ]);
                $mailer->send($email);
                return new JsonResponse(['message bien trasmit'], 200, ["Content-Type" => "application/json"]);
            } catch (\Throwable $th) {
                return new JsonResponse([$th], 400);
            }
        } else if ($methode_send == "SMS" || $methode_send == "sms") {

            try {

                // return new Response("Message bien transmis !! $smsResponse ");
                return new JsonResponse(['message bien trasmit'], 200, ["Content-Type" => "application/json"]);
            } catch (\Throwable $apiException) {
                return new JsonResponse([$apiException], 400);
            }
        }
    }

    /**
     * @Route("/api/unites_demande_suivis/create", name="app_unites_suivie_create",methods={"POST"})
     * @param Request $request
     * @return View
     */
    public function create(UnitesAutreRepository $unitesAutreRepository, Request $request, UnitesRepository $u_repo, UnitesDemandeRepository $ud_repo, EntityManagerInterface $entityManagerInterface): ?Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $message = $data['message'];
            $observation = $data['observation'];
            $unites = $data['unites'];
            $unitesDemande = $data['unitesDemande'];
            $unitesAutre = $data['unitesAutre'];
            $unitesDemadneC = $ud_repo->find($unitesDemande);
            $uds = new UnitesDemandeSuivi();

            $dateSaisieFormater = new \DateTime($data['date']);
            $dateSaisie = \DateTimeImmutable::createFromMutable($dateSaisieFormater);
            $uds->setDate($dateSaisie);
            $uds->setMessage($message);
            $uds->setObservation($observation);
            $uds->setUnitesDemande($unitesDemadneC);

            if ($unites != null) {
                $unitesC = $u_repo->find($unites);
                $uds->setUnites($unitesC);
                $uds->setUnitesAutre(null);
            }
            if ($unitesAutre != null) {
                $unitesAutreC = $unitesAutreRepository->find($unitesAutre);
                $uds->setUnitesAutre($unitesAutreC);
                $uds->setUnites(null);
            }

            $entityManagerInterface->persist($uds);
            $entityManagerInterface->flush();


            return new JsonResponse(['creer'], 200, ["Content-Type" => "application/json"]);
        } catch (\Exception $e) {
            return new JsonResponse(['err' => $e->getMessage()], 400);
        }
    }
}