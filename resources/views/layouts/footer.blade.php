<!-- 🌈 Footer -->
<div class="footer-bottom">
    <div>&copy; {{ date('Y') }} <strong>Fake News Detector</strong></div>
    <small>Developed with 💙 by <strong>Shubham Goswami</strong></small>
    <div class="footer-social">
        <a href="https://wa.me/918299722527" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        <a href="https://www.instagram.com/_i_am_shubham__/" target="_blank" title="Instagram"><i
                class="fab fa-instagram"></i></a>
        <a href="https://www.linkedin.com/in/shubham-goswami-6a0542191/" target="_blank" title="LinkedIn"><i
                class="fab fa-linkedin"></i></a>
        <a href="mailto:goswamishubham66@gmail.com" title="Email"><i class="fa-solid fa-envelope"></i></a>
    </div>
</div>

{{-- AJAX --}}
<script>
    $(function () {
        // 🌙 Dark mode toggle
        $('#darkToggle').click(() => $('body').toggleClass('dark'));

        // Tooltip activation
        new bootstrap.Tooltip(document.body, { selector: '[data-bs-toggle="tooltip"]' });

        // 🔍 Analyze
        $('#analyzeForm').submit(function (e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Analyzing...');
            $('#loading').show();
            $('#result').addClass('d-none');

            $.ajax({
                url: "{{ route('news.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    $('#loading').hide();
                    $('#result').removeClass('d-none');
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-brain me-1"></i> Analyze with AI');

                    // Verdict with emoji
                    $('#verdictText').html(getVerdictEmoji(res.verdict) + ' ' + res.verdict)
                        .attr('class', 'fw-bold text-capitalize text-' + getVerdictColor(res.verdict));

                    $('#aiResponse').text(res.ai_response);

                    // Confidence bar update
                    const confidence = res.confidence || 0;
                    const $bar = $('#confidenceBar');
                    const $label = $('#confidenceLabel');
                    let color = 'bg-secondary';
                    let level = 'Low Confidence';

                    if (confidence >= 80) { color = 'bg-success'; level = 'High Confidence'; }
                    else if (confidence >= 60) { color = 'bg-warning'; level = 'Medium Confidence'; }
                    else { color = 'bg-danger'; level = 'Low Confidence'; }

                    $bar.removeClass().addClass('progress-bar ' + color)
                        .css('width', confidence + '%').text(confidence + '%');
                    $label.text(level);

                    // Add to Recent
                    const verdictClass = res.verdict ? res.verdict.replace(/\s/g, '') : 'Unverified';
                    const newItem = `
              <div class="recent-item" onclick="this.classList.toggle('expanded')">
                <span class="text-content">${res.content}</span>
                <span class="verdict-badge verdict-${verdictClass}">${res.verdict}</span>
              </div>`;
                    $('.recent-list').prepend(newItem);
                },
                error: function (err) {
                    $('#loading').hide();
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-brain me-1"></i> Analyze with AI');
                    alert('Error: ' + err.responseText);
                }
            });
        });

        function getVerdictColor(verdict) {
            switch (verdict) {
                case 'False': return 'danger';
                case 'Misleading': return 'warning';
                case 'Likely True': return 'success';
                default: return 'secondary';
            }
        }

        function getVerdictEmoji(verdict) {
            switch (verdict) {
                case 'False': return '❌';
                case 'Misleading': return '⚠️';
                case 'Likely True': return '✅';
                default: return '❓';
            }
        }

        // 📋 Share result
        $('#shareBtn').click(() => {
            const summary = `Verdict: ${$('#verdictText').text()} | Confidence: ${$('#confidenceBar').text()} | ${$('#aiResponse').text()}`;
            navigator.clipboard.writeText(summary);
            alert('AI analysis copied to clipboard!');
        });
    });
</script>
</body>

</html>