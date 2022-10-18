# SDB PHP API Client (Work in Progress)

PHP client library for the SDB API. This client lets you integrate with SDB.

- Get all clients

install:
`composer require silassiai/sdb-php-api-client:dev-main`

```php
require_once 'vendor/autoload.php';

var_dump(
    (new \Silassiai\PhpSbdApiClient\Api\Clients(
        \Silassiai\PhpSbdApiClient\Connection::connect(
            'username',
            'password',
            'secret'
        )
            ->setTenant('tenant')
            ->setEnvironment('acc')
            ->setClientId('sdbApiClient')
            ->setGrantType('sdb_api_ropc_grant')
            ->setScope('sdbApi offline_access')
    ))
    ->get()
);
```
