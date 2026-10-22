<?php

namespace Wexample\SymfonyRemoteVies\Service;

use DateTimeImmutable;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Wexample\PhpRemote\Class\RemoteStatus;
use Wexample\PhpRemote\Interface\RemoteInterface;
use Wexample\SymfonyRemoteVies\Class\ViesCheck;
use Wexample\SymfonyRemoteVies\Exception\ViesException;

/**
 * Checks an EU VAT number against VIES, the European Commission's register, which
 * forwards each question to the member state's own. A business buyer from another
 * member state is only invoiced without VAT when VIES says its number is valid.
 *
 * A verdict, valid or not, is cached for a day: a checkout asks the same question
 * at every price it computes.
 */
class ViesClient implements RemoteInterface
{
    public const string DEFAULT_URL = 'https://ec.europa.eu/taxation_customs/vies/rest-api';

    private const int CACHE_TTL = 24 * 3600;

    /** The ISO codes VIES spells otherwise. */
    private const array PREFIXES = ['GR' => 'EL'];

    /** What VIES answers when the question itself is not a VAT number: a verdict, not a failure. */
    private const string ERROR_INVALID_INPUT = 'INVALID_INPUT';

    public function __construct(
        private readonly ?HttpClientInterface $httpClient = null,
        private readonly string $url = self::DEFAULT_URL,
        private readonly ?string $requester = null,
        private readonly ?CacheInterface $cache = null,
    ) {
    }

    public function getKey(): string
    {
        return 'vies';
    }

    public function getLabel(): string
    {
        return 'VIES VAT number check';
    }

    public function checkStatus(): RemoteStatus
    {
        if (null === $this->httpClient) {
            return RemoteStatus::unconfigured('No HTTP client.');
        }

        $response = $this->httpClient->request('GET', $this->endpoint('/check-status'));

        if (200 !== $response->getStatusCode()) {
            return RemoteStatus::down('HTTP '.$response->getStatusCode());
        }

        return ($response->toArray()['vow']['available'] ?? false) ? RemoteStatus::up() : RemoteStatus::down('VIES reports itself unavailable.');
    }

    /**
     * @param string $vatNumber The number with its country prefix, spaces, dots and dashes allowed ("FR 12 345 678 901").
     *
     * @throws ViesException When VIES gives no verdict; a number it rejects is a check, not valid.
     */
    public function check(string $vatNumber): ViesCheck
    {
        [$countryCode, $number] = self::split($vatNumber);

        if (null === $this->cache) {
            return $this->request($countryCode, $number);
        }

        return $this->cache->get(
            'wexample_remote_vies_'.$countryCode.$number,
            function (ItemInterface $item) use ($countryCode, $number): ViesCheck {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->request($countryCode, $number);
            }
        );
    }

    /**
     * A number as VIES reads it: the prefix apart, separators dropped, GR spelled EL.
     *
     * @return array{0: string, 1: string}
     */
    public static function split(string $vatNumber): array
    {
        $clean = strtoupper((string) preg_replace('/[\s.\-]/', '', $vatNumber));
        $prefix = substr($clean, 0, 2);

        return [self::PREFIXES[$prefix] ?? $prefix, substr($clean, 2)];
    }

    private function request(
        string $countryCode,
        string $number
    ): ViesCheck {
        if (null === $this->httpClient) {
            throw new ViesException('No HTTP client to reach VIES.');
        }

        $body = ['countryCode' => $countryCode, 'vatNumber' => $number];

        if (null !== $this->requester) {
            [$body['requesterMemberStateCode'], $body['requesterNumber']] = self::split($this->requester);
        }

        $response = $this->httpClient->request('POST', $this->endpoint('/check-vat-number'), ['json' => $body]);

        try {
            // VIES reports its errors under HTTP 200 as well as 400 and 500.
            $data = $response->toArray(false);
        } catch (TransportExceptionInterface|DecodingExceptionInterface $exception) {
            // Unreachable, or a gateway's error page instead of its JSON: no verdict either.
            throw new ViesException('VIES could not be read: '.$exception->getMessage());
        }

        if (isset($data['valid'])) {
            return ViesCheck::fromArray($data);
        }

        $error = $data['errorWrappers'][0]['error'] ?? null;

        if (self::ERROR_INVALID_INPUT === $error) {
            return new ViesCheck($countryCode, $number, false, new DateTimeImmutable());
        }

        throw new ViesException(sprintf('VIES gave no verdict on %s%s (HTTP %d): %s.', $countryCode, $number, $response->getStatusCode(), $error ?? 'no error code'), $error);
    }

    private function endpoint(string $path): string
    {
        return rtrim($this->url, '/').$path;
    }
}
