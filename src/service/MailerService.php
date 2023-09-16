<?php

namespace App\service;

use App\Entity\Sms;
use App\Repository\ConfigurationRepository;
use App\Repository\SmsRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerService
{

    private $mail;

    public function __construct(
        MailerInterface  $mailer
    ) {
        $this->mail = $mailer;
    }

    public function sendMail(string $message, $detail, string $destinataire, string $destinatairecc, $objet)
    {

        $email = (new TemplatedEmail())
            ->from(new Address("pdepssimlait@gmail.com", 'PDEPS SIMLAIT'))
            ->to(new Address($destinataire))
            ->cc(new Address($destinatairecc))
            ->subject($objet)
            ->htmlTemplate('emails/template_sms.html.twig')
            ->context([
                'message' => $message,
                'detail' => $detail
            ]);
        try {
            $this->mail->send($email);
            return true;
        } catch (\Throwable $th) {
            return $th;
        }

        return true;
    }
}
