<!DOCTYPE html>
<html>
<head>
    <title>AI Result</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">🧠 AI Fact Check Result</h2>

        <p class="mb-2"><strong>Content:</strong></p>
        <p class="p-3 bg-gray-50 border rounded mb-4">{{ $news->content }}</p>

        <p class="mb-2"><strong>Verdict:</strong> 
            <span class="font-bold {{ $news->verdict === 'Likely True' ? 'text-green-600' : ($news->verdict === 'Misleading' ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $news->verdict }}
            </span>
        </p>

        <p class="mt-4"><strong>AI Explanation:</strong></p>
        <p class="p-3 bg-gray-50 border rounded mb-4 whitespace-pre-line">{{ $news->ai_response }}</p>

        <a href="{{ route('news.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded">← Back</a>
    </div>
</body>
</html>
