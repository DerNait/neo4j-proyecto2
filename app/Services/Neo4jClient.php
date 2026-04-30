<?php

namespace App\Services;

use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\SummarizedResult;

class Neo4jClient
{
    private ClientInterface $client;
    private string $uri;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->uri      = config('neo4j.uri');
        $this->username = config('neo4j.username');
        $this->password = config('neo4j.password');

        $this->buildClient();
        $this->warmUp();
    }

    /**
     * @param array<string, mixed> $params
     */
    public function run(string $query, array $params = []): SummarizedResult
    {
        $lastException = null;
        for ($attempt = 0; $attempt < 4; $attempt++) {
            try {
                return $this->client->run($query, $params);
            } catch (\Throwable $e) {
                if (!str_contains($e->getMessage(), 'DatabaseNotFound')) {
                    throw $e;
                }
                $lastException = $e;
                usleep(300000 * ($attempt + 1));
            }
        }
        throw $lastException;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    private function buildClient(): void
    {
        $this->client = ClientBuilder::create()
            ->withDriver('default', $this->uri, Authenticate::basic($this->username, $this->password))
            ->build();
    }

    private function warmUp(): void
    {
        for ($i = 0; $i < 6; $i++) {
            try {
                $this->client->run('RETURN 1', []);
                return;
            } catch (\Throwable $e) {
                if (!str_contains($e->getMessage(), 'DatabaseNotFound')) {
                    return;
                }
                usleep(500000);
            }
        }
    }
}
