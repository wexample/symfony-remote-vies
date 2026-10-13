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
