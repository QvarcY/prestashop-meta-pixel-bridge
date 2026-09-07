# Installation Guide

This guide explains how to install and configure Meta Pixel Bridge for PrestaShop.

Current reference version:

```text
Meta Pixel Bridge 1.0.1
```

Tested reference environment:

```text
PrestaShop 8.2.3
```

---

# 1. Download the module

Download the latest release ZIP from GitHub Releases.

Example:

```text
metapixelbridge-1.0.1.zip
```

Do not unzip the package before uploading it to PrestaShop.

---

# 2. Install in PrestaShop

Open the PrestaShop Back Office:

```text
Modules
→ Module Manager
→ Upload a module
```

Select:

```text
metapixelbridge-1.0.1.zip
```

Wait for PrestaShop to complete the installation.

After installation, locate:

```text
Meta Pixel Bridge
```

and click:

```text
Configure
```

---

# 3. Get your Meta Pixel / Dataset ID

Open Meta Events Manager and select the Dataset / Pixel that should receive your store events.

Copy the numeric Pixel / Dataset ID.

Example format:

```text
1234567890123456
```

Do not copy this example value.

Use your own Dataset / Pixel ID.

---

# 4. Initial safe configuration

For the first setup, use:

```text
Enable tracking             Enabled
Dry-run mode                Enabled
Debug logging               Enabled
Respect GPC / Do Not Track  Enabled
```

Enter your own:

```text
Pixel / Dataset ID
```

Recommended event settings:

```text
PageView                    Enabled
ViewContent                 Enabled
AddToCart                   Enabled
InitiateCheckout            Enabled
Search                      Enabled
Purchase                    Disabled
```

Keep `Purchase` disabled until you have reviewed your store payment workflow.

---

# 5. Configure consent

Meta Pixel Bridge supports multiple consent sources.

Choose the option that matches your store.

## Option A — Marketing-consent cookie

Use this when your consent manager stores marketing permission in a cookie.

Configure:

```text
Consent source:
Marketing-consent cookie
```

Enter:

```text
Consent cookie name
Accepted cookie values
Cookie value matching
```

Example accepted values:

```text
accepted,marketing,1,true,yes
```

Use the values actually created by your own consent solution.

---

## Option B — LocalStorage JSON consent

Use this when your consent manager stores consent as JSON in browser LocalStorage.

Example stored object:

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

Example:

```text
LocalStorage key:
craftin_cookie_consent_v1

Marketing field path:
marketing

Expiry field path:
valid_until
```

These names are only examples.

Use the key and field names used by your own consent system.

If an expiry field is configured, expired consent is treated as denied.

---

## Option C — Manual JavaScript API

Use this when another consent system should control Meta Pixel Bridge directly.

Configure:

```text
Consent source:
Manual JavaScript API
```

Grant marketing consent:

```javascript
MetaPixelBridgeConsent.grant();
```

Revoke consent:

```javascript
MetaPixelBridgeConsent.revoke();
```

Force Meta Pixel Bridge to re-check consent:

```javascript
MetaPixelBridgeConsent.refresh();
```

---

## Option D — Always granted

This mode assumes consent is handled outside Meta Pixel Bridge.

Use it only when appropriate for your environment.

It can also be useful temporarily during dry-run development testing.

---

# 6. Save the configuration

Click:

```text
Save
```

After changing module code or upgrading the module, clear the PrestaShop cache if necessary:

```text
Advanced Parameters
→ Performance
→ Clear cache
```

Then reload the storefront:

```text
Ctrl + F5
```

---

# 7. Dry-run test

Open the storefront.

Open Chrome DevTools:

```text
F12
→ Console
```

With valid marketing consent and Dry-run enabled, you should see output similar to:

```text
[MetaPixelBridge] DRY RUN — event not sent to Meta PageView
```

No real Meta Pixel request is sent in Dry-run mode.

Test the main store actions:

```text
Open page
Open product
Add product to cart
Start checkout
Perform search
```

