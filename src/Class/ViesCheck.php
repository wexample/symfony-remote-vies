<?php

namespace Wexample\SymfonyRemoteVies\Class;

use DateTimeImmutable;

/**
 * VIES's verdict on a VAT number, on a given day. A valid number comes with the
 * name and address the member state registered, where it discloses them (Germany
 * and a few others do not), and with a consultation number when the check was made
 * as a known requester: what proves, in an audit, that it was checked.
 */
class ViesCheck
{
    public function __construct(
        /** The VIES prefix: ISO code, but EL for Greece and XI for Northern Ireland. */
        public readonly string $countryCode,
        /** The number without its prefix. */
        public readonly string $vatNumber,
        public readonly bool $valid,
        public readonly DateTimeImmutable $requestDate,
        public readonly ?string $name = null,
        public readonly ?string $address = null,
        public readonly ?string $requestIdentifier = null,
    ) {
    }

    /**
     * From the answer of POST /check-vat-number, where "---" stands for what is not disclosed.
     */
    public static function fromArray(array $data): self
    {
        $disclosed = fn (?string $value): ?string => null === $value || '' === trim($value) || '---' === trim($value) ? null : trim($value);

        return new self(
            $data['countryCode'],
            $data['vatNumber'],
            (bool) $data['valid'],
            new DateTimeImmutable($data['requestDate']),
            $disclosed($data['name'] ?? null),
            $disclosed($data['address'] ?? null),
            $disclosed($data['requestIdentifier'] ?? null),
        );
    }

    public function getFullVatNumber(): string
    {
        return $this->countryCode.$this->vatNumber;
    }
}
