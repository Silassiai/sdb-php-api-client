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
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function get(): ResponseInterface
    {
        return $this->connection->get(self::URI);
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getStakeholders(int $id): array
    {
        return $this->connection->get(self::URI . '/' . $id . '/stakeholders');
    }
}