Expected events:

```text
PageView
ViewContent
AddToCart
InitiateCheckout
Search
```

Verify that product IDs, prices, currency and cart data are correct.

---

# 8. Verify consent blocking

Use a fresh private / incognito browser session.

Do not grant marketing consent.

Expected state:

```text
consentGranted: false
pixelLoaded: false
pixelInitialized: false
```

Meta Pixel should not load.

Then grant marketing consent.

Expected state in Dry-run mode:

```text
consentGranted: true
queuedEvents: 0
dryRun: true
```

---

# 9. Enable live tracking

After successful Dry-run testing:

```text
Dry-run mode → Disabled
```

Temporarily keep:

```text
Debug logging → Enabled
```

Reload the storefront after valid marketing consent.

Expected console output should include:

```text
[MetaPixelBridge] Pixel initialized YOUR_PIXEL_ID
[MetaPixelBridge] Consent granted
[MetaPixelBridge] Sent PageView
```

---

# 10. Verify browser network requests

Open:

```text
F12
→ Network
```

Filter for:

```text
facebook.com/tr
```

A working Meta Pixel request should contain values such as:

```text
id=YOUR_PIXEL_ID
ev=PageView
```

Other examples:

```text
ev=ViewContent
ev=AddToCart
ev=InitiateCheckout
ev=Search
```

The request should return a successful HTTP status.

---

# 11. Verify with Meta Events Manager

Open:

```text
Meta Events Manager
→ Dataset / Pixel
→ Test Events
```

Select:

```text
Website
```

Enter your storefront URL.

Example:

```text
https://example.com/
```

Start the test session.

Perform the following actions in the test storefront:

```text
Open homepage
Open a product
Add product to cart
Start checkout
Perform a search
```

Expected events:

```text
PageView
ViewContent
AddToCart
InitiateCheckout
Search
```

Meta Events Manager should display Meta Pixel Bridge events as processed browser events.

---

# 12. Production configuration

After successful testing, recommended settings are:

```text
Enable tracking             Enabled
Dry-run mode                Disabled
Debug logging               Disabled
Respect GPC / Do Not Track  Enabled

PageView                    Enabled
ViewContent                 Enabled
AddToCart                   Enabled
InitiateCheckout            Enabled
Search                      Enabled
Purchase                    Depends on payment workflow
```

---

# 13. Important Purchase warning

The browser `Purchase` event is based on the PrestaShop order-confirmation flow.

For immediate-payment stores this may be appropriate.

For delayed payments:

```text
Order created
≠
Payment received
```

Examples:

- bank transfer
- cheque
- manual payment
- offline payment

For these stores, keep:

```text
Purchase → Disabled
```

until a paid-only server-side Purchase implementation is available.

---

# 14. Updating Meta Pixel Bridge

When installing a newer version:

1. Back up your store.
2. Upload the newer module package.
3. Allow PrestaShop to run the module upgrade.
4. Clear PrestaShop cache.
5. Reload the storefront with `Ctrl + F5`.
6. Repeat the important tests from `TESTING.md`.

Do not edit PrestaShop combined cache JavaScript files directly.

Files under paths such as:

```text
/themes/.../assets/cache/
```

may be regenerated by PrestaShop.

Always modify the actual module source instead.

---

# Troubleshooting

If the module does not appear to work, check in this order:

```text
1. Correct Pixel / Dataset ID
2. Tracking enabled
3. Dry-run status
4. Consent configuration
5. Consent state in browser
6. PrestaShop cache
7. Browser Console
8. facebook.com/tr Network requests
9. Meta Events Manager
```

See:

[TESTING.md](TESTING.md)

for a detailed verification checklist.

See:

[CONSENT.md](CONSENT.md)

for detailed consent configuration.

---

## Support

GitHub:

https://github.com/QvarcY

Developer:

https://kas.id.lv/

CraftIN:

https://craftin.lv/

Email:

info@craftin.lv

Support the project:

https://buymeacoffee.com/craftin
