---
name: Bug report
about: Report a reproducible problem with Meta Pixel Bridge
title: "[BUG] "
labels: bug
assignees: ''
---

# Bug report

Thank you for taking the time to report a problem with Meta Pixel Bridge.

Before submitting, please check:

- you are using the latest available release,
- PrestaShop cache has been cleared after updating the module,
- the issue is reproducible,
- no passwords, access tokens or customer personal data are included.

For security or privacy vulnerabilities, **do not use a public GitHub issue**.

Please follow [SECURITY.md](../../SECURITY.md) instead.

---

## Meta Pixel Bridge version

Example:

```text
1.0.1
```

Version:

```text

```

---

## PrestaShop version

Example:

```text
8.2.3
```

Version:

```text

```

---

## PHP version

If known:

```text

```

---

## Browser

Example:

```text
Chrome 152
Firefox
Edge
Safari
```

Browser and version:

```text

```

---

## Problem description

Describe what is happening.

```text

```

---

## Expected behaviour

What did you expect Meta Pixel Bridge to do?

```text

```

---

## Actual behaviour

What actually happened?

```text

```

---

## Steps to reproduce

Please provide the shortest reliable reproduction procedure.

Example:

```text
1. Open a product page
2. Accept marketing consent
3. Add the product to cart
4. Open DevTools Console
5. AddToCart event is not generated
```

Your steps:

```text
1.
2.
3.
4.
```

---

## Module configuration

Do not include secrets.

### Tracking

```text
Enable tracking:
Dry-run mode:
Debug logging:
Respect GPC / Do Not Track:
```

### Events

```text
PageView:
ViewContent:
AddToCart:
InitiateCheckout:
Search:
Purchase:
```

### Consent source

Select or describe the configured consent source:

```text
Marketing-consent cookie
LocalStorage JSON consent
Manual JavaScript API
Always granted
```

Configured source:

```text

```

If relevant, include the configuration field names.

Do not include unrelated personal information.

---

## Browser Console output

Enable Meta Pixel Bridge debug logging temporarily if needed.

Paste relevant output here:

```text

```

Please remove:

- customer information
- email addresses
- access tokens
- passwords
- session identifiers
- unrelated sensitive information

---

## Network result

If the issue involves events not reaching Meta:

Open:

```text
F12
→ Network
```

Filter for:

```text
facebook.com/tr
```

Describe what you see:

```text
Request present:
HTTP status:
Pixel ID correct:
Event name:
Browser error, if any:
```

---

## Meta Events Manager

If relevant, describe what appears under:

```text
Meta Events Manager
→ Dataset / Pixel
→ Test Events
```

Result:

```text

```

---

## Consent behaviour

If the issue involves consent, provide both states if possible.

### Without marketing consent

```text
consentGranted:
pixelLoaded:
pixelInitialized:
queuedEvents:
```

### With marketing consent

```text
consentGranted:
pixelLoaded:
pixelInitialized:
queuedEvents:
```

---

## Theme or custom checkout

Are you using:

- a custom PrestaShop theme?
- a one-page checkout module?
- a custom cart implementation?
- another Meta Pixel module?
- another consent manager?

Details:

```text

```

---

## Additional information

Screenshots, sanitized logs or other details that may help reproduce the problem:

```text

```

---

## Checklist

Please confirm before submitting:

- [ ] I am using the latest Meta Pixel Bridge release.
- [ ] I cleared the PrestaShop cache after updating.
- [ ] I searched existing issues for the same problem.
- [ ] I included reproduction steps.
- [ ] I removed passwords, tokens and customer personal information.
- [ ] This is not a confidential security vulnerability.