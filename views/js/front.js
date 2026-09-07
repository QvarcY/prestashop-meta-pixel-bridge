(function (window, document) {
  'use strict';

  var config = window.MetaPixelBridgeConfig || null;
  if (!config || !config.pixelId) {
    return;
  }

  var state = {
    consentGranted: false,
    pixelLoaded: false,
    pixelInitialized: false,
    queue: [],
    sentThisPage: Object.create(null),
    pendingAdd: null,
    prestashopBound: false
  };

  function log() {
    if (!config.debug || !window.console || typeof window.console.info !== 'function') {
      return;
    }
    var args = Array.prototype.slice.call(arguments);
    args.unshift('[MetaPixelBridge]');
    window.console.info.apply(window.console, args);
  }

  function warn() {
    if (!config.debug || !window.console || typeof window.console.warn !== 'function') {
      return;
    }
    var args = Array.prototype.slice.call(arguments);
    args.unshift('[MetaPixelBridge]');
    window.console.warn.apply(window.console, args);
  }

  function readCookie(name) {
    if (!name) {
      return null;
    }
    var escaped = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var match = document.cookie.match(new RegExp('(?:^|;\\s*)' + escaped + '=([^;]*)'));
    if (!match) {
      return null;
    }
    try {
      return decodeURIComponent(match[1]);
    } catch (e) {
      return match[1];
    }
  }

  function privacySignalBlocksTracking() {
    if (!config.respectGpc) {
      return false;
    }
    if (navigator.globalPrivacyControl === true) {
      return true;
    }
    var dnt = navigator.doNotTrack || window.doNotTrack || navigator.msDoNotTrack;
    return dnt === '1' || dnt === 'yes';
  }

  function readObjectPath(object, path) {
    if (!object || !path) {
      return undefined;
    }
    var parts = String(path).split('.');
    var current = object;
    for (var i = 0; i < parts.length; i++) {
      var part = parts[i].trim();
      if (!part || current === null || typeof current !== 'object' || !Object.prototype.hasOwnProperty.call(current, part)) {
        return undefined;
      }
      current = current[part];
    }
    return current;
  }

  function consentValueMatches(value) {
    var consent = config.consent || {};
    var current = String(value).trim().toLowerCase();
    var accepted = Array.isArray(consent.acceptedValues) ? consent.acceptedValues : [];
    var matchMode = consent.match === 'contains' ? 'contains' : 'exact';

    for (var i = 0; i < accepted.length; i++) {
      var wanted = String(accepted[i]).trim().toLowerCase();
      if (!wanted) {
        continue;
      }
      if (matchMode === 'contains' && current.indexOf(wanted) !== -1) {
        return true;
      }
      if (matchMode === 'exact' && current === wanted) {
        return true;
      }
    }
    return false;
  }

  function localStorageConsentGranted() {
    var consent = config.consent || {};
    var storage;
    try {
      storage = window.localStorage;
    } catch (e) {
      return false;
    }
    if (!storage || !consent.storageKey || !consent.storageField) {
      return false;
    }

    var raw;
    try {
      raw = storage.getItem(consent.storageKey);
    } catch (e) {
      return false;
    }
    if (!raw) {
      return false;
    }

    var data;
    try {
      data = JSON.parse(raw);
    } catch (e) {
      return false;
    }

    if (consent.storageExpiryField) {
      var expiry = readObjectPath(data, consent.storageExpiryField);
      if (expiry !== undefined && expiry !== null && expiry !== '') {
        var expiryNumber = Number(expiry);
        if (!isFinite(expiryNumber) || expiryNumber <= Math.floor(Date.now() / 1000)) {
          return false;
        }
      }
    }

    var value = readObjectPath(data, consent.storageField);
    if (value === undefined || value === null) {
      return false;
    }
    return consentValueMatches(value);
  }

  function cookieConsentGranted() {
    var consent = config.consent || {};
    var raw = readCookie(consent.cookieName || '');
    if (raw === null) {
      return false;
    }

    return consentValueMatches(raw);
  }

  function configuredConsentGranted() {
    if (privacySignalBlocksTracking()) {
      return false;
    }

    var mode = config.consent && config.consent.mode ? config.consent.mode : 'cookie';
    if (mode === 'always') {
      return true;
    }
    if (mode === 'cookie') {
      return cookieConsentGranted();
    }
    if (mode === 'localstorage') {
      return localStorageConsentGranted();
    }
    return false;
  }

  function installFbqLoader() {
    if (window.fbq) {
      state.pixelLoaded = true;
      return;
    }

    /* Standard Meta Pixel loader. It is intentionally executed only after consent. */
    (function (f, b, e, v, n, t, s) {
      if (f.fbq) {
        return;
      }
      n = f.fbq = function () {
        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
      };
      if (!f._fbq) {
        f._fbq = n;
      }
      n.push = n;
      n.loaded = true;
      n.version = '2.0';
      n.queue = [];
      t = b.createElement(e);
      t.async = true;
      t.src = v;
      s = b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t, s);
    })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

    state.pixelLoaded = true;
  }

  function initializePixel() {
    if (config.dryRun || state.pixelInitialized || !state.consentGranted) {
      return;
    }

    installFbqLoader();
    if (typeof window.fbq !== 'function') {
      warn('fbq loader could not be initialized.');
      return;
    }

    window.fbq('consent', 'grant');
    window.fbq('init', String(config.pixelId));
    state.pixelInitialized = true;
    log('Pixel initialized', config.pixelId);
  }

  function storageFor(kind) {
    try {
      return kind === 'local' ? window.localStorage : window.sessionStorage;
    } catch (e) {
      return null;
    }
  }

  function wasSentOnce(eventObject) {
    if (!eventObject || !eventObject.once || !eventObject.once.key) {
      return false;
    }
    var storage = storageFor(eventObject.once.storage);
    if (!storage) {
      return false;
    }
    try {
      return storage.getItem(eventObject.once.key) === '1';
    } catch (e) {
      return false;
    }
  }

  function markSentOnce(eventObject) {
    if (!eventObject || !eventObject.once || !eventObject.once.key) {
      return;
    }
    var storage = storageFor(eventObject.once.storage);
    if (!storage) {
      return;
    }
    try {
      storage.setItem(eventObject.once.key, '1');
    } catch (e) {
      // Storage may be blocked; tracking still proceeds.
    }
  }

  function normalizeEvent(eventObject) {
    if (!eventObject || !eventObject.name) {
      return null;
    }
    return {
      name: String(eventObject.name),
      params: eventObject.params && typeof eventObject.params === 'object' ? eventObject.params : {},
      eventId: eventObject.eventId ? String(eventObject.eventId) : createEventId(String(eventObject.name).toLowerCase()),
      once: eventObject.once || null
    };
  }

  function trackEventObject(rawEvent) {
    var eventObject = normalizeEvent(rawEvent);
    if (!eventObject || wasSentOnce(eventObject)) {
      if (eventObject && wasSentOnce(eventObject)) {
        log('Skipped duplicate one-time event', eventObject.name, eventObject.once.key);
      }
      return;
    }

    var pageKey = eventObject.name + ':' + eventObject.eventId;
    if (state.sentThisPage[pageKey]) {
      return;
    }

    if (!state.consentGranted) {
      state.queue.push(eventObject);
      log('Queued until consent', eventObject.name, eventObject);
      return;
    }

    state.sentThisPage[pageKey] = true;

    if (config.dryRun) {
      log('DRY RUN — event not sent to Meta', eventObject.name, eventObject.params, { eventID: eventObject.eventId });
      return;
    }

    initializePixel();
    if (typeof window.fbq !== 'function' || !state.pixelInitialized) {
      delete state.sentThisPage[pageKey];
      state.queue.push(eventObject);
      warn('Pixel unavailable; event re-queued', eventObject.name);
      return;
    }

    window.fbq('track', eventObject.name, eventObject.params, { eventID: eventObject.eventId });
    markSentOnce(eventObject);
    log('Sent', eventObject.name, eventObject.params, { eventID: eventObject.eventId });
  }

  function flushQueue() {
    if (!state.consentGranted || !state.queue.length) {
      return;
    }
    var pending = state.queue.slice();
    state.queue = [];
    for (var i = 0; i < pending.length; i++) {
      trackEventObject(pending[i]);
    }
  }

  function grantConsent(source) {
    if (privacySignalBlocksTracking()) {
      state.consentGranted = false;
      log('Consent grant ignored because GPC / DNT is active.');
      return;
    }

    state.consentGranted = true;
    if (!config.dryRun) {
      initializePixel();
      if (typeof window.fbq === 'function' && state.pixelInitialized) {
        window.fbq('consent', 'grant');
      }
    }
    log('Consent granted', source || 'manual');
    flushQueue();
  }

  function revokeConsent(source) {
    state.consentGranted = false;
    if (typeof window.fbq === 'function' && state.pixelInitialized) {
      window.fbq('consent', 'revoke');
    }
    log('Consent revoked', source || 'manual');
  }

  function refreshConsent() {
    var mode = config.consent && config.consent.mode ? config.consent.mode : 'cookie';
    if (mode === 'manual') {
      if (privacySignalBlocksTracking()) {
        revokeConsent('privacy-signal');
      }
      return;
    }

    var granted = configuredConsentGranted();
    if (granted && !state.consentGranted) {
      grantConsent(mode);
    } else if (!granted && state.consentGranted) {
      revokeConsent(mode);
    }
  }

  function createEventId(prefix) {
    var random;
    try {
      if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        random = window.crypto.randomUUID().replace(/-/g, '');
      } else if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
        var bytes = new Uint8Array(8);
        window.crypto.getRandomValues(bytes);
        random = Array.prototype.map.call(bytes, function (b) {
          return ('0' + b.toString(16)).slice(-2);
        }).join('');
      }
    } catch (e) {
      random = null;
    }
    if (!random) {
      random = String(Date.now()) + String(Math.floor(Math.random() * 1000000));
    }
    return 'mpb_' + String(prefix || 'event').replace(/[^a-z0-9_-]/gi, '_') + '_' + random;
  }

  function safeNumber(value) {
    if (typeof value === 'number' && isFinite(value)) {
      return value;
    }
    if (typeof value === 'string') {
      var normalized = value.replace(/\s/g, '').replace(',', '.').replace(/[^0-9.-]/g, '');
      var parsed = parseFloat(normalized);
      return isFinite(parsed) ? parsed : 0;
    }
    return 0;
  }

  function roundMoney(value) {
    return Math.round((safeNumber(value) + Number.EPSILON) * 100) / 100;
  }

  function findCartProduct(cart, productId, attributeId) {
    if (!cart || !Array.isArray(cart.products)) {
      return null;
    }
    var id = parseInt(productId, 10) || 0;
    var attr = parseInt(attributeId, 10) || 0;
    for (var i = 0; i < cart.products.length; i++) {
      var p = cart.products[i] || {};
      var pid = parseInt(p.id_product || p.id, 10) || 0;
      var paid = parseInt(p.id_product_attribute || 0, 10) || 0;
      if (pid === id && paid === attr) {
        return p;
      }
    }
    return null;
  }

  function inferPrice(product, fallback) {
    if (product) {
      var candidates = [
        product.price_amount,
        product.price_with_reduction,
        product.price_wt,
        product.unit_price_full,
        product.price
      ];
      for (var i = 0; i < candidates.length; i++) {
        var n = safeNumber(candidates[i]);
        if (n > 0) {
          return n;
        }
      }
    }
    return safeNumber(fallback);
  }

  function initialViewContentFor(productId) {
    var events = Array.isArray(config.initialEvents) ? config.initialEvents : [];
    for (var i = 0; i < events.length; i++) {
      var eventObject = events[i];
      if (eventObject && eventObject.name === 'ViewContent' && eventObject.params && Array.isArray(eventObject.params.content_ids)) {
        if (String(eventObject.params.content_ids[0]) === String(productId)) {
          return eventObject;
        }
      }
    }
    return null;
  }

  function captureAddToCartClick(event) {
    var target = event.target && event.target.closest ? event.target.closest('[data-button-action="add-to-cart"]') : null;
    if (!target) {
      return;
    }

    var form = target.closest ? target.closest('form') : null;
    var idInput = form ? form.querySelector('[name="id_product"]') : null;
    var attributeInput = form ? form.querySelector('[name="id_product_attribute"]') : null;
    var qtyInput = form ? form.querySelector('[name="qty"], #quantity_wanted') : null;

    state.pendingAdd = {
      idProduct: idInput ? parseInt(idInput.value, 10) || 0 : parseInt(target.getAttribute('data-id-product'), 10) || 0,
      idProductAttribute: attributeInput ? parseInt(attributeInput.value, 10) || 0 : 0,
      quantity: qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1,
      capturedAt: Date.now()
    };
  }

  function onUpdateCart(event) {
    if (!config.events || !config.events.addToCart || !event || !event.reason || event.reason.linkAction !== 'add-to-cart') {
      return;
    }

    var reason = event.reason;
    var productId = parseInt(reason.idProduct, 10) || 0;
    var attributeId = parseInt(reason.idProductAttribute, 10) || 0;
    if (!productId) {
      return;
    }

    var pending = state.pendingAdd;
    var quantity = 1;
    if (pending && pending.idProduct === productId && (Date.now() - pending.capturedAt) < 15000) {
      quantity = pending.quantity || 1;
      if (!attributeId && pending.idProductAttribute) {
        attributeId = pending.idProductAttribute;
      }
    }
    state.pendingAdd = null;

    var cart = reason.cart || (event.resp && event.resp.cart) || null;
    var product = findCartProduct(cart, productId, attributeId);
    var viewContent = initialViewContentFor(productId);
    var fallbackPrice = viewContent && viewContent.params ? viewContent.params.value : 0;
    var itemPrice = inferPrice(product, fallbackPrice);
    var contentName = product && product.name ? String(product.name) : (viewContent && viewContent.params ? viewContent.params.content_name : undefined);
    var params = {
      content_ids: [String(productId)],
      content_type: 'product',
      contents: [{ id: String(productId), quantity: quantity, item_price: roundMoney(itemPrice) }],
      value: roundMoney(itemPrice * quantity),
      currency: config.currency || 'EUR'
    };
    if (contentName) {
      params.content_name = contentName;
    }

    trackEventObject({
      name: 'AddToCart',
      params: params,
      eventId: createEventId('addtocart_' + productId)
    });
  }

  function bindPrestashopEvents(attempt) {
    if (state.prestashopBound) {
      return;
    }
    if (window.prestashop && typeof window.prestashop.on === 'function') {
      window.prestashop.on('updateCart', onUpdateCart);
      state.prestashopBound = true;
      log('Bound to PrestaShop updateCart event.');
      return;
    }
    if ((attempt || 0) < 50) {
      window.setTimeout(function () {
        bindPrestashopEvents((attempt || 0) + 1);
      }, 100);
    } else {
      warn('PrestaShop event bus was not available; AddToCart tracking cannot be bound on this theme.');
    }
  }

  window.MetaPixelBridge = {
    track: function (name, params, eventId) {
      trackEventObject({ name: name, params: params || {}, eventId: eventId || createEventId(String(name).toLowerCase()) });
    },
    trackEventObject: trackEventObject,
    refreshConsent: refreshConsent,
    getState: function () {
      return {
        consentGranted: state.consentGranted,
        pixelLoaded: state.pixelLoaded,
        pixelInitialized: state.pixelInitialized,
        queuedEvents: state.queue.length,
        dryRun: !!config.dryRun
      };
    }
  };

  window.MetaPixelBridgeConsent = {
    grant: function () { grantConsent('manual-api'); },
    revoke: function () { revokeConsent('manual-api'); },
    refresh: refreshConsent
  };

  document.addEventListener('click', captureAddToCartClick, true);
  document.addEventListener('metapixelbridge:consent', function (event) {
    if (event && event.detail && event.detail.granted === true) {
      grantConsent('custom-event');
    } else {
      revokeConsent('custom-event');
    }
  });

  var initial = Array.isArray(config.initialEvents) ? config.initialEvents : [];
  for (var i = 0; i < initial.length; i++) {
    state.queue.push(normalizeEvent(initial[i]));
  }

  var injectedQueue = Array.isArray(window.MetaPixelBridgeQueue) ? window.MetaPixelBridgeQueue.slice() : [];
  window.MetaPixelBridgeQueue = [];
  for (var q = 0; q < injectedQueue.length; q++) {
    state.queue.push(normalizeEvent(injectedQueue[q]));
  }

  bindPrestashopEvents(0);
  refreshConsent();

  if (config.consent && (config.consent.mode === 'cookie' || config.consent.mode === 'localstorage')) {
    window.setInterval(refreshConsent, 1000);
  }

  window.addEventListener('storage', function (event) {
    if (config.consent && config.consent.mode === 'localstorage' && event && event.key === config.consent.storageKey) {
      refreshConsent();
    }
  });

  log('Ready', window.MetaPixelBridge.getState(), config);
})(window, document);
