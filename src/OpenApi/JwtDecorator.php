<?php
// api/src/OpenApi/JwtDecorator.php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Model;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface as FactoryOpenApiFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class JwtDecorator implements FactoryOpenApiFactoryInterface
{

    public function __construct(
        private FactoryOpenApiFactoryInterface $decorated
    ) {
    }



    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ]);

        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'phone' => [
                    'type' => 'string',
                    'example' => '0123456789',
                ],
                'email' => [
                    'type' => 'string',
                    'example' => 'johndoe@example.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'apassword',
                ],
            ],
            'required' => ['password'],
        ]);

        $pathItem = new Model\PathItem(
            ref: 'JWT Token',
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['Token'],
                responses: [
                    '200' => [
                        'description' => 'Get JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token',
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Get JWT token to login.',
                requestBody: new Model\RequestBody(
                    description: 'Generate new JWT Token',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials',
                            ],
                        ],
                    ]),
                ),
            ),
        );

        $openApi->getPaths()->addPath('/authentication_token', $pathItem);

        return $openApi;
    }



    /**
     * @Route("/me", name="api_me", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function me(TokenStorageInterface $tokenStorage): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $tokenStorage->getToken()->getUser();

        $data = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'phone' => $user->getPhone(),
            'enabled' => $user->getEnabled(),
            'isActiveNow' => $user->getIsActiveNow(),
            'lastActivityAt' => $user->getLastActivityAt(),
            'sexe' => $user->getSexe(),
            'status' => $user->getStatus(),
            'adresse' => $user->getAdresse(),
            'sent' => $user->getSent(),
            'received' => $user->getReceived(),

            'departement' => $user->getDepartement()->asArrayUser() ?  $user->getDepartement()->asArrayUser() : null,

            'avatar' => $user->getAvatar(),
            'zoneIntervention' => $user->getZoneIntervention(),

        ];

        return new JsonResponse($data);
    }
}
