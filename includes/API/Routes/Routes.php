<?php

namespace FormInteg\BIT_WC_ZOHO_BOOKS\API\Routes;

use WP_REST_Controller;
use WP_REST_Server;
use FormInteg\BIT_WC_ZOHO_BOOKS\API\Controllers\AuthorizationController;

class Routes extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'bitwcbookszoho';
        $this->rest_base = 'v1';
        $this->authorizationController = new AuthorizationController();
    }

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            $this->rest_base . '/authoraization/',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this->authorizationController, 'Authorization'],
                    'permission_callback' => '__return_true'
                ],
                'schema' => [$this, 'get_item_schema']

            ]
        );
    }

    public function get_items_permissions_check($request)
    {
        $integrateData = get_option('bit_wc_zoho_books_integrate_key_data');
        $header = $request->get_header('wc-books-Api-Key');
        $api_key = get_option('bit_wcb_form_secret_api_key');
        $error = '';

        if (empty($header)) {
            $error = ['message' => 'Api Key is required to access this resource'];
        } elseif (!is_array($integrateData) && !isset($integrateData['key'])) {
            $error = 'wc-books pro License Key is Invalid';
        } elseif ($request->get_header('wc-books-Api-Key') != $api_key || $api_key == null) {
            $error = 'Invalid API key';
        }

        if (!empty($error)) {
            return wp_send_json_error(['message' => $error], 401);
        }

        return true;
    }
}
