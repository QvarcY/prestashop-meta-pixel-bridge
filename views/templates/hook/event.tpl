<script>
(function (eventData) {
  window.MetaPixelBridgeQueue = window.MetaPixelBridgeQueue || [];
  if (window.MetaPixelBridge && typeof window.MetaPixelBridge.trackEventObject === 'function') {
    window.MetaPixelBridge.trackEventObject(eventData);
  } else {
    window.MetaPixelBridgeQueue.push(eventData);
  }
})({$mpb_event_json nofilter});
</script>
