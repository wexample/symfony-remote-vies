# symfony-remote-vies

Version: 2.0.4

EU VAT numbers checked against VIES, the European Commission's register, declared as a symfony-remote

## Table of Contents

- [Installation](#installation)
- [Introduction](#introduction)

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

`symfony-remote-vies` checks EU VAT numbers against VIES, the European Commission's register, which forwards each question to the member state that issued the number. A business buyer from another member state is invoiced without VAT (reverse charge) only when its number is valid there: a format or checksum check says a number could exist, VIES says it does. `ViesClient` is also declared as a `symfony-remote`, so `remote:status` checks the service.
