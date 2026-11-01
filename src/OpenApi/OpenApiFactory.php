<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * Ajoute à la doc OpenAPI les routes hors API Platform (contrôleurs classiques).
 */
#[AsDecorator(decorates: 'api_platform.openapi.factory')]
final readonly class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $healthSchema = [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'enum' => ['ok', 'degraded']],
                'services' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'object',
                        'properties' => [
                            'ok' => ['type' => 'boolean'],
                            'error' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ];
        $content = new \ArrayObject(['application/json' => ['schema' => $healthSchema]]);

        $openApi->getPaths()->addPath('/health', new PathItem(get: new Operation(
            operationId: 'getHealth',
            tags: ['Health'],
            responses: [
                '200' => new Response('PostgreSQL, Elasticsearch et RabbitMQ joignables', $content),
                '503' => new Response('Au moins un service est injoignable', $content),
            ],
            summary: 'État des services',
            description: 'Vérifie que l\'API joint PostgreSQL, Elasticsearch et RabbitMQ.',
            security: [],
        )));

        return $openApi;
    }
}
