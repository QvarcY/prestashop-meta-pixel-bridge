# Contributing to Meta Pixel Bridge

Thank you for your interest in improving Meta Pixel Bridge.

Contributions, bug fixes, compatibility improvements, documentation updates and well-reasoned feature proposals are welcome.

Meta Pixel Bridge aims to remain:

- lightweight,
- transparent,
- privacy-aware,
- easy to configure,
- easy to test,
- independent from paid SaaS services.

---

## Before contributing

Before starting significant work:

1. Check the existing GitHub issues.
2. Check whether a similar feature or fix is already planned.
3. Open a feature request for larger changes before investing significant development time.
4. Keep changes focused on the problem being solved.

Small fixes and documentation improvements can usually be submitted directly.

---

## Development principles

Contributions should preserve the core principles of Meta Pixel Bridge.

### No unnecessary external services

The browser Pixel implementation should not require:

- an external SaaS account,
- a paid subscription,
- external middleware,
- unrelated analytics services.

Future server-side functionality may communicate directly with Meta where required.

---

### Consent must remain a first-class feature

Changes must not silently bypass configured consent rules.

When consent is denied, Meta tracking must not be activated through an alternative code path.

Any change affecting:

```text
Pixel loading
event dispatch
consent state
GPC
Do Not Track
LocalStorage consent
cookie consent
manual consent API
```

should be tested carefully.

---

### Avoid unnecessary customer PII

Do not add customer personal information to Meta event payloads without a clear reason, documentation and explicit design review.

Meta Pixel Bridge v1.0.1 intentionally does not implement Advanced Matching.

Examples of data that should not be introduced casually:

```text
email address
phone number
customer name
postal address
customer account ID
```

---

## Setting up a development copy

Use a non-production PrestaShop installation whenever possible.

Install the module through:

```text
PrestaShop Back Office
→ Modules
→ Module Manager
→ Upload a module
```

For development testing, recommended initial settings are:

```text
Enable tracking             Enabled
Dry-run mode                Enabled
Debug logging               Enabled
Respect GPC / Do Not Track  Enabled

PageView                    Enabled
ViewContent                 Enabled
AddToCart                   Enabled
InitiateCheckout            Enabled
Search                      Enabled
Purchase                    Disabled
```

Use your own Meta Pixel / Dataset ID.

---

## Testing changes

Before submitting a pull request, test the affected functionality.

See:

[docs/TESTING.md](docs/TESTING.md)

At minimum, verify that your change does not unexpectedly break:

```text
PageView
ViewContent
AddToCart
InitiateCheckout
Search
Consent gating
Dry-run mode
```

When relevant, test both:

```text
marketing consent denied
marketing consent granted
```

---

## PrestaShop cache

After changing frontend JavaScript or updating the module, clear the PrestaShop cache:

```text
Back Office
→ Advanced Parameters
→ Performance
→ Clear cache
```

Then reload the storefront with:

```text
Ctrl + F5
```

Do not modify generated PrestaShop combined cache files directly.

---

## Coding changes

Keep changes focused and readable.

Please avoid:

- unrelated refactoring,
- unnecessary dependencies,
- minified source-only submissions,
- hidden external network calls,
- unrelated tracking functionality,
- hard-coded store-specific values.

Store-specific examples may be used in documentation, but the module itself should remain reusable.

---

## Version-specific changes

Do not change the module version number in a pull request unless the maintainer specifically requests it.

Release versioning is maintained by the project maintainer.

Current version format:

```text
MAJOR.MINOR.PATCH
```

Example:

```text
1.0.1
```

---

## Documentation

If a change affects configuration or user-visible behaviour, update the appropriate documentation.

Relevant files include:

```text
README.md
CHANGELOG.md
docs/INSTALLATION.md
docs/CONSENT.md
docs/TESTING.md
```

---

## Bug fixes

Bug fixes should include:

- a description of the problem,
- reproduction steps,
- explanation of the fix,
- how the fix was tested.

If possible, reference the related GitHub issue.

---

## New features

New functionality should explain:

1. what problem it solves,
2. why it belongs in Meta Pixel Bridge,
3. how it affects consent or privacy,
4. how it was tested,
5. whether it changes existing behaviour.

Large features should generally be discussed in an issue before implementation.

---

## Purchase tracking

Be especially careful when changing `Purchase` behaviour.

For delayed payment methods:

```text
Order created
≠
Payment received
```

Do not change the default behaviour in a way that would cause unpaid orders to be reported automatically as completed purchases.

Future paid-only Purchase tracking is intended to use server-side order/payment state.

---

## Security issues

Do not submit confidential security vulnerabilities as public issues or public pull requests.

See:

[SECURITY.md](SECURITY.md)

Security contact:

```text
info@craftin.lv
```

---

## Pull requests

A useful pull request should contain:

- a clear title,
- a concise description,
- the reason for the change,
- testing information,
- related issue number when applicable.

Please keep one pull request focused on one logical change whenever possible.

---

## License

By contributing to Meta Pixel Bridge, you agree that your contribution may be distributed under the project's MIT License.

See:

[LICENSE.md](LICENSE.md)

---

## Maintainer

Meta Pixel Bridge is developed and maintained by:

**CraftIN / QvarcY (kas.id.lv)**

Developer:
https://kas.id.lv/

CraftIN:
https://craftin.lv/

GitHub:
https://github.com/QvarcY

Contact:
info@craftin.lv