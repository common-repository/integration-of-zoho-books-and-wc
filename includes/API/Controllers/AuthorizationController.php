<?php

namespace FormInteg\BIT_WC_ZOHO_BOOKS\API\Controllers;

class AuthorizationController
{
    public function Authorization()
    {
        $code = urlencode($_GET['code']);
        $location = urlencode($_GET['location']);
        $accountServer = $_GET['accounts-server'];
        $url = admin_url("/admin.php?page=bit_wc_zoho_books#/?code={$code}&location={$location}&accounts-server={$accountServer}");
        if (wp_safe_redirect($url)) {
            exit;
        }

        if (!headers_sent()) {
            header('Content-Type: text/html');
        }

        echo "<script type='text/javascript'>window.location='$url'</script><a href='$url'>please click here to redirect</a>";
        exit;
        // header('Location:' . $url);
    }
}
