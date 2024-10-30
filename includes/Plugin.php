<?php

namespace FormInteg\BIT_WC_ZOHO_BOOKS;

use FormInteg\BIT_WC_ZOHO_BOOKS\Admin\Admin_Bar;
use FormInteg\BIT_WC_ZOHO_BOOKS\Admin\Admin_Ajax;
use FormInteg\BIT_WC_ZOHO_BOOKS\API\Routes\Routes;
use FormInteg\BIT_WC_ZOHO_BOOKS\Core\Util\Activation;
use FormInteg\BIT_WC_ZOHO_BOOKS\Core\Util\HttpHelper;
use FormInteg\BIT_WC_ZOHO_BOOKS\Core\Util\Uninstallation;
use FormInteg\BIT_WC_ZOHO_BOOKS\Core\Util\Common;
use WC_Order;

/**
 * Main class for the plugin.
 *
 * @since 1.0.0-alpha
 */
final class Plugin
{
    /**1000.FO5535NXGC7LV25ZTTJ6LI9P3OEMCG
     * Main instance of the plugin.
     *
     * @since 1.0.0-alpha
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

    /**
     * Registers the plugin with WordPress.
     *
     * @since 1.0.0-alpha
     */
    public function register()
    {
        (new Activation())->activate();
        (new Uninstallation())->register();
        add_action('plugins_loaded', [$this, 'init_plugin']);
        add_action('rest_api_init', [$this, 'register_bf_api_routes'], 10);
        add_filter('bit_wc_zoho_books_addSalesOrder', [$this, 'addBitWcZohoBooksSalesOrder'], 100, 5);

        // Initiate the plugin on 'init'
        $this->init_plugin();
    }

    public function register_bf_api_routes()
    {
        $routes = new Routes();
        $routes->register_routes();
    }

    /*****************************frm***************************************************************** */
    /**
     * Do plugin upgrades
     *
     * @since 1.1.2
     *
     * @return void
     */
    public function plugin_upgrades()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks()
    {
        // Localize our plugin
        add_action('init', [$this, 'localization_setup']);

        // initialize the classes
        add_action('init', [$this, 'init_classes']);
        add_action('init', [$this, 'wpdb_table_shortcuts'], 0);

        add_action('woocommerce_loaded', function () {
            add_action('woocommerce_checkout_order_processed', [$this, 'executeIntegration'], 20, 2);
        });

    }

    private function woocommerce_get_order($order_id)
    {
        if (!function_exists('wc_get_order')) {
            require_once dirname(WC_PLUGIN_FILE) . '/includes/class-wc-order.php';
        }

        if (function_exists('wc_get_order')) {
            return wc_get_order($order_id);
        }

        return (object) [];
    }

