# symfony-remote-vies

Version: 2.0.2

```php
$check = $viesClient->check('IE 6388047V');
$check->valid;               // true
$check->name;                // "GOOGLE IRELAND LIMITED", null where the state does not disclose it
$check->address;
$check->requestDate;         // when VIES answered
$check->requestIdentifier;   // the consultation number, with a configured requester
$check->getFullVatNumber();  // "IE6388047V"
```

The number is given with its country prefix; spaces, dots and dashes are dropped and Greece's `GR` is asked as `EL`, the prefix VIES uses. A number VIES rejects, including one that is no VAT number at all, is a check with `valid` false.

A `ViesException` means no verdict: VIES or the member state's register unreachable or overloaded (`MS_UNAVAILABLE`, `TIMEOUT`…, in `$exception->errorCode`), which happens daily. The number is then unchecked, neither valid nor invalid: the caller decides whether to retry, wait, or apply VAT meanwhile.

Keep the consultation number with the invoice it justifies: in an audit, it proves the number was checked on that day.

## Table of Contents

- [Integration in the Suite](#integration-in-the-suite)
- [Dependencies](#dependencies)
- [Versioning & Compatibility Policy](#versioning--compatibility-policy)
- [License](#license)
- [About us](#about-us)
- [Migration Notes](#migration-notes)

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

## Dependencies

- php: >=8.5
- wexample/symfony-helpers: >=17.0.0
- wexample/symfony-remote: >=2.0.0
- symfony/http-client: >=7.0
- symfony/cache-contracts: ^3.0

## Versioning & Compatibility Policy

Wexample packages follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

We maintain backward compatibility within major versions and provide clear migration guides for breaking changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Free to use in both personal and commercial projects.

## About us

[Wexample](https://wexample.com) stands as a cornerstone of the digital ecosystem — a collective of seasoned engineers, researchers, and creators driven by a relentless pursuit of technological excellence. More than a media platform, it has grown into a vibrant community where innovation meets craftsmanship, and where every line of code reflects a commitment to clarity, durability, and shared intelligence.

This packages suite embodies this spirit. Trusted by professionals and enthusiasts alike, it delivers a consistent, high-quality foundation for modern development — open, elegant, and battle-tested. Its reputation is built on years of collaboration, refinement, and rigorous attention to detail, making it a natural choice for those who demand both robustness and beauty in their tools.

Wexample cultivates a culture of mastery. Each package, each contribution carries the mark of a community that values precision, ethics, and innovation — a community proud to shape the future of digital craftsmanship.

Built and maintained by [Romain Weeger](https://weeger.io).

## Migration Notes

When upgrading between major versions, refer to the migration guides in the documentation.

Breaking changes are clearly documented with upgrade paths and examples.
