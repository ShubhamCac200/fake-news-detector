<!DOCTYPE html>
<html>
<head>
    <title>Fake News Detector</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-center">📰 Fake News & Misinformation Detector</h1>

        <a href="{{ route('news.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ New Check</a>

        <div class="mt-6 bg-white p-4 shadow rounded">
            <h2 class="font-semibold mb-3">Recent Checks</h2>
            <ul>
                @forelse($checks as $item)
                    <li class="border-b py-2">
                        <a href="{{ route('news.show', $item->id) }}">
                            {{ Str::limit($item->content, 80) }} — 
                            <span class="text-sm text-gray-600">{{ $item->verdict }}</span>
                        </a>
                    </li>
                @empty
                    <p>No records yet.</p>
                @endforelse
            </ul>
        </div>
    </div>
</body>
</html>
