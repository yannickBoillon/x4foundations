<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Vérifie que l'API joint bien PostgreSQL, Elasticsearch et RabbitMQ.
 */
final class HealthController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Client $elasticsearch,
        #[Autowire(env: 'MESSENGER_TRANSPORT_DSN')]
        private readonly string $messengerDsn,
    ) {
    }

    #[Route('/health', name: 'app_health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $checks = [
            'postgresql' => $this->check(fn () => $this->connection->executeQuery('SELECT 1')->fetchOne()),
            'elasticsearch' => $this->check(function (): void {
                $response = $this->elasticsearch->ping();
                if (!$response instanceof Elasticsearch || !$response->asBool()) {
                    throw new \RuntimeException('ping failed');
                }
            }),
            'rabbitmq' => $this->check(function (): void {
                $url = parse_url($this->messengerDsn);
                $socket = @fsockopen($url['host'] ?? 'rabbitmq', $url['port'] ?? 5672, $errno, $errstr, 2);
                if (false === $socket) {
                    throw new \RuntimeException($errstr);
                }
                fclose($socket);
            }),
        ];

        $healthy = !\in_array(false, array_column($checks, 'ok'), true);

        return $this->json(
            ['status' => $healthy ? 'ok' : 'degraded', 'services' => $checks],
            $healthy ? 200 : 503,
        );
    }

    /** @return array{ok: bool, error?: string} */
    private function check(callable $probe): array
    {
        try {
            $probe();

            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
