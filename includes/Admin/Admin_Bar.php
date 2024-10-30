<?php

namespace FormInteg\BIT_WC_ZOHO_BOOKS\Admin;

/**
 * The admin menu and page handler class
 */
class Admin_Bar
{
    public function register()
    {
        add_action('init', [$this, 'register_post_type']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
    }

    /**
     * Register the admin menu
     *
     * @return void
     */
    public function register_admin_menu()
    {
        $capability = apply_filters('bit_wc_zoho_books_form_access_capability', 'manage_options');
        $rootExists = !empty($GLOBALS['admin_page_hooks']['Integration of Zoho Books and WooCommerce']);
        if ($rootExists) {
            remove_menu_page('Integration of Zoho Books and WooCommerce');
        }
        $hook = add_menu_page(__('Integration of Zoho Books and WooCommerce - The Best Plugin for Integrating with Zoho Books with WooCommerce', 'Integration of Zoho Books and WooCommerce'), 'Integration of Zoho Books and WooCommerce', $capability, 'bit_wc_zoho_books', [$this, 'table_home_page'], 'data:image/svg+xml;base64,' . base64_encode('<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20010904//EN"
 "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="1250.000000pt" height="1250.000000pt" viewBox="0 0 1250.000000 1250.000000"
 preserveAspectRatio="xMidYMid meet">

<g transform="translate(0.000000,1250.000000) scale(0.100000,-0.100000)"
fill="#000000" stroke="none">
<path d="M1395 12493 c-372 -45 -686 -195 -940 -448 -223 -224 -373 -508 -431
-820 -18 -97 -19 -256 -19 -4975 0 -4719 1 -4878 19 -4975 89 -480 400 -894
831 -1109 130 -65 281 -116 420 -142 97 -18 256 -19 4975 -19 4719 0 4878 1
4975 19 480 89 894 400 1109 831 65 130 116 281 142 420 18 97 19 256 19 4975
0 4719 -1 4878 -19 4975 -58 312 -208 596 -431 820 -217 216 -480 358 -794
428 l-96 21 -4865 2 c-2676 0 -4878 -1 -4895 -3z m6747 -4452 l3 -590 70 45
c283 184 670 294 1034 294 625 0 1173 -271 1566 -774 263 -338 395 -732 395
-1181 0 -501 -150 -908 -471 -1280 -270 -312 -613 -527 -995 -623 -172 -43
-286 -56 -489 -56 -200 0 -314 13 -485 54 -219 54 -394 131 -587 260 -405 268
-710 688 -823 1130 -57 222 -53 108 -57 1773 l-4 1537 421 0 420 0 2 -589z
m-4725 -1193 l943 -943 0 903 c0 496 3 902 7 902 5 0 414 -406 910 -902 l903
-903 0 943 0 942 420 0 420 0 0 -1957 0 -1958 -903 903 c-496 496 -905 902
-909 902 -5 0 -9 -405 -10 -900 l-3 -900 -1955 1955 -1955 1955 595 0 595 0
942 -942z"/>
<path d="M9120 6940 c-246 -26 -495 -151 -672 -338 -208 -221 -308 -471 -308
-772 0 -193 36 -346 123 -512 142 -275 396 -482 692 -565 75 -21 119 -26 260
-30 148 -4 184 -2 275 17 337 69 631 304 781 624 121 259 131 607 25 872 -138
347 -446 612 -797 684 -109 23 -269 31 -379 20z m385 -179 c639 -178 915 -925
544 -1472 -169 -249 -442 -402 -744 -416 -145 -7 -236 6 -368 51 -144 49 -244
113 -362 231 -139 139 -218 281 -261 470 -27 116 -25 311 4 428 91 365 372
640 738 721 104 23 343 16 449 -13z"/>
<path d="M8620 5955 l0 -35 97 0 98 0 -120 -120 -120 -120 178 0 177 0 0 30 0
30 -102 0 -103 0 125 125 125 125 -178 0 -177 0 0 -35z"/>
<path d="M9075 5981 c-85 -21 -122 -80 -117 -185 2 -19 16 -43 46 -72 42 -43
45 -44 106 -44 61 0 64 1 106 44 42 42 44 46 44 105 0 56 -3 64 -36 101 -33
36 -47 44 -104 55 -8 1 -28 0 -45 -4z m99 -92 c35 -41 34 -83 -3 -120 -37 -37
-79 -38 -120 -3 -25 20 -31 34 -31 64 0 84 101 123 154 59z"/>
<path d="M9290 5835 l0 -155 30 0 c28 0 29 1 32 58 l3 57 93 3 92 3 0 -61 0
-60 30 0 30 0 0 155 0 155 -30 0 -30 0 0 -65 0 -66 -92 3 -93 3 -3 63 c-3 61
-3 62 -32 62 l-30 0 0 -155z"/>
<path d="M9750 5983 c-34 -7 -87 -48 -105 -83 -8 -16 -15 -48 -15 -72 0 -164
237 -207 295 -54 44 116 -53 231 -175 209z m82 -74 c11 -6 26 -26 34 -45 12
-29 12 -39 0 -68 -36 -87 -157 -69 -173 26 -5 32 -1 41 25 68 32 32 76 39 114
19z"/>
</g>
</svg>
'), 56);

        add_action('load-' . $hook, [$this, 'load_assets']);
    }

    /**
     * Load the asset libraries
     *
     * @return void
     */
    public function load_assets()
    {
        /*  require_once dirname( __FILE__ ) . '/class-form-builder-assets.php';
        new BIT_WC_ZOHO_BOOKS_Form_Builder_Assets(); */
    }

    /**
     * The contact form page handler
     *
     * @return void
     */
    public function table_home_page()
    {
        require_once BIT_WC_ZOHO_BOOKS_PLUGIN_DIR_PATH . '/views/view-root.php';

        global $wp_rewrite;
        $api = [
            'base'      => get_rest_url() . 'bitwcbookszoho/v1',
            'separator' => $wp_rewrite->permalink_structure ? '?' : '&'
        ];
        $parsed_url = parse_url(get_admin_url());

        $base_apth_admin = str_replace($parsed_url['scheme'] . '://' . $parsed_url['host'], '', get_admin_url());
        wp_enqueue_script('bit_wc_zoho_books-admin-script', BIT_WC_ZOHO_BOOKS_ASSET_URI . '/js/index.js');
        $bit_wc_zoho_books = [
            'nonce'           => wp_create_nonce('bit_wc_zoho_books'),
            'confirm'         => __('Are you sure?', 'bit_wc_zoho_books'),
            'isPro'           => false,
            'routeComponents' => ['default' => null],
            'mixins'          => ['default' => null],
            'assetsURL'       => BIT_WC_ZOHO_BOOKS_ASSET_URI . '/js/',
            'baseURL'         => $base_apth_admin . 'admin.php?page=bit_wc_zoho_books#/',
            'ajaxURL'         => admin_url('admin-ajax.php'),
            'api'             => $api,
        ];
        wp_localize_script('bit_wc_zoho_books-admin-script', 'bit_wc_zoho_books', $bit_wc_zoho_books);
    }

}
