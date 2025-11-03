<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fake News Detector</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap + FontAwesome + jQuery --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
<path fill='%2300c4ff' d='M208 0a56 56 0 0 0-56 56v24H120a72 72 0 0 0 0 144v24a56 56 0 0 0 56 56v24a56 56 0 0 0 112 0v-24h32a56 56 0 0 0 56-56v-24a72 72 0 0 0 0-144h-32V56a56 56 0 0 0-56-56z'/>
</svg>">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* ====== Base Styles ====== */
        body {
            background-color: #f4f7fb;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .navbar {
            background: linear-gradient(90deg, #0d6efd, #6610f2);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }

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
            border-radius: 12px;
        }

        .verdict-badge {
            font-size: 0.85rem;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            color: #fff;
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .recent-item.expanded span.text-content {
            white-space: normal;
            overflow: visible;
        }

        .progress {
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        .ai-explanation {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 10px 12px;
            border-radius: 8px;
            color: #333;
            white-space: pre-wrap;
        }

        .footer-bottom {
            background: linear-gradient(90deg, #6610f2, #0d6efd);
            color: #fff;
            text-align: center;
            padding: 14px 8px;
            font-size: 0.85rem;
            margin-top: auto;
            position: relative;
        }

        .footer-bottom small {
            display: block;
            margin-top: 4px;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .footer-social a {
            margin: 0 6px;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .footer-social a:hover {
            color: #ffd700;
            transform: scale(1.1);
        }

        /* ====== 🌙 Dark Mode Fix ====== */
        body.dark {
            background-color: #121212;
            color: #e6e6e6;
        }

        body.dark .card {
            background: #1e1e1e;
            color: #fff;
        }

        body.dark .result-card {
            background: #2b2b2b;
            border-left-color: #0dcaf0;
            color: #f1f1f1;
        }

        body.dark .navbar {
            background: linear-gradient(90deg, #0dcaf0, #6610f2);
        }

        body.dark textarea,
        body.dark input,
        body.dark .form-control {
            background-color: #2c2c2c;
            color: #f1f1f1;
            border: 1px solid #444;
        }

        body.dark textarea::placeholder {
            color: #aaa;
        }

        body.dark .ai-explanation {
            background: #2b2b2b;
            color: #e6e6e6;
            border-left-color: #0dcaf0;
        }

        body.dark .recent-item {
            border-bottom: 1px solid #333;
        }

        body.dark .recent-item:hover {
            background-color: #2c2c2c;
        }

        .progress {
            background-color: #2b2b2b;
        }
    </style>
</head>

<body>

    {{-- 🌈 NAVBAR --}}
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-white" href="#">
                <i class="fa-solid fa-newspaper me-2"></i> Fake News Detector
            </a>
            <button class="btn btn-sm btn-light" id="darkToggle" title="Toggle dark mode">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>
    </nav>