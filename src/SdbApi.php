<?php

namespace Silassiai\PhpSbdApiClient;

class SdbApi
{
    /** @var Connection $connection */
    protected Connection $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}