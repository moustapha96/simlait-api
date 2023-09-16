<?php

namespace App\service;

use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;



class RequestErrorLogger
{


    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function logRequestError(ExceptionEvent $event)
    {
        $exception = $event->getThrowable()->getMessage();
        $request = $event->getRequest();
        $content = $request->getContent();
        $method = $request->getMethod();
        $ip = $request->getClientIp();

        $history = new History();
        $history->setException($exception);
        $history->setIp($ip);
        $history->setRequest($request->getRequestUri());
        $history->setMethod($method);
        $history->setContent($content);

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
