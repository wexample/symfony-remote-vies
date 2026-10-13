## Installation

```php
// config/bundles.php
Wexample\SymfonyRemote\WexampleSymfonyRemoteBundle::class => ['all' => true],
Wexample\SymfonyRemoteVies\WexampleSymfonyRemoteViesBundle::class => ['all' => true],
```

Nothing is required: the public VIES REST API is the default (`wexample_symfony_remote_vies.url` to point elsewhere). Verdicts are cached in `cache.app` for a day.

```yaml
# config/packages/wexample_symfony_remote_vies.yaml
wexample_symfony_remote_vies:
    requester: 'FR12345678901'   # your own VAT number, to get a consultation number per check
```
