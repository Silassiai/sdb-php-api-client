<?php

namespace Silassiai\PhpSbdApiClient\Api;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Silassiai\PhpSbdApiClient\SdbApi;

class Changes extends SdbApi
{
    /** @var string */
    public const URI = 'changes/clients';

    /**
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function get(): array
    {
        return $this->connection->get(self::URI);
    }
}