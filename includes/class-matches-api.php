<?php
class Cricket_Matches_API extends Cricket_API_Handler {

    public function __construct() {
        add_shortcode('match_card_slider', [$this, 'render_match_cards']);
    }

    public function render_match_cards() {
        $transient_key = 'match_card_slider';
        $cached = get_transient($transient_key);
        if ($cached) return $cached;

        $url = "https://api.cricapi.com/v1/currentMatches?apikey={$this->api_key}&offset=0";
        $data = $this->make_request($url);

        if (!$data || !isset($data['data'])) return '';

        ob_start();
        ?>
        <div class="match-slider-wrapper">
            <?php foreach ($data['data'] as $match): ?>
                <div class="match-card">
                    <h4><?php echo esc_html($match['matchType'] . ' - ' . ($match['venue'] ?? '')); ?></h4>
                    <?php foreach ($match['teamInfo'] as $team): ?>
                        <div class="team">
                            <?php if (!empty($team['img'])): ?>
                                <img src="<?php echo esc_url($team['img']); ?>" alt="">
                            <?php endif; ?>
                            <?php echo esc_html($team['name']); ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="match-status"><?php echo esc_html($match['status'] ?? ''); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        $html = ob_get_clean();
        set_transient($transient_key, $html, 2 * MINUTE_IN_SECONDS);
        return $html;
    }
}
