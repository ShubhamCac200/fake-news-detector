<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Fake News Detector</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Bootstrap + FontAwesome + jQuery --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <style>
    body {
      background-color: #f4f7fb;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
    }

    /* 🌈 Gradient Navbar */
    .navbar {
      background: linear-gradient(90deg, #0d6efd, #6610f2);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    }

    .navbar-brand {
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    /* 🧠 Cards */
    .card {
      border: none;
      border-radius: 18px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      background: #fff;
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: translateY(-3px);
    }

    .result-card {
      background: #e8f7ff;
      border-left: 5px solid #0d6efd;
    }

    /* 🏷️ Verdict badges */
    .verdict-badge {
      font-size: 0.85rem;
      padding: 0.25rem 0.6rem;
      border-radius: 6px;
      color: #fff;
      white-space: nowrap;
      text-transform: capitalize;
    }

    .verdict-False {
      background: #dc3545;
    }

    .verdict-Misleading {
      background: #ffc107;
      color: #000;
    }

    .verdict-LikelyTrue {
      background: #198754;
    }

    .verdict-Unverified {
      background: #6c757d;
    }

    /* 🕓 Recent Checks */
    .recent-list {
      max-height: 400px;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #0d6efd #f1f1f1;
    }

    .recent-item {
      cursor: pointer;
      border-bottom: 1px solid #eee;
      padding: 8px 0;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      transition: all 0.3s ease;
    }

    .recent-item:hover {
      background: #f8f9fa;
      border-radius: 8px;
      padding-left: 6px;
    }

    .recent-item span.text-content {
      flex: 1;
      min-width: 200px;
      margin-right: 10px;
      display: block;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .recent-item.expanded span.text-content {
      white-space: normal;
      overflow: visible;
    }

    /* 🌈 Gradient Footer */
    .footer-bottom {
      background: linear-gradient(90deg, #6610f2, #0d6efd);
      color: #fff;
      text-align: center;
      padding: 14px 8px;
      font-size: 0.85rem;
      margin-top: auto;
      position: relative;
      overflow: hidden;
    }

    .footer-bottom small {
      display: block;
      margin-top: 4px;
      font-size: 0.78rem;
      color: rgba(255, 255, 255, 0.85);
    }

    .footer-bottom a {
      color: #fff;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease, transform 0.2s ease;
    }

    .footer-bottom a:hover {
      color: #ffd700;
      transform: scale(1.1);
    }

    .footer-social {
      margin-top: 6px;
    }

    .footer-social a {
      display: inline-block;
      margin: 0 6px;
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.9);
      transition: transform 0.2s ease, color 0.2s ease;
    }

    .footer-social a:hover {
      color: #ffd700;
      transform: scale(1.1);
    }

    /* Decorative line on top of footer */
    .footer-bottom::before {
      content: "";
      position: absolute;
      top: 0;
      left: 50%;
      width: 80%;
      height: 1px;
      background: rgba(255, 255, 255, 0.4);
      transform: translateX(-50%);
      border-radius: 50%;
    }

    /* ✅ Responsive */
    @media (max-width: 768px) {

      h4,
      h5 {
        font-size: 1.1rem;
      }

      .navbar-brand {
        font-size: 1rem;
      }

      .card {
        padding: 1rem;
      }

      textarea {
        font-size: 0.9rem;
      }

      .recent-item span.text-content {
        font-size: 0.85rem;
      }
    }

    @media (max-width: 576px) {
      .recent-list {
        max-height: 300px;
      }

      .btn {
        width: 100%;
      }

      .footer-bottom {
        font-size: 0.8rem;
        padding: 12px 6px;
      }

      .footer-social a {
        font-size: 1rem;
        margin: 0 4px;
      }
    }
  </style>

</head>

<body>

  {{-- 🌈 NAVBAR --}}
  <nav class="navbar navbar-dark mb-4">
    <div class="container-fluid">
      <a class="navbar-brand text-white" href="#">
        <i class="fa-solid fa-newspaper me-2"></i> Fake News Detector
      </a>
    </div>
  </nav>

  <div class="container flex-grow-1">
    <div class="row g-4">

      {{-- LEFT: Analyzer --}}
      <div class="col-lg-8 col-md-12">
        <div class="card p-4">
          <h4 class="fw-bold text-primary mb-3">
            <i class="fa-solid fa-magnifying-glass text-primary"></i> Analyze News / Tweets / Posts
          </h4>

          <form id="analyzeForm">
            @csrf
            <textarea name="content" id="content" rows="6" class="form-control mb-3"
              placeholder="Paste your content here..."></textarea>
            <button type="submit" class="btn btn-success">
              <i class="fa-solid fa-brain me-1"></i> Analyze with AI
            </button>
          </form>

          <div id="loading" class="text-center mt-4" style="display:none;">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Analyzing... please wait</p>
          </div>

          <div id="result" class="result-card p-4 mt-4 d-none">
            <h5><i class="fa-regular fa-lightbulb me-2"></i><strong>Verdict:</strong>
              <span id="verdictText" class="fw-bold"></span>
            </h5>
            <hr>
            <h6><i class="fa-solid fa-comments me-2"></i>AI Explanation:</h6>
            <p id="aiResponse" class="mt-2"></p>
          </div>
        </div>
      </div>

      {{-- RIGHT: Recent Checks --}}
      <div class="col-lg-4 col-md-12">
        <div class="card p-4">
          <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-rotate me-2"></i>Recent Checks</h5>
          <div class="recent-list">
            @forelse($checks as $item)
              <div class="recent-item" onclick="this.classList.toggle('expanded')">
                <span class="text-content">{{ $item->content }}</span>
                <span class="verdict-badge verdict-{{ str_replace(' ', '', $item->verdict ?? 'Unverified') }}">
                  {{ $item->verdict ?? 'Unverified' }}
                </span>
              </div>
            @empty
              <p class="text-muted">No checks yet.</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>

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

            $('#verdictText').text(res.verdict)
              .attr('class', 'fw-bold text-capitalize text-' + getVerdictColor(res.verdict));
            $('#aiResponse').text(res.ai_response);

            const verdictClass = res.verdict ? res.verdict.replace(/\s/g, '') : 'Unverified';
            const newItem = `
            <div class="recent-item" onclick="this.classList.toggle('expanded')">
              <span class="text-content">${res.content}</span>
              <span class="verdict-badge verdict-${verdictClass}">
                ${res.verdict}
              </span>
            </div>
          `;
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
    });
  </script>

</body>

</html>