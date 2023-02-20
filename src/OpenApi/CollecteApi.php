<?php

namespace App\OpenApi;

use App\service\CollecteService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/**
 * Class CollecteApi
 */
class CollecteApi extends AbstractController
{
    /**
     * @var CollecteService
     */
    private $collecteService;

    /**
     * ArticleController constructor.
     * @param CollecteService $collecteService
     */
    public function __construct(CollecteService $collecteService)
    {
        $this->collecteService = $collecteService;
    }


    // /**
    //  * @Rest\Post("/api/search")
    //  * @param Request $request
    //  * @return View
    //  */
    // public function searchCritere(Request $request): View
    // {
    //     $collectes = $this->collecteService->searchCritere($request);
    //     return View::create($collectes, Response::HTTP_OK);
    // }


}
