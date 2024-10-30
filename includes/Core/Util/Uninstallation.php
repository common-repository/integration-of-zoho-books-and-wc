<?php

namespace FormInteg\BIT_WC_ZOHO_BOOKS\Core\Util;

/**
 * Class handling plugin uninstallation.
 *
 * @since 1.0.0
 * @access private
 * @ignore
 */
final class Uninstallation
{
    /**
     * Reset object.
     *
     * @since 1.0.0
     * @var Reset
     */
    private $reset;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     *
     */
    public function __construct()
    {
    }

    /**
     * Registers functionality through WordPress hooks.
     *
     * @since 1.0.0
     */
    public function register()
    {
        add_action(
            'bit_wc_zoho_books_uninstall',
            [$this, 'deleteTable']
        );
    }

    public function deleteTable()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bit_wc_zoho_books_integration");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bit_wc_zoho_books_log");
    }
}
