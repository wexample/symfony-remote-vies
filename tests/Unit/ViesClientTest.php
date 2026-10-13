<?php

namespace Wexample\SymfonyRemoteVies\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wexample\PhpRemote\Enum\RemoteState;
use Wexample\SymfonyRemoteVies\Exception\ViesException;
use Wexample\SymfonyRemoteVies\Service\ViesClient;

class ViesClientTest extends TestCase
{
    private const string URL = 'https://vies.test/rest-api';

    private function answer(
        bool $valid,
        string $countryCode = 'IE',
        string $vatNumber = '6388047V'
    ): string {
        return json_encode([
            'countryCode' => $countryCode,
            'vatNumber' => $vatNumber,
            'requestDate' => '2026-10-13T09:30:32.291Z',
            'valid' => $valid,
            'requestIdentifier' => '',
            'name' => $valid ? 'EXAMPLE IRELAND LIMITED' : '---',
            'address' => $valid ? '3RD FLOOR, EXAMPLE HOUSE, DUBLIN 4' : '---',
        ]);
    }

    /**
     * @param list<array{url: string, body: array}> $requests
     */
    private function client(
        string|array $answers,
        ?array &$requests = null,
        ?string $requester = null,
        bool $cached = false
    ): ViesClient {
        $answers = (array) $answers;
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests, &$answers) {
            $requests[] = ['url' => $url, 'body' => json_decode($options['body'] ?? 'null', true)];

            return new MockResponse(array_shift($answers));
        });

        return new ViesClient($http, self::URL.'/', $requester, $cached ? new ArrayAdapter() : null);
    }

    public function testAValidNumberComesWithWhatTheStateRegistered(): void
    {
        $check = $this->client($this->answer(true), $requests)->check('ie 6388047v');

        $this->assertTrue($check->valid);
        $this->assertSame('IE6388047V', $check->getFullVatNumber());
        $this->assertSame('EXAMPLE IRELAND LIMITED', $check->name);
        $this->assertSame('2026-10-13', $check->requestDate->format('Y-m-d'));
        $this->assertNull($check->requestIdentifier);
        $this->assertSame('https://vies.test/rest-api/check-vat-number', $requests[0]['url']);
        $this->assertSame(['countryCode' => 'IE', 'vatNumber' => '6388047V'], $requests[0]['body']);
    }

    public function testAnInvalidNumberDisclosesNothing(): void
    {
        $check = $this->client($this->answer(false, 'FR', '00000000000'))->check('FR 00.000.000-000');

        $this->assertFalse($check->valid);
        $this->assertNull($check->name);
        $this->assertNull($check->address);
    }

    public function testWhatIsNoVatNumberAtAllIsInvalid(): void
    {
        $check = $this->client('{"actionSucceed": false, "errorWrappers": [{"error": "INVALID_INPUT"}]}')->check('XX123');

        $this->assertFalse($check->valid);
        $this->assertSame('XX', $check->countryCode);
    }

    public function testGreeceIsAskedAsEl(): void
    {
        $this->client($this->answer(true, 'EL', '123456789'), $requests)->check('GR123456789');

        $this->assertSame('EL', $requests[0]['body']['countryCode']);
    }

    public function testARequesterGetsAConsultationNumber(): void
    {
        $answer = json_decode($this->answer(true), true);
        $answer['requestIdentifier'] = 'WAPIAAAAZ1234567';
        $check = $this->client(json_encode($answer), $requests, 'FR 12 345678901')->check('IE6388047V');

        $this->assertSame('WAPIAAAAZ1234567', $check->requestIdentifier);
        $this->assertSame('FR', $requests[0]['body']['requesterMemberStateCode']);
        $this->assertSame('12345678901', $requests[0]['body']['requesterNumber']);
    }

    public function testAnUnavailableStateGivesNoVerdict(): void
    {
        $client = $this->client('{"actionSucceed": false, "errorWrappers": [{"error": "MS_UNAVAILABLE"}]}');

        try {
            $client->check('DE123456789');
            $this->fail('An unavailable member state is no verdict.');
        } catch (ViesException $exception) {
            $this->assertSame('MS_UNAVAILABLE', $exception->errorCode);
        }
    }

    public function testAnErrorPageGivesNoVerdict(): void
    {
        $client = new ViesClient(new MockHttpClient(new MockResponse('<html>Bad gateway</html>', ['http_code' => 502])), self::URL);

        $this->expectException(ViesException::class);
        $client->check('IE6388047V');
    }

    public function testAVerdictIsAskedOnce(): void
    {
        $client = $this->client([$this->answer(true), $this->answer(false)], $requests, cached: true);

        $this->assertTrue($client->check('IE6388047V')->valid);
        $this->assertTrue($client->check('IE 6388047V')->valid);
        $this->assertCount(1, $requests);
    }

    public function testStatus(): void
    {
        $this->assertSame(RemoteState::Up, $this->client('{"vow": {"available": true}, "countries": []}')->checkStatus()->state);
        $this->assertSame(RemoteState::Down, $this->client('{"vow": {"available": false}, "countries": []}')->checkStatus()->state);
        $this->assertSame(RemoteState::Unconfigured, (new ViesClient())->checkStatus()->state);
    }
}
