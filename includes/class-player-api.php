<?php
class Cricket_Player_API extends Cricket_API_Handler {

    public function __construct() {
        add_shortcode('cricket_player_search', [$this, 'render_search_form']);
        add_action('wp_ajax_cps_search_players', [$this, 'search_players']);
        add_action('wp_ajax_nopriv_cps_search_players', [$this, 'search_players']);
        add_action('rest_api_init', [$this, 'register_rest_route']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('cricket-js', CRICKET_PLUGIN_URL . 'assets/js/cricket.js', ['jquery'], null, true);
        wp_enqueue_style('cricket-css', CRICKET_PLUGIN_URL . 'assets/css/cricket.css', [], null);

        wp_localize_script('cricket-js', 'cps_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cps_search_nonce')
        ]);
    }

    public function render_search_form() {
        ob_start();
        ?>
        <div class="cricket-player-search">
            <form id="cps-search-form">
                <input type="text" id="cps-player-name" placeholder="Enter player name..." required>
                <button type="submit">Search</button>
            </form>
            <div id="cps-results-container">
                <div class="cps-help-text"><p>Search results appear here.</p></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function search_players() {
        check_ajax_referer('cps_search_nonce', 'security');
        $name = sanitize_text_field($_POST['player_name'] ?? '');
        if (!$name) wp_send_json_error(['message' => 'Please enter a player name']);

        $url = "https://api.cricapi.com/v1/players?apikey={$this->api_key}&offset=0&search=" . urlencode($name);
        $data = $this->make_request($url);

        if (!$data || !isset($data['data'])) {
            wp_send_json_error(['message' => 'API error', 'response' => $data]);
        } else {
            wp_send_json_success($data['data']);
        }
    }

    public function register_rest_route() {
        register_rest_route('cricket/v1', '/player/(?P<id>\w+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_player_details'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function get_player_details($request) {
        $id = sanitize_text_field($request->get_param('id'));
        $url = "https://api.cricapi.com/v1/players_info?apikey={$this->api_key}&id={$id}";
        return $this->make_request($url);
    }
}
