# Changelog

All notable changes to Meta Pixel Bridge are documented in this file.

The format is inspired by [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## [Unreleased]

### Planned

- Meta Conversions API support
- Paid-only `Purchase` events based on PrestaShop order status
- Browser/server event deduplication
- Expanded diagnostics
- Additional consent integrations
- Broader PrestaShop compatibility testing

---

## [1.0.1] - 2026-09-07

### Added

- LocalStorage JSON consent support
- Configurable LocalStorage key
- Configurable marketing consent field
- Configurable consent expiry field
- Automatic consent re-checking after stored consent changes
- Support for existing consent managers that persist JSON consent in LocalStorage

### Improved

- Consent handling is now suitable for consent systems that do not expose a dedicated marketing-consent cookie
- Marketing consent can be granted or revoked without modifying Meta Pixel Bridge itself
- Consent expiry is respected when configured
- Browser event queue is released only after valid marketing consent is detected

### Verified

The following events were tested successfully with Meta Events Manager on PrestaShop 8.2.3:

- `PageView`
- `ViewContent`
- `AddToCart`
- `InitiateCheckout`
- `Search`

Verification included:

- browser console event generation
- consent gating
- successful `facebook.com/tr` requests
- successful HTTP responses
- events displayed as **Processed** in Meta Events Manager
- unique Meta Pixel Bridge event IDs

### Important

`Purchase` remains disabled by default.

For delayed payment methods such as bank transfer, an order confirmation does not necessarily mean that payment has been received.

---

## [1.0.0] - 2026-09-07

### Added

Initial working release of Meta Pixel Bridge for PrestaShop.

#### Meta Pixel integration

- Configurable Meta Pixel / Dataset ID
- Browser-side Meta Pixel initialization
- Pixel loading only after allowed consent state
- Unique `eventID` generation

#### Standard events

- `PageView`
- `ViewContent`
- `AddToCart`
- `InitiateCheckout`
- `Search`
- Optional browser-side `Purchase`

#### PrestaShop integration

- Product page detection
- Product ID, name, price and currency payloads
- PrestaShop `updateCart` integration for successful add-to-cart actions
- Checkout detection
- Cart contents and item count
- Search query tracking
- Order-confirmation Purchase support

#### Consent

- Marketing-consent cookie mode
- Manual JavaScript consent API
- Always-granted mode for externally managed consent
- Optional Global Privacy Control support
- Optional Do Not Track support
- Meta Pixel script blocked until consent is granted

#### Testing and diagnostics

- Dry-run mode
- Browser console debug logging
- Tracker state diagnostics
- Event payload diagnostics
- Event queue while consent is unavailable

#### Privacy

- No Advanced Matching
- No customer email sent
- No customer phone number sent
- No customer name or postal address intentionally sent
- No customer account ID intentionally sent

### Safety

`Purchase` is disabled by default to avoid reporting unpaid orders as completed purchases for delayed or offline payment methods.

---

## Versioning

Meta Pixel Bridge uses semantic-style version numbering:

```text
MAJOR.MINOR.PATCH
```

Examples:

```text
1.0.0
1.0.1
1.1.0
2.0.0
```

- **PATCH** — fixes and small backward-compatible improvements
- **MINOR** — new backward-compatible functionality
- **MAJOR** — significant or potentially breaking changes

---

## Author

Meta Pixel Bridge is developed and maintained by:

**CraftIN / QvarcY (kas.id.lv)**

- https://kas.id.lv/
- https://craftin.lv/
- https://github.com/QvarcY
- info@craftin.lv