<?php
/**
 * Meta Pixel Bridge for PrestaShop
 *
 * Privacy-aware Meta Pixel integration for PrestaShop.
 *
 * @author    CraftIN / QvarcY (kas.id.lv)
 * @contact   info@craftin.lv
 * @website   https://kas.id.lv/
 * * @copyright 2026 CraftIN / QvarcY (kas.id.lv)
 * @license   MIT
 *
 * Unofficial integration.
 * Meta and Facebook are trademarks of Meta Platforms, Inc.
 * PrestaShop is a trademark of PrestaShop SA.
 *
 * This project is not affiliated with, endorsed by, or officially
 * connected to Meta Platforms, Inc. or PrestaShop SA.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class MetaPixelBridge extends Module
{
    const CFG_ENABLED = 'MPB_ENABLED';
    const CFG_PIXEL_ID = 'MPB_PIXEL_ID';
    const CFG_DRY_RUN = 'MPB_DRY_RUN';
    const CFG_DEBUG = 'MPB_DEBUG';
    const CFG_RESPECT_GPC = 'MPB_RESPECT_GPC';
    const CFG_CONSENT_MODE = 'MPB_CONSENT_MODE';
    const CFG_CONSENT_COOKIE = 'MPB_CONSENT_COOKIE';
    const CFG_CONSENT_VALUES = 'MPB_CONSENT_VALUES';
    const CFG_CONSENT_MATCH = 'MPB_CONSENT_MATCH';
    const CFG_CONSENT_STORAGE_KEY = 'MPB_CONSENT_STORAGE_KEY';
    const CFG_CONSENT_STORAGE_FIELD = 'MPB_CONSENT_STORAGE_FIELD';
    const CFG_CONSENT_STORAGE_EXPIRY = 'MPB_CONSENT_STORAGE_EXPIRY';
    const CFG_EVENT_PAGEVIEW = 'MPB_EVENT_PAGEVIEW';
    const CFG_EVENT_VIEWCONTENT = 'MPB_EVENT_VIEWCONTENT';
    const CFG_EVENT_ADDTOCART = 'MPB_EVENT_ADDTOCART';
    const CFG_EVENT_INITCHECKOUT = 'MPB_EVENT_INITCHECKOUT';
    const CFG_EVENT_SEARCH = 'MPB_EVENT_SEARCH';
    const CFG_EVENT_PURCHASE = 'MPB_EVENT_PURCHASE';

    public function __construct()
    {
        $this->name = 'metapixelbridge';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.1';
        $this->author = 'CraftIN / QvarcY (kas.id.lv)';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => '8.99.99',
        ];

        parent::__construct();

        $this->displayName = $this->l('Meta Pixel Bridge');
        $this->description = $this->l('Privacy-aware Meta Pixel event tracking for PrestaShop without a SaaS subscription.');
        $this->confirmUninstall = $this->l('Remove Meta Pixel Bridge and all of its saved settings?');
    }

    public function install()
    {
        return parent::install()
            && $this->installConfiguration()
            && $this->registerHook([
                'actionFrontControllerSetMedia',
                'displayHeader',
                'displayOrderConfirmation',
            ]);
    }

    public function uninstall()
    {
        return $this->deleteConfiguration() && parent::uninstall();
    }

    protected function installConfiguration()
    {
        $defaults = [
            self::CFG_ENABLED => 0,
            self::CFG_PIXEL_ID => '',
            self::CFG_DRY_RUN => 1,
            self::CFG_DEBUG => 1,
            self::CFG_RESPECT_GPC => 1,
            self::CFG_CONSENT_MODE => 'cookie',
            self::CFG_CONSENT_COOKIE => '',
            self::CFG_CONSENT_VALUES => 'accepted,marketing,1,true,yes',
            self::CFG_CONSENT_MATCH => 'exact',
            self::CFG_CONSENT_STORAGE_KEY => '',
            self::CFG_CONSENT_STORAGE_FIELD => 'marketing',
            self::CFG_CONSENT_STORAGE_EXPIRY => 'valid_until',
            self::CFG_EVENT_PAGEVIEW => 1,
            self::CFG_EVENT_VIEWCONTENT => 1,
            self::CFG_EVENT_ADDTOCART => 1,
            self::CFG_EVENT_INITCHECKOUT => 1,
            self::CFG_EVENT_SEARCH => 1,
            self::CFG_EVENT_PURCHASE => 0,
        ];

        foreach ($defaults as $key => $value) {
            if (!Configuration::updateValue($key, $value)) {
                return false;
            }
        }

        return true;
    }

    protected function deleteConfiguration()
    {
        $keys = [
            self::CFG_ENABLED,
            self::CFG_PIXEL_ID,
            self::CFG_DRY_RUN,
            self::CFG_DEBUG,
            self::CFG_RESPECT_GPC,
            self::CFG_CONSENT_MODE,
            self::CFG_CONSENT_COOKIE,
            self::CFG_CONSENT_VALUES,
            self::CFG_CONSENT_MATCH,
            self::CFG_CONSENT_STORAGE_KEY,
            self::CFG_CONSENT_STORAGE_FIELD,
            self::CFG_CONSENT_STORAGE_EXPIRY,
            self::CFG_EVENT_PAGEVIEW,
            self::CFG_EVENT_VIEWCONTENT,
            self::CFG_EVENT_ADDTOCART,
            self::CFG_EVENT_INITCHECKOUT,
            self::CFG_EVENT_SEARCH,
            self::CFG_EVENT_PURCHASE,
        ];

        $ok = true;
        foreach ($keys as $key) {
            $ok = Configuration::deleteByName($key) && $ok;
        }

        return $ok;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitMetaPixelBridge')) {
            $errors = $this->validateSettings();
            if (empty($errors)) {
                $this->saveSettings();
                $output .= $this->displayConfirmation($this->l('Settings saved.'));
            } else {
                foreach ($errors as $error) {
                    $output .= $this->displayError($error);
                }
            }
        }

        $output .= $this->renderStatusPanel();
        $output .= $this->renderForm();

        return $output;
    }

    protected function validateSettings()
    {
        $errors = [];
        $enabled = (bool) Tools::getValue(self::CFG_ENABLED);
        $pixelId = trim((string) Tools::getValue(self::CFG_PIXEL_ID));
        $consentMode = (string) Tools::getValue(self::CFG_CONSENT_MODE);
        $cookieName = trim((string) Tools::getValue(self::CFG_CONSENT_COOKIE));
        $matchMode = (string) Tools::getValue(self::CFG_CONSENT_MATCH);
        $storageKey = trim((string) Tools::getValue(self::CFG_CONSENT_STORAGE_KEY));
        $storageField = trim((string) Tools::getValue(self::CFG_CONSENT_STORAGE_FIELD));

        if ($pixelId !== '' && !preg_match('/^[0-9]{5,30}$/', $pixelId)) {
            $errors[] = $this->l('Pixel / Dataset ID must contain digits only.');
        }

        if ($enabled && $pixelId === '') {
            $errors[] = $this->l('Enter the Pixel / Dataset ID before enabling tracking.');
        }

        if (!in_array($consentMode, ['cookie', 'localstorage', 'manual', 'always'], true)) {
            $errors[] = $this->l('Invalid consent mode.');
        }

        if ($enabled && $consentMode === 'cookie' && $cookieName === '') {
            $errors[] = $this->l('Cookie consent mode requires a marketing-consent cookie name.');
        }

        if ($enabled && $consentMode === 'localstorage' && $storageKey === '') {
            $errors[] = $this->l('LocalStorage consent mode requires a storage key.');
        }

        if ($enabled && $consentMode === 'localstorage' && $storageField === '') {
            $errors[] = $this->l('LocalStorage consent mode requires a marketing field path.');
        }

        if (!in_array($matchMode, ['exact', 'contains'], true)) {
            $errors[] = $this->l('Invalid cookie matching mode.');
        }

        return $errors;
    }

    protected function saveSettings()
    {
        $booleanKeys = [
            self::CFG_ENABLED,
            self::CFG_DRY_RUN,
            self::CFG_DEBUG,
            self::CFG_RESPECT_GPC,
            self::CFG_EVENT_PAGEVIEW,
            self::CFG_EVENT_VIEWCONTENT,
            self::CFG_EVENT_ADDTOCART,
            self::CFG_EVENT_INITCHECKOUT,
            self::CFG_EVENT_SEARCH,
            self::CFG_EVENT_PURCHASE,
        ];

        foreach ($booleanKeys as $key) {
            Configuration::updateValue($key, (int) (bool) Tools::getValue($key));
        }

        Configuration::updateValue(self::CFG_PIXEL_ID, trim((string) Tools::getValue(self::CFG_PIXEL_ID)));
        Configuration::updateValue(self::CFG_CONSENT_MODE, (string) Tools::getValue(self::CFG_CONSENT_MODE));
        Configuration::updateValue(self::CFG_CONSENT_COOKIE, trim((string) Tools::getValue(self::CFG_CONSENT_COOKIE)));
        Configuration::updateValue(self::CFG_CONSENT_VALUES, trim((string) Tools::getValue(self::CFG_CONSENT_VALUES)));
        Configuration::updateValue(self::CFG_CONSENT_MATCH, (string) Tools::getValue(self::CFG_CONSENT_MATCH));
        Configuration::updateValue(self::CFG_CONSENT_STORAGE_KEY, trim((string) Tools::getValue(self::CFG_CONSENT_STORAGE_KEY)));
        Configuration::updateValue(self::CFG_CONSENT_STORAGE_FIELD, trim((string) Tools::getValue(self::CFG_CONSENT_STORAGE_FIELD)));
        Configuration::updateValue(self::CFG_CONSENT_STORAGE_EXPIRY, trim((string) Tools::getValue(self::CFG_CONSENT_STORAGE_EXPIRY)));
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMetaPixelBridge';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        $fieldsForm = [[
            'form' => [
                'legend' => [
                    'title' => $this->l('Meta Pixel Bridge settings'),
                    'icon' => 'icon-signal',
                ],
                'description' => $this->l('Unofficial Meta Pixel integration. No advanced matching or customer PII is sent by this version.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable tracking'),
                        'name' => self::CFG_ENABLED,
                        'is_bool' => true,
                        'values' => $this->switchValues(),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Pixel / Dataset ID'),
                        'name' => self::CFG_PIXEL_ID,
                        'required' => false,
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Numeric ID from Meta Events Manager. The module remains inactive until this is set.'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Dry-run mode'),
                        'name' => self::CFG_DRY_RUN,
                        'is_bool' => true,
                        'values' => $this->switchValues(),
                        'desc' => $this->l('Recommended while configuring. Events are logged in the browser console but are not sent to Meta.'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Debug logging'),
                        'name' => self::CFG_DEBUG,
                        'is_bool' => true,
                        'values' => $this->switchValues(),
                        'desc' => $this->l('Writes tracker state and event payloads to the browser developer console.'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Respect GPC / Do Not Track'),
                        'name' => self::CFG_RESPECT_GPC,
                        'is_bool' => true,
                        'values' => $this->switchValues(),
                        'desc' => $this->l('When enabled, browser Global Privacy Control or Do Not Track prevents tracking.'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Consent source'),
                        'name' => self::CFG_CONSENT_MODE,
                        'options' => [
                            'query' => [
                                ['id' => 'localstorage', 'name' => $this->l('LocalStorage JSON consent (recommended when supported)')],
                                ['id' => 'cookie', 'name' => $this->l('Marketing-consent cookie')],
                                ['id' => 'manual', 'name' => $this->l('Manual JavaScript API')],
                                ['id' => 'always', 'name' => $this->l('Always granted (only when consent is handled elsewhere)')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('LocalStorage key'),
                        'name' => self::CFG_CONSENT_STORAGE_KEY,
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Used only in LocalStorage JSON mode. Example: craftin_cookie_consent_v1.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Marketing field path'),
                        'name' => self::CFG_CONSENT_STORAGE_FIELD,
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Dot-separated JSON property path whose value grants marketing consent. Example: marketing.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Expiry field path'),
                        'name' => self::CFG_CONSENT_STORAGE_EXPIRY,
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Optional Unix timestamp field. Expired consent is treated as denied. Example: valid_until.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Consent cookie name'),
                        'name' => self::CFG_CONSENT_COOKIE,
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Used only in cookie mode. The Meta script is not requested until the cookie matches an accepted value.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Accepted consent values'),
                        'name' => self::CFG_CONSENT_VALUES,
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Used for cookie and LocalStorage modes. Comma-separated, case-insensitive values, for example: accepted,1,true,yes.'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Consent value matching'),
                        'name' => self::CFG_CONSENT_MATCH,
                        'options' => [
                            'query' => [
                                ['id' => 'exact', 'name' => $this->l('Exact value')],
                                ['id' => 'contains', 'name' => $this->l('Stored value contains one accepted value')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ], [
            'form' => [
                'legend' => [
                    'title' => $this->l('Standard events'),
                    'icon' => 'icon-list',
                ],
                'input' => [
                    $this->eventSwitch(self::CFG_EVENT_PAGEVIEW, 'PageView', $this->l('Every page view.')),
                    $this->eventSwitch(self::CFG_EVENT_VIEWCONTENT, 'ViewContent', $this->l('Product detail pages with product ID, name, value and currency.')),
                    $this->eventSwitch(self::CFG_EVENT_ADDTOCART, 'AddToCart', $this->l('Successful PrestaShop add-to-cart AJAX actions.')),
                    $this->eventSwitch(self::CFG_EVENT_INITCHECKOUT, 'InitiateCheckout', $this->l('Checkout entry, once per cart in the browser session.')),
                    $this->eventSwitch(self::CFG_EVENT_SEARCH, 'Search', $this->l('Store search result pages.')),
                    $this->eventSwitch(self::CFG_EVENT_PURCHASE, 'Purchase (browser order confirmation)', $this->l('Sends Purchase when the confirmation page is shown. For delayed bank-transfer/check payments this means order placed, not necessarily payment received; keep it disabled if you need paid-only revenue.')),
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ]];

        return $helper->generateForm($fieldsForm);
    }

    protected function eventSwitch($name, $label, $desc)
    {
        return [
            'type' => 'switch',
            'label' => $label,
            'name' => $name,
            'is_bool' => true,
            'values' => $this->switchValues(),
            'desc' => $desc,
        ];
    }

    protected function switchValues()
    {
        return [
            ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')],
            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled')],
        ];
    }

    protected function getFormValues()
    {
        $keys = [
            self::CFG_ENABLED,
            self::CFG_PIXEL_ID,
            self::CFG_DRY_RUN,
            self::CFG_DEBUG,
            self::CFG_RESPECT_GPC,
            self::CFG_CONSENT_MODE,
            self::CFG_CONSENT_COOKIE,
            self::CFG_CONSENT_VALUES,
            self::CFG_CONSENT_MATCH,
            self::CFG_CONSENT_STORAGE_KEY,
            self::CFG_CONSENT_STORAGE_FIELD,
            self::CFG_CONSENT_STORAGE_EXPIRY,
            self::CFG_EVENT_PAGEVIEW,
            self::CFG_EVENT_VIEWCONTENT,
            self::CFG_EVENT_ADDTOCART,
            self::CFG_EVENT_INITCHECKOUT,
            self::CFG_EVENT_SEARCH,
            self::CFG_EVENT_PURCHASE,
        ];

        $values = [];
        foreach ($keys as $key) {
            $values[$key] = Tools::isSubmit('submitMetaPixelBridge') ? Tools::getValue($key) : Configuration::get($key);
        }

        return $values;
    }

    protected function renderStatusPanel()
    {
        $enabled = (bool) Configuration::get(self::CFG_ENABLED);
        $pixelId = trim((string) Configuration::get(self::CFG_PIXEL_ID));
        $dryRun = (bool) Configuration::get(self::CFG_DRY_RUN);
        $consentMode = (string) Configuration::get(self::CFG_CONSENT_MODE);
        $cookieName = trim((string) Configuration::get(self::CFG_CONSENT_COOKIE));
        $storageKey = trim((string) Configuration::get(self::CFG_CONSENT_STORAGE_KEY));
        $storageField = trim((string) Configuration::get(self::CFG_CONSENT_STORAGE_FIELD));
        $pixelOk = (bool) preg_match('/^[0-9]{5,30}$/', $pixelId);
        $consentOk = ($consentMode === 'cookie') ? ($cookieName !== '') : (($consentMode === 'localstorage') ? ($storageKey !== '' && $storageField !== '') : true);

        $stateClass = ($enabled && $pixelOk && $consentOk) ? 'alert-success' : 'alert-warning';
        $stateText = ($enabled && $pixelOk && $consentOk)
            ? $this->l('Configuration is ready for front-office tracking.')
            : $this->l('Tracking is not fully active yet. Review the items below.');

        $html = '<div class="panel">';
        $html .= '<h3><i class="icon-signal"></i> ' . $this->l('Meta Pixel Bridge diagnostics') . '</h3>';
        $html .= '<div class="alert ' . $stateClass . '"><strong>' . $stateText . '</strong></div>';
        $html .= '<table class="table">';
        $html .= $this->statusRow($this->l('Module'), $enabled ? $this->l('Enabled') : $this->l('Disabled'), $enabled);
        $html .= $this->statusRow($this->l('Pixel / Dataset ID'), $pixelOk ? Tools::safeOutput($pixelId) : $this->l('Not configured'), $pixelOk);
        $html .= $this->statusRow($this->l('Consent mode'), Tools::safeOutput($consentMode), $consentOk);
        $html .= $this->statusRow($this->l('Dry-run'), $dryRun ? $this->l('ON — no Meta requests are sent') : $this->l('OFF — live sending allowed after consent'), !$dryRun);
        $html .= $this->statusRow($this->l('Conversions API'), $this->l('Reserved for the next version; browser event IDs are already CAPI-ready.'), true);
        $html .= '</table>';
        $html .= '<p><strong>' . $this->l('Manual consent API:') . '</strong> <code>MetaPixelBridgeConsent.grant()</code> / <code>MetaPixelBridgeConsent.revoke()</code></p>';
        $html .= '<p>' . $this->l('No noscript tracking image and no advanced matching are used. Before consent, the Meta fbevents.js file is not requested at all.') . '</p>';
        $html .= '</div>';

        return $html;
    }

    protected function statusRow($label, $value, $ok)
    {
        return '<tr><td style="width:220px"><strong>' . Tools::safeOutput($label) . '</strong></td><td>'
            . ($ok ? '<span class="label label-success">OK</span> ' : '<span class="label label-warning">CHECK</span> ')
            . $value . '</td></tr>';
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        if (!$this->shouldAttachFrontend()) {
            return;
        }

        $this->context->controller->registerJavascript(
            'module-' . $this->name . '-front',
            'modules/' . $this->name . '/views/js/front.js',
            [
                'position' => 'bottom',
                'priority' => 150,
                'version' => $this->version,
            ]
        );
    }

    public function hookDisplayHeader($params)
    {
        if (!$this->shouldAttachFrontend()) {
            return '';
        }

        $config = $this->buildFrontendConfig();
        $this->context->smarty->assign([
            'mpb_config_json' => $this->jsonForHtml($config),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/config.tpl');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (!$this->shouldAttachFrontend() || !(bool) Configuration::get(self::CFG_EVENT_PURCHASE)) {
            return '';
        }

        if (empty($params['order']) || !Validate::isLoadedObject($params['order'])) {
            return '';
        }

        $event = $this->buildPurchaseEvent($params['order']);
        if (!$event) {
            return '';
        }

        $this->context->smarty->assign([
            'mpb_event_json' => $this->jsonForHtml($event),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/event.tpl');
    }

    protected function shouldAttachFrontend()
    {
        return (bool) Configuration::get(self::CFG_ENABLED)
            && trim((string) Configuration::get(self::CFG_PIXEL_ID)) !== '';
    }

    protected function buildFrontendConfig()
    {
        $currency = $this->context->currency && $this->context->currency->iso_code
            ? (string) $this->context->currency->iso_code
            : 'EUR';
        $controller = $this->getControllerName();
        $cartId = $this->context->cart ? (int) $this->context->cart->id : 0;
        $initialEvents = [];

        if ((bool) Configuration::get(self::CFG_EVENT_PAGEVIEW)) {
            $initialEvents[] = [
                'name' => 'PageView',
                'params' => new stdClass(),
                'eventId' => $this->makeRequestEventId('pageview'),
            ];
        }

        if ((bool) Configuration::get(self::CFG_EVENT_VIEWCONTENT) && $controller === 'product') {
            $productEvent = $this->buildViewContentEvent($currency);
            if ($productEvent) {
                $initialEvents[] = $productEvent;
            }
        }

        if ((bool) Configuration::get(self::CFG_EVENT_SEARCH) && $controller === 'search') {
            $query = trim((string) Tools::getValue('s', Tools::getValue('search_query', '')));
            if ($query !== '') {
                $initialEvents[] = [
                    'name' => 'Search',
                    'params' => ['search_string' => $query],
                    'eventId' => $this->makeRequestEventId('search'),
                ];
            }
        }

        if ((bool) Configuration::get(self::CFG_EVENT_INITCHECKOUT) && $this->isCheckoutController($controller)) {
            $checkoutEvent = $this->buildCheckoutEvent($currency, $cartId);
            if ($checkoutEvent) {
                $initialEvents[] = $checkoutEvent;
            }
        }

        return [
            'module' => $this->name,
            'version' => $this->version,
            'pixelId' => (string) Configuration::get(self::CFG_PIXEL_ID),
            'dryRun' => (bool) Configuration::get(self::CFG_DRY_RUN),
            'debug' => (bool) Configuration::get(self::CFG_DEBUG),
            'respectGpc' => (bool) Configuration::get(self::CFG_RESPECT_GPC),
            'consent' => [
                'mode' => (string) Configuration::get(self::CFG_CONSENT_MODE),
                'cookieName' => (string) Configuration::get(self::CFG_CONSENT_COOKIE),
                'acceptedValues' => $this->parseAcceptedValues((string) Configuration::get(self::CFG_CONSENT_VALUES)),
                'match' => (string) Configuration::get(self::CFG_CONSENT_MATCH),
                'storageKey' => (string) Configuration::get(self::CFG_CONSENT_STORAGE_KEY),
                'storageField' => (string) Configuration::get(self::CFG_CONSENT_STORAGE_FIELD),
                'storageExpiryField' => (string) Configuration::get(self::CFG_CONSENT_STORAGE_EXPIRY),
            ],
            'events' => [
                'pageView' => (bool) Configuration::get(self::CFG_EVENT_PAGEVIEW),
                'viewContent' => (bool) Configuration::get(self::CFG_EVENT_VIEWCONTENT),
                'addToCart' => (bool) Configuration::get(self::CFG_EVENT_ADDTOCART),
                'initiateCheckout' => (bool) Configuration::get(self::CFG_EVENT_INITCHECKOUT),
                'search' => (bool) Configuration::get(self::CFG_EVENT_SEARCH),
                'purchase' => (bool) Configuration::get(self::CFG_EVENT_PURCHASE),
            ],
            'currency' => $currency,
            'controller' => $controller,
            'cartId' => $cartId,
            'initialEvents' => $initialEvents,
        ];
    }

    protected function getControllerName()
    {
        if ($this->context->controller && !empty($this->context->controller->php_self)) {
            return strtolower((string) $this->context->controller->php_self);
        }

        return strtolower((string) Tools::getValue('controller', ''));
    }

    protected function isCheckoutController($controller)
    {
        return in_array($controller, ['order', 'checkout'], true);
    }

    protected function buildViewContentEvent($currency)
    {
        $idProduct = (int) Tools::getValue('id_product');
        if (!$idProduct && $this->context->controller && isset($this->context->controller->product)) {
            $idProduct = (int) $this->context->controller->product->id;
        }

        if (!$idProduct) {
            return null;
        }

        $product = new Product($idProduct, false, (int) $this->context->language->id, (int) $this->context->shop->id);
        if (!Validate::isLoadedObject($product)) {
            return null;
        }

        $idAttribute = (int) Tools::getValue('id_product_attribute');
        $price = (float) Product::getPriceStatic($idProduct, true, $idAttribute ?: null);

        return [
            'name' => 'ViewContent',
            'params' => [
                'content_ids' => [(string) $idProduct],
                'content_type' => 'product',
                'content_name' => (string) $product->name,
                'value' => $this->money($price),
                'currency' => $currency,
                'contents' => [[
                    'id' => (string) $idProduct,
                    'quantity' => 1,
                    'item_price' => $this->money($price),
                ]],
            ],
            'eventId' => $this->makeRequestEventId('viewcontent_' . $idProduct),
        ];
    }

    protected function buildCheckoutEvent($currency, $cartId)
    {
        if (!$this->context->cart || !$this->context->cart->id) {
            return null;
        }

        $products = $this->context->cart->getProducts(true);
        $contents = [];
        $contentIds = [];
        $numItems = 0;

        foreach ($products as $product) {
            $id = isset($product['id_product']) ? (int) $product['id_product'] : 0;
            $qty = isset($product['cart_quantity']) ? (int) $product['cart_quantity'] : (isset($product['quantity']) ? (int) $product['quantity'] : 1);
            $price = isset($product['price_with_reduction']) ? (float) $product['price_with_reduction'] : 0.0;
            if (!$id) {
                continue;
            }
            $contentIds[] = (string) $id;
            $numItems += max(1, $qty);
            $contents[] = [
                'id' => (string) $id,
                'quantity' => max(1, $qty),
                'item_price' => $this->money($price),
            ];
        }

        $value = (float) $this->context->cart->getOrderTotal(true, Cart::BOTH);

        return [
            'name' => 'InitiateCheckout',
            'params' => [
                'content_ids' => $contentIds,
                'content_type' => 'product',
                'contents' => $contents,
                'num_items' => $numItems,
                'value' => $this->money($value),
                'currency' => $currency,
            ],
            'eventId' => $this->makeRequestEventId('checkout_' . (int) $cartId),
            'once' => [
                'storage' => 'session',
                'key' => 'mpb_checkout_' . (int) $cartId,
            ],
        ];
    }

    protected function buildPurchaseEvent($order)
    {
        $currency = new Currency((int) $order->id_currency);
        $currencyCode = Validate::isLoadedObject($currency) ? (string) $currency->iso_code : 'EUR';
        $products = $order->getProducts();
        $contents = [];
        $contentIds = [];
        $numItems = 0;

        foreach ($products as $product) {
            $id = isset($product['product_id']) ? (int) $product['product_id'] : 0;
            $qty = isset($product['product_quantity']) ? (int) $product['product_quantity'] : 1;
            $price = isset($product['unit_price_tax_incl']) ? (float) $product['unit_price_tax_incl'] : 0.0;
            if (!$id) {
                continue;
            }
            $contentIds[] = (string) $id;
            $numItems += max(1, $qty);
            $contents[] = [
                'id' => (string) $id,
                'quantity' => max(1, $qty),
                'item_price' => $this->money($price),
            ];
        }

        $value = isset($order->total_paid_tax_incl) ? (float) $order->total_paid_tax_incl : (float) $order->total_paid;
        $eventId = 'mpb_purchase_' . (int) $this->context->shop->id . '_' . (int) $order->id;

        return [
            'name' => 'Purchase',
            'params' => [
                'content_ids' => $contentIds,
                'content_type' => 'product',
                'contents' => $contents,
                'num_items' => $numItems,
                'value' => $this->money($value),
                'currency' => $currencyCode,
                'order_id' => (string) $order->id,
            ],
            'eventId' => $eventId,
            'once' => [
                'storage' => 'local',
                'key' => 'mpb_purchase_' . (int) $this->context->shop->id . '_' . (int) $order->id,
            ],
        ];
    }

    protected function parseAcceptedValues($raw)
    {
        $parts = array_map('trim', explode(',', (string) $raw));
        $values = [];
        foreach ($parts as $part) {
            if ($part !== '') {
                $values[] = strtolower($part);
            }
        }

        return array_values(array_unique($values));
    }

    protected function makeRequestEventId($prefix)
    {
        try {
            $random = bin2hex(random_bytes(8));
        } catch (Exception $e) {
            $random = str_replace('.', '', uniqid('', true));
        }

        return 'mpb_' . preg_replace('/[^a-z0-9_\-]/i', '_', $prefix) . '_' . $random;
    }

    protected function money($value)
    {
        return (float) number_format((float) $value, 2, '.', '');
    }

    protected function jsonForHtml($value)
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
    }
}
