# Testing Meta Pixel Bridge

This document provides a practical verification checklist for Meta Pixel Bridge after installation or update.

The goal is to confirm that:

- the module loads correctly,
- consent gating works,
- expected ecommerce events are generated,
- Meta Pixel is initialized only when allowed,
- events reach Meta successfully.

---

## Recommended test environment

Tested reference environment:

- PrestaShop 8.2.3
- Chrome / Chromium-based browser
- Meta Events Manager
- Meta Pixel Bridge v1.0.1

Before testing, clear the PrestaShop cache after updating the module.

Open:

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

---

# 1. Initial safe configuration

Open:

```text
Modules
→ Module Manager
→ Meta Pixel Bridge
→ Configure
```

Recommended test configuration:

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

Enter your own Meta Pixel / Dataset ID.

---

# 2. Test without marketing consent

Open the store in a fresh private / incognito browser session.

Do not grant marketing consent.

Open:

```text
F12
→ Console
```

Expected tracker state:

```text
consentGranted: false
pixelLoaded: false
pixelInitialized: false
queuedEvents: 1
dryRun: true
```

The Meta Pixel script should not be loaded.

No Meta browser events should be sent.

---

# 3. Test with marketing consent

Grant marketing consent using your configured consent manager.

Reload the page.

Expected console state:

```text
consentGranted: true
queuedEvents: 0
dryRun: true
```

Example dry-run output:

```text
[MetaPixelBridge] DRY RUN — event not sent to Meta PageView
```

In dry-run mode:

```text
pixelLoaded: false
pixelInitialized: false
```

is expected.

The tracker processes the event logic without sending data to Meta.

---

# 4. Test PageView

Open or reload any storefront page.

Expected event:

```text
PageView
```

Example:

```text
[MetaPixelBridge] DRY RUN — event not sent to Meta PageView
```

---

# 5. Test ViewContent

Open a product detail page.

Expected events:

```text
PageView
ViewContent
```

The ViewContent payload should contain values such as:

```text
content_ids
content_name
content_type
value
currency
```

Example:

```text
content_type: product
currency: EUR
```

Verify that the product ID, name and price match the displayed product.

---

# 6. Test AddToCart

Add the product to the cart using the normal storefront interface.

Expected event:

```text
AddToCart
```

The payload should contain:

```text
content_ids
contents
content_type
value
currency
```

Meta Pixel Bridge listens to the PrestaShop cart update event instead of relying only on a button click.

---

# 7. Test InitiateCheckout

Open the cart and start the checkout process.

Expected event:

```text
InitiateCheckout
```

Verify:

```text
content_ids
contents
num_items
value
currency
```

The event should normally occur once per cart during the browser session.

---

# 8. Test Search

Use the PrestaShop search field.

Example query:

```text
keychain
```

Expected event:

```text
Search
```

Expected payload:

```text
search_string: keychain
```

---

# 9. Enable live Meta Pixel testing

After all dry-run events are correct:

```text
Dry-run mode → Disabled
```

Keep:

```text
Debug logging → Enabled
```

temporarily.

Reload the storefront after valid marketing consent.

Expected console output:

```text
[MetaPixelBridge] Pixel initialized YOUR_PIXEL_ID
[MetaPixelBridge] Consent granted
[MetaPixelBridge] Sent PageView
```

Expected tracker state:

```text
consentGranted: true
pixelLoaded: true
pixelInitialized: true
queuedEvents: 0
dryRun: false
```

---

# 10. Verify network requests

Open:

```text
F12
→ Network
```

Filter for:

```text
facebook.com/tr
```

A working request should contain:

```text
id=YOUR_PIXEL_ID
ev=PageView
```

Other event examples:

```text
ev=ViewContent
ev=AddToCart
ev=InitiateCheckout
ev=Search
```

A successful request should return a successful HTTP status.

---

# 11. Verify with Meta Events Manager

Open:

```text
Meta Events Manager
→ Dataset / Pixel
→ Test Events
→ Website
```

Enter your storefront URL and start the test session.

Perform:

```text
Open homepage
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

Meta Events Manager should display them as processed browser events.

Meta Pixel Bridge events include unique event IDs such as:

```text
mpb_pageview_...
mpb_viewcontent_...
mpb_addtocart_...
mpb_checkout_...
mpb_search_...
```

---

# 12. Purchase event

Do not enable Purchase during general testing unless you understand the payment flow.

For delayed payment methods:

```text
Order created
≠
Payment received
```

The browser Purchase event is triggered by order confirmation and may therefore report unpaid orders.

Recommended configuration for bank transfer, cheque or manual payment stores:

```text
Purchase → Disabled
```

---

# 13. Disable debug logging after testing

After successful verification:

```text
Debug logging → Disabled
```

Recommended production state:

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

# Troubleshooting checklist

If tracking does not work, verify in this order:

```text
1. Correct Pixel / Dataset ID
2. Tracking enabled
3. Dry-run status
4. Consent state
5. PrestaShop cache cleared
6. Browser console output
7. facebook.com/tr network requests
8. Meta Events Manager
```

Do not debug Meta Events Manager first if the browser is not sending a network request.

---

## Author

Meta Pixel Bridge is developed and maintained by:

**CraftIN / QvarcY (kas.id.lv)**

- https://kas.id.lv/
- https://craftin.lv/
- https://github.com/QvarcY
- info@craftin.lv