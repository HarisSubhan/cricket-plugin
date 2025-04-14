<?php
class Cricket_API_Handler {
    protected $api_key = '23853bc0-52f1-498c-985e-2faa764e23c4';

    protected function make_request($url) {
        $response = wp_remote_get($url, ['timeout' => 15, 'sslverify' => false]);
        if (is_wp_error($response)) return false;
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}