    public function executeIntegration($order_id, $importType)
    {
        global $wpdb;
        $if_already_imported_result = $wpdb->get_results("SELECT COUNT(response_type) as success_count FROM {$wpdb->prefix}bit_wc_zoho_books_log WHERE order_id = {$order_id} AND response_type = 'success' GROUP BY generated_at");
        foreach ($if_already_imported_result as $res) {
            if ($res->success_count === '2') {
                return;
            }
        }
        $integ_result = $wpdb->get_results("SELECT integration_details FROM {$wpdb->prefix}bit_wc_zoho_books_integration ORDER BY id DESC LIMIT 1");

        if (!$integ_result) {
            return;
        }
        $integ_details = json_decode($integ_result[0]->integration_details);

        if (isset($integ_details->enabled) && !$integ_details->enabled) {
            return;
        }

        $order = $this->woocommerce_get_order($order_id);
        if ((intval($integ_details->tokenDetails->generates_on) + (55 * 60)) < time()) {
            $requiredParams['clientId'] = $integ_details->clientId;
            $requiredParams['clientSecret'] = $integ_details->clientSecret;
            $requiredParams['dataCenter'] = $integ_details->dataCenter;
            $requiredParams['tokenDetails'] = $integ_details->tokenDetails;
            $newTokenDetails = Admin_Ajax::refreshAccessToken((object)$requiredParams);
            if ($newTokenDetails) {
                $integ_details->tokenDetails = $newTokenDetails;
                $new_integ_details = wp_json_encode($integ_details);
                $wpdb->query("UPDATE {$wpdb->prefix}bit_wc_zoho_books_integration SET integration_details = '{$new_integ_details}' WHERE id = 1");
            }
        }

        $fieldMap = $integ_details->field_map;
        $fieldData = [];

        $woocommerceFieldValuesMap = [
            'billing_address_1'   => $order->get_billing_address_1(),
            'billing_address_2'   => $order->get_billing_address_2(),
            'billing_city'        => $order->get_billing_city(),
            'billing_company'     => $order->get_billing_company(),
            'billing_country'     => $order->get_billing_country(),
            'billing_email'       => $order->get_billing_email(),
            'billing_first_name'  => $order->get_billing_first_name(),
            'billing_last_name'   => $order->get_billing_last_name(),
            'billing_phone'       => $order->get_billing_phone(),
            'billing_postcode'    => $order->get_billing_postcode(),
            'billing_state'       => $order->get_billing_state(),
            'order_comments'      => $order->get_customer_note(),
            'shipping_address_1'  => $order->get_shipping_address_1(),
            'shipping_address_2'  => $order->get_shipping_address_2(),
            'shipping_city'       => $order->get_shipping_city(),
            'shipping_company'    => $order->get_shipping_company(),
            'shipping_country'    => $order->get_shipping_country(),
            'shipping_first_name' => $order->get_shipping_first_name(),
            'shipping_last_name'  => $order->get_shipping_last_name(),
            'shipping_postcode'   => $order->get_shipping_postcode(),
            'shipping_state'      => $order->get_shipping_state(),
            'shipping_total'      => $order->get_shipping_total()
        ];

        foreach ($fieldMap as $fldType => $fields) {
            $fieldData[$fldType] = [];
            foreach ($fields as $fieldPair) {
                if (!empty($fieldPair->zohoFormField) && !empty($fieldPair->formField)) {
                    if ($fieldPair->formField === 'custom' && isset($fieldPair->customValue)) {
                        if (strpos($fieldPair->zohoFormField, 'billing_address_') !== false || strpos($fieldPair->zohoFormField, 'shipping_address_') !== false) {
                            $fld = explode('_bit_', $fieldPair->zohoFormField);
                            $fieldData[$fldType][$fld[0]][$fld[1]] = Common::replaceFieldWithValue($fieldPair->customValue, $woocommerceFieldValuesMap);
                        } else {
                            $fieldData[$fldType][$fieldPair->zohoFormField] = Common::replaceFieldWithValue($fieldPair->customValue, $woocommerceFieldValuesMap);
                        }
                    } elseif (strpos($fieldPair->zohoFormField, 'billing_address_') !== false || strpos($fieldPair->zohoFormField, 'shipping_address_') !== false) {
                        $fld = explode('_bit_', $fieldPair->zohoFormField);
                        $fieldData[$fldType][$fld[0]][$fld[1]] = $woocommerceFieldValuesMap[$fieldPair->formField];
                    } elseif (isset($woocommerceFieldValuesMap[$fieldPair->formField])) {
                        $fieldData[$fldType][$fieldPair->zohoFormField] = $woocommerceFieldValuesMap[$fieldPair->formField];
                    } else {
                        $fieldData[$fldType][$fieldPair->zohoFormField] = isset($order->{$fieldPair->formField}) ? $order->{$fieldPair->formField} : '';
                    }
                }
            }
        }

        $defaultHeader['Authorization'] = "Zoho-oauthtoken {$integ_details->tokenDetails->access_token}";
        $defaultHeader['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
        $generated_at = uniqid();

        $customer = null;

        // Search If Customer Email already Exists
        if (isset($fieldData['booksCustomerFields']['email']) && !empty($fieldData['booksCustomerFields']['email'])) {
            $searchCustomerEndpoint = "https://books.zoho.{$integ_details->dataCenter}/api/v3/contacts?organization_id={$integ_details->orgId}&email={$fieldData['booksCustomerFields']['email']}";
            $searchCustomerResponse = HttpHelper::get($searchCustomerEndpoint, null, $defaultHeader);
            if (isset($searchCustomerResponse->code) && $searchCustomerResponse->code === 0 && isset($searchCustomerResponse->contacts) && count($searchCustomerResponse->contacts)) {
                $customer = $searchCustomerResponse->contacts[0];
            }

            // add customer email as a contact persons
            $fieldData['booksCustomerFields']['contact_persons'] = [
                (object) [
                    'email'                         => $fieldData['booksCustomerFields']['email'],
                    'first_name'                    => $fieldData['booksCustomerFields']['first_name'],
                    'last_name'                     => $fieldData['booksCustomerFields']['last_name'],
                    'mobile'                        => $fieldData['booksCustomerFields']['mobile'],
                    'phone'                         => $fieldData['booksCustomerFields']['phone'],
                    'salutation'                    => $fieldData['booksCustomerFields']['salutation'],
                    'designation'                   => $fieldData['booksCustomerFields']['designation'],
                    'department'                    => $fieldData['booksCustomerFields']['department'],
                    'is_primary_contact'            => true
                ]
            ];
        }

        if (is_null($customer)) {
            // Create a Customer
            $createCustomerEndpoint = "https://books.zoho.{$integ_details->dataCenter}/api/v3/contacts?organization_id={$integ_details->orgId}";

            $data['JSONString'] = wp_json_encode($fieldData['booksCustomerFields']);

            $createCustomerResponse = HttpHelper::post($createCustomerEndpoint, $data, $defaultHeader);
            if (isset($createCustomerResponse->code) && $createCustomerResponse->code === 0) {
                $customer = $createCustomerResponse->contact;
                $this->saveToLogDB($order_id, 'customer', 'success', $createCustomerResponse, $generated_at);
            } elseif (isset($createCustomerResponse->code) && $createCustomerResponse->code !== 0) {
                $this->saveToLogDB($order_id, 'customer', 'error', $createCustomerResponse, $generated_at);
                return;
            }
        }
    }

    private function saveToLogDB($order_id, $apiType, $respType, $respObj, $generated_at)
    {
        global $wpdb;
        $respObj = addslashes(wp_json_encode($respObj));
        $wpdb->query("INSERT INTO {$wpdb->prefix}bit_wc_zoho_books_log(order_id, api_type, response_type, response_obj, generated_at) VALUE($order_id, '{$apiType}', '{$respType}', '{$respObj}', '$generated_at')");
    }

    /**
     * Set WPDB table shortcut names
     *
     * @return void
     */
    public function wpdb_table_shortcuts()
    {
        global $wpdb;

        $wpdb->bit_wc_zoho_books_schema = $wpdb->prefix . 'bit_wc_zoho_books_schema';
        $wpdb->bit_wc_zoho_books_schema_meta = $wpdb->prefix . 'bit_wc_zoho_books_schema_meta';
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('bit_wc_zoho_books', false, BIT_WC_ZOHO_BOOKS_PLUGIN_DIR_PATH . '/lang/');
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        if ($this->is_request('admin')) {
            $this->container['admin'] = (new Admin_Bar())->register();
            $this->container['admin_ajax'] = (new Admin_Ajax())->register();
        }
    }


    /**
     * What type of request is this?
     *
     * @since 1.0.0-alpha
     *
     * @param  string $type admin, ajax, cron, api or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined('DOING_AJAX');

            case 'cron':
                return defined('DOING_CRON');

            case 'api':
                return defined('REST_REQUEST');

            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }

    public function init_plugin()
    {
        $this->init_hooks();

        do_action('bit_wc_zoho_books_loaded');
    }
    /********************************************************************************************** */

    /**
     * Retrieves the main instance of the plugin.
     *
     * @since 1.0.0-alpha
     *
     * @return BIT_WC_ZOHO_BOOKS Plugin main instance.
     */
    public static function instance()
    {
        return static::$instance;
    }

    /**
     * Loads the plugin main instance and initializes it.
     *
     * @since 1.0.0-alpha
     *
     * @param string $main_file Absolute path to the plugin main file.
     * @return bool True if the plugin main instance could be loaded, false otherwise.
     */
    public static function load($main_file)
    {
        if (null !== static::$instance) {
            return false;
        }

        static::$instance = new static($main_file);
        static::$instance->register();

        return true;
    }
}
