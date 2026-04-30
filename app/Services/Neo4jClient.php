<?php

namespace App\Services;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\SummarizedResult;

class Neo4jClient
{
    private ClientInterface $client;

    public function __construct()
    {
        $uri      = config('neo4j.uri');
        $username = config('neo4j.username');
        $password = config('neo4j.password');

        $this->client = ClientBuilder::create()
            ->withDriver('default', $uri, \Laudis\Neo4j\Authentication\Authenticate::basic($username, $password))
            ->build();
    }

    /**
     * Ejecuta una consulta Cypher y devuelve el resultado.
     *
     * @param array<string, mixed> $params
     */
    public function run(string $query, array $params = []): SummarizedResult
    {
        return $this->client->run($query, $params);
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}
