# Consent Configuration

Meta Pixel Bridge can delay loading Meta Pixel until marketing consent has been granted.

This document explains the supported consent modes and how to configure them.

---

# Why consent handling matters

Meta Pixel is a marketing and tracking technology.

In many environments, it should not be loaded before the visitor has granted the required marketing consent.

Meta Pixel Bridge can therefore keep Meta Pixel completely inactive until the configured consent condition is satisfied.

Expected behaviour:

```text
No marketing consent
        ↓
Meta Pixel script is not loaded
        ↓
No Meta browser events are sent
```

After consent:

```text
Marketing consent granted
        ↓
Meta Pixel script is loaded
        ↓
Pixel is initialized
        ↓
Configured events are sent
```

---

# Supported consent sources

Meta Pixel Bridge supports:

```text
1. Marketing-consent cookie
2. LocalStorage JSON consent
3. Manual JavaScript API
4. Always granted
```

Choose the mode that matches your existing consent solution.

---

# 1. Marketing-consent cookie

Use this mode when your cookie consent system stores marketing permission in a browser cookie.

Example cookie:

```text
cookie_consent=marketing
```

or:

```text
marketing_consent=true
```

Configure:

```text
Consent source:
Marketing-consent cookie
```

Then enter:

```text
Cookie name
Accepted values
Matching mode
```

Example:

```text
Cookie name:
cookie_consent

Accepted values:
accepted,marketing,1,true,yes
```

## Exact matching

Use exact matching when the whole cookie value represents the consent state.

Example cookie:

```text
marketing_consent=true
```

Accepted value:

```text
true
```

## Contains matching

Use contains matching when one cookie stores several consent categories.

Example:

```text
cookie_consent=necessary,analytics,marketing
```

Accepted value:

```text
marketing
```

In this case, Meta Pixel Bridge can treat marketing consent as granted when the cookie contains the configured value.

---

# 2. LocalStorage JSON consent

Use this mode when the consent manager stores consent preferences as JSON in browser LocalStorage.

Example:

```json
{
  "necessary": true,
  "analytics": true,
  "marketing": true,
  "saved_at": 1788795208,
  "valid_until": 1804347208
}
```

Configure:

```text
Consent source:
LocalStorage JSON consent
```

Then configure:

```text
LocalStorage key
Marketing field path
Expiry field path
```

Example:

```text
LocalStorage key:
craftin_cookie_consent_v1

Marketing field path:
marketing

Expiry field path:
valid_until
```

These values are only examples.

Your own consent system may use completely different names.

## Marketing field

The marketing field should point to the value that represents marketing consent.

Example:

```json
{
  "marketing": true
}
```

Configuration:

```text
Marketing field path:
marketing
```

## Nested marketing field

If the JSON is nested:

```json
{
  "preferences": {
    "marketing": true
  }
}
```

the field path may be:

```text
preferences.marketing
```

Use the path format supported by the module configuration.

## Expiry field

If your consent object includes an expiry timestamp:

```json
{
  "marketing": true,
  "valid_until": 1804347208
}
```

configure:

```text
Expiry field path:
valid_until
```

Expired consent should be treated as denied.

If your consent system does not use an expiry value, leave the expiry field empty if supported by the configuration.

## Checking LocalStorage manually

Open browser DevTools:

```text
F12
→ Console
```

Check the stored value:

```javascript
localStorage.getItem('YOUR_STORAGE_KEY');
```

Parse the JSON:

```javascript
JSON.parse(localStorage.getItem('YOUR_STORAGE_KEY'));
```

Example:

```javascript
JSON.parse(
  localStorage.getItem('craftin_cookie_consent_v1')
);
```

You should be able to confirm whether:

```text
marketing = true
```

or:

```text
marketing = false
```

---

# 3. Manual JavaScript API

Use this mode when another consent manager or custom frontend should directly control Meta Pixel Bridge.

Configure:

```text
Consent source:
Manual JavaScript API
```

Grant consent:

```javascript
MetaPixelBridgeConsent.grant();
```

Revoke consent:

```javascript
MetaPixelBridgeConsent.revoke();
```

Force a consent refresh:

```javascript
MetaPixelBridgeConsent.refresh();
```

This is useful when the external consent manager already knows exactly when marketing consent changes.

---

# 4. Always granted

This mode tells Meta Pixel Bridge to treat consent as already granted.

Configure:

```text
Consent source:
Always granted
```

Use this mode only when consent is handled outside Meta Pixel Bridge or when you fully understand the privacy implications.

It may also be useful temporarily during controlled development testing.

It is not recommended as a general default for stores that require marketing consent before loading Meta Pixel.

---

# GPC and Do Not Track

Meta Pixel Bridge can optionally respect:

```text
Global Privacy Control
Do Not Track
```

Recommended setting:

```text
Respect GPC / Do Not Track:
Enabled
```

When supported browser privacy signals are detected, tracking can remain blocked even if another consent source would otherwise allow it.

---

# Consent test procedure

Use this simple two-part test.

## Test A — consent denied

Open the store in a fresh private or incognito browser session.

Do not accept marketing cookies.

Expected state:

```text
consentGranted: false
pixelLoaded: false
pixelInitialized: false
```

There should be no successful Meta Pixel tracking request.

## Test B — consent granted

Grant marketing consent.

Reload the page if required by your consent manager.

Expected live state:

```text
consentGranted: true
pixelLoaded: true
pixelInitialized: true
```

When Dry-run is enabled, Pixel loading remains disabled by design.

Expected Dry-run state may therefore be:

```text
consentGranted: true
pixelLoaded: false
pixelInitialized: false
dryRun: true
```

---

# Network verification

With Dry-run disabled and valid marketing consent:

```text
F12
→ Network
```

Filter for:

```text
facebook.com/tr
```

A live PageView request should contain:

```text
id=YOUR_PIXEL_ID
ev=PageView
```

If no Meta request exists, verify:

```text
1. Tracking is enabled
2. Dry-run is disabled
3. Consent is actually granted
4. GPC / DNT is not blocking tracking
5. PrestaShop cache is cleared
6. Browser extensions are not blocking Meta
```

---

# Changing consent after page load

Depending on the consent implementation, Meta Pixel Bridge may detect a changed consent state and re-evaluate tracking.

For custom integrations, the safest approach is to call:

```javascript
MetaPixelBridgeConsent.refresh();
```

after the external consent manager changes its state.

For manual API mode:

```javascript
MetaPixelBridgeConsent.grant();
```

or:

```javascript
MetaPixelBridgeConsent.revoke();
```

can be called directly.

---

# Privacy note

Meta Pixel Bridge provides technical consent controls.

It does not determine:

- which legal basis applies to your store,
- which consent categories you must use,
- which privacy notice you need,
- or whether your specific configuration complies with applicable law.

Store owners remain responsible for their own privacy and consent configuration.

This project does not provide legal advice.

---

## Example: CraftIN-style LocalStorage consent

Example only:

```json
{
  "necessary": true,
  "analytics": true,
  "marketing": true,
  "saved_at": 1788795208,
  "valid_until": 1804347208
}
```

Example module configuration:

```text
Consent source:
LocalStorage JSON consent

LocalStorage key:
craftin_cookie_consent_v1

Marketing field path:
marketing

Expiry field path:
valid_until
```

This example demonstrates the format used during development and testing.

It is not required for other stores to use the same key or field names.

---

## Author

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