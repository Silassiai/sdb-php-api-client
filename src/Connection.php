<?php

namespace Silassiai\PhpSbdApiClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JsonException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Silassiai\PhpSbdApiClient\Exceptions\SdbClientException;
use stdClass;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Connection
{
    /** @var string $username */
    private string $username;
    /** @var string $password */
    private string $password;
    /** @var string $secret */
    private string $secret;
    /** @var string $tenant */
    private string $tenant;
    /** @var string $environment */
    private string $environment;
    /** @var string $clientId */
    private string $clientId;
    /** @var string $scope */
    private string $scope;
    /** @var string $grantType */
    private string $grantType;

    /** @var FilesystemAdapter $cache */
    private FilesystemAdapter $cache;
    /** @var Client $client */
    private Client $client;

    public function __construct(
        string $username,
        string $password,
        string $secret
    )
    {
        $this->username = $username;
        $this->password = $password;
        $this->secret = $secret;
        $this->client = new Client();
        $this->cache = new FilesystemAdapter();
    }

    public static function connect(
        string $username,
        string $password,
        string $secret
    ): self
    {
        return new self($username, $password, $secret);
    }

    /**
     * @param string $tenant
     * @return Connection
     */
    public function setTenant(string $tenant): Connection
    {
        $this->tenant = $tenant;
        return $this;
    }

    /**
     * @param string $environment
     * @return Connection
     */
    public function setEnvironment(string $environment): Connection
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param string $clientId
     * @return Connection
     */
    public function setClientId(string $clientId): Connection
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @param string $scope
     * @return Connection
     */
    public function setScope(string $scope): Connection
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @param string $grantType
     * @return Connection
     */
    public function setGrantType(string $grantType): Connection
    {
        $this->grantType = $grantType;
        return $this;
    }

    /**
     * @return string
     */
    private function getBaseUrl(): string
    {
        return 'https://' . $this->tenant . '-api.' . $this->environment . '-sdbecd.nl/';
    }

    /**
     * @return string
     */
    private function getBaseSsoUrl(): string
    {
        return 'https://' . $this->tenant . '.' . $this->environment . '-sdbidentity.nl/';
    }

    /**
     * @return stdClass
     * @throws GuzzleException
     * @throws JsonException
     */
    private function getNewToken(): stdClass
    {
        $response = $this->client->post($this->getBaseSsoUrl() . 'connect/token', [
            'form_params' => [
                'client_id' => $this->clientId,
                'scope' => $this->scope,
                'grant_type' => $this->grantType,
                'secret' => $this->secret,
                'username' => $this->username,
                'password' => $this->password,
            ]
        ])->getBody()->getContents();
        return json_decode($response, false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getAccessToken(): array
    {
        return $this->cache->get($this->username . 'token', function (ItemInterface $item) {
            $newAccessToken = $this->getNewToken();

            $expiresAt = time() + ($newAccessToken->expires_in - 10);
            $item->expiresAfter($expiresAt);

            return [
                'access_token' => $newAccessToken->access_token,
                'refresh_token' => $newAccessToken->refresh_token,
                'expires_at' => $expiresAt,
            ];
        });
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param $body
     * @param array $params
     * @param array $headers
     * @return Request
     * @throws InvalidArgumentException
     */
    public function createRequest(string $method, string $endpoint, $body = null, array $params = [], array $headers = []): Request
    {
        $headers['Authorization'] = 'Bearer ' . $this->getAccessToken()['access_token'];

        // Create param string
        if (!empty($params)) {
            $endpoint .= strpos($endpoint, '?') === false ? '?' : '&';
            $endpoint .= http_build_query($params);
        }

        return new Request($method, $endpoint, $headers, $body);
    }

    /**
     * @param string $uri
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function get(string $uri): array
    {

        $response = $this->client->send(
            $this->createRequest('GET', $this->getBaseUrl() . 'api/' . $uri, null, [], [])
        );
        return $this->parseResponse($response);
    }

    /**
     * @throws JsonException
     * @throws SdbClientException
     */
    public function parseResponse(Response $response): array
    {
        if ($response->getStatusCode() === 204) {
            return [];
        }

        Message::rewindBody($response);
        $responseBodyContent = $response->getBody()->getContents();
        $resultArray = json_decode($responseBodyContent, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($resultArray)) {
            throw new SdbClientException('Json decode failed. Got response: ' . $responseBodyContent);
        }
        return $resultArray;
    }
}