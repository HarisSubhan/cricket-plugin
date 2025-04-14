jQuery(document).ready(function ($) {
    $('#cps-search-form').on('submit', function (e) {
        e.preventDefault();
        const playerName = $('#cps-player-name').val().trim();
        const resultsContainer = $('#cps-results-container');

        if (!playerName) return;

        resultsContainer.html('<p class="cps-loading">Loading...</p>');

        $.ajax({
            url: cps_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'cps_search_players',
                security: cps_ajax.nonce,
                player_name: playerName,
            },
            success: function (response) {
                if (response.success) {
                    const players = response.data;
                    if (players.length === 0) {
                        resultsContainer.html('<p>No players found.</p>');
                        return;
                    }

                    let html = '<ul class="cps-player-list">';
                    players.forEach(player => {
                        html += `<li>
                            <a href="#" class="cps-player-link" data-id="${player.id}">
                                ${player.name} (${player.country})
                            </a>
                        </li>`;
                    });
                    html += '</ul>';
                    resultsContainer.html(html);
                } else {
                    resultsContainer.html('<p>Error fetching players.</p>');
                }
            },
            error: function () {
                resultsContainer.html('<p>Request failed.</p>');
            }
        });
    });

    $(document).on('click', '.cps-player-link', function (e) {
        e.preventDefault();
        const playerId = $(this).data('id');
        const resultsContainer = $('#cps-results-container');
        resultsContainer.html('<p class="cps-loading">Fetching player details...</p>');

        fetch(`/wp-json/cricket/v1/player/${playerId}`)
            .then(res => res.json())
            .then(player => {
                let html = `<div class="cps-player-card">
                    <h3>${player.name}</h3>
                    <p><strong>Country:</strong> ${player.country}</p>
                    <p><strong>Role:</strong> ${player.role}</p>
                    <p><strong>Batting Style:</strong> ${player.battingStyle}</p>
                    <p><strong>Bowling Style:</strong> ${player.bowlingStyle}</p>
                    <p><strong>Born:</strong> ${player.dateOfBirth}</p>
                </div>`;
                resultsContainer.html(html);
            })
            .catch(() => {
                resultsContainer.html('<p>Could not load player details.</p>');
            });
    });
});
