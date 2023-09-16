<?php

namespace App\Controller;

use App\Entity\ModelSms;
use App\Repository\ModelSmsRepository;
use App\service\ConfigurationService;
use App\service\MailerService;
use App\service\OrangeSMSService;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrangeApiController extends AbstractController
{
    #[Route('api/orange/api', name: 'app_orange_api')]
    public function index(): Response
    {
        return $this->render('orange_api/index.html.twig', [
            'controller_name' => 'OrangeApiController',
        ]);
    }


    #[Route('api/orange/send', name: 'app_orange_api_send', methods: ['POST'])]
    public function sendSMS(OrangeSMSService $orangeSMSService, ConfigurationService $config, MailerService $mailerService, Request $request): JsonResponse
    {
        $emailAdmin = $config->get('email');
        $solde = $orangeSMSService->getSolde();


        $data = json_decode($request->getContent(), true);
        $receiver = $data['receiver'];
        $message = $data['message'];
        $response = $orangeSMSService->sendSMS($receiver, $message);
        if ($response  == 401) {
            return new JsonResponse("Message Non Envoyé, Api Non Connecté", 500);
        }
        if ($response == 403) {
            $mailerService->sendMail("L'envoi de messages échoue actuellement. Veuillez vérifier le solde de SMS disponible.", $solde, $emailAdmin, 'contactezmas@gmail.com', "Solde SMS");
            return new JsonResponse("Message Non Envoyé, code 403", 500);
        }
        if ($response == 500) {
            $mailerService->sendMail("L'envoi de messages échoue actuellement. Veuillez vérifier l'Api SMS .", '', $emailAdmin, 'contactezmas@gmail.com', "Probleme d'envoi des SMS");
            return new JsonResponse("Message Non Envoyé, code 500", 500);
        }
        return new JsonResponse("Message envoyé avec succés ", 200);
    }


    #[Route('api/orange/solde', name: 'app_orange_api_solde', methods: ['GET'])]
    public function soldeSMS(OrangeSMSService $orangeSMSService): JsonResponse
    {
        $response = $orangeSMSService->getSolde();
        return new JsonResponse($response, 200);
    }

    #[Route('api/orange/stat', name: 'app_orange_api_stat', methods: ['GET'])]
    public function statSMS(OrangeSMSService $orangeSMSService): JsonResponse
    {
        $response = $orangeSMSService->getStat();
        return new JsonResponse($response, 200);
    }

    #[Route('api/orange/purchaseorders', name: 'app_orange_api_purchaseorders', methods: ['GET'])]
    public function historySms(OrangeSMSService $orangeSMSService): JsonResponse
    {
        $response = $orangeSMSService->purchaseorders();
        return new JsonResponse($response, 200);
    }

    #[Route('api/orange/genereToken', name: 'app_orange_api_genereToken', methods: ['GET'])]
    public function setToken(OrangeSMSService $orangeSMSService): JsonResponse
    {
        $response = $orangeSMSService->setToken();
        return new JsonResponse($response, 200);
    }

    #[Route('api/orange/sendMessage', name: 'app_orange_api_send_message', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        OrangeSMSService $orangeSMSService,
        ModelSmsRepository $modelSmsRepository,
        MailerService $mailerService,
        ConfigurationService $config,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $emailAdmin = $config->get('email');

        $solde = $orangeSMSService->getSolde();


        $sms = $modelSmsRepository->findOneBy(['code' => $data['code']]);
        if (!$sms) {
            return new JsonResponse("Message non envoyé", 400);
        }
        $message = $sms->getMessage();

        if ($sms->getParametre() != null && count($sms->getParametre()) != 0) {

            $parametres = $sms->getParametre();
            foreach ($parametres as $value) {
                if ($value != '') {
                    $message = str_replace("[" . $value . "]", $data[$value], $message);
                }
            }
        }
        $receiver = $data['receiver'];
        $response = $orangeSMSService->sendSMS($receiver, $message);
        if ($response  == 401) {
            return new JsonResponse("Message Non Envoyé, Api Non Connecté", 500);
        }
        if ($response == 403) {

            $mailerService->sendMail("L'envoi de messages échoue actuellement. Veuillez vérifier le solde de SMS disponible.", $solde, $emailAdmin, 'contactezmas@gmail.com', "Solde SMS");

            return new JsonResponse("Message Non Envoyé, code 403", 500);
        }
        if ($response == 500) {
            $mailerService->sendMail("L'envoi de messages échoue actuellement. Veuillez vérifier l'Api SMS.", '', $emailAdmin, 'contactezmas@gmail.com', "Probleme d'envoi des SMS");
            return new JsonResponse("Message Non Envoyé, code 500", 500);
        }

        return new JsonResponse("Message envoyé avec succés ", 200);
    }
}
