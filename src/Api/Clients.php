<?php

namespace Silassiai\PhpSbdApiClient\Api;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Silassiai\PhpSbdApiClient\SdbApi;

class Clients extends SdbApi
{
    /** @var string */
    public const URI = 'clients';

    /**
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function get(): array
    {
        return $this->connection->get(self::URI);
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function getById(int $id)
    {
        return $this->connection->get(self::URI . '/' . $id);
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function getStakeholders(int $id): array
    {
        return $this->connection->get(self::URI . '/' . $id . '/stakeholders');
    }
}