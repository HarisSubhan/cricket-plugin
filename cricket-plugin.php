<?php
/**
 * Plugin Name: Cricket API Plugin
 * Description: Fetch and display cricket player data, matches, and stats via API.
 * Version: 1.0
 * Author : Sports324
 */

require_once __DIR__ . '/includes/class-api-handler.php';
require_once __DIR__ . '/includes/class-player-api.php';
require_once __DIR__ . '/includes/class-matches-api.php';
require_once __DIR__ . '/includes/class-teams-api.php';

// Enqueue scripts
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('cricket-plugin-css', plugin_dir_url(__FILE__) . 'assets/css/cricket.css');
    wp_enqueue_script('cricket-plugin-js', plugin_dir_url(__FILE__) . 'assets/js/cricket.js', ['jquery'], null, true);
    wp_localize_script('cricket-plugin-js', 'CricketPlugin', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('cricket_nonce')
    ]);
});

// AJAX for player search
add_action('wp_ajax_get_player', 'cricket_plugin_get_player');
add_action('wp_ajax_nopriv_get_player', 'cricket_plugin_get_player');
function cricket_plugin_get_player() {
    check_ajax_referer('cricket_nonce', 'nonce');
    $name = sanitize_text_field($_POST['name']);
    $data = Player_API::search_player($name);
    wp_send_json_success($data);
}

// Shortcode for player search
add_shortcode('cricket_player_search', function () {
    ob_start(); ?>
    <div class="cricket-search">
        <input type="text" id="player-name" placeholder="Enter player name">
        <button id="search-player">Search</button>
        <div id="player-result"></div>
    </div>
    <?php return ob_get_clean();
});

