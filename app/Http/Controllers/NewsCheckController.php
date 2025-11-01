<?php

namespace App\Http\Controllers;

use App\Models\NewsCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsCheckController extends Controller
{
    public function index()
    {
        // Show the 20 most recent checks
        $checks = NewsCheck::latest()->take(20)->get();
        return view('news.create', compact('checks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        // 📝 Save initial record first
        $news = NewsCheck::create(['content' => $request->input('content')]);

        // 🧠 AI Prompt
        $prompt = "You are an AI fact-checking assistant.
        Analyze the following statement (it can be in English, Hindi, or any language) for misinformation or fake news.
        Respond clearly in English with:
        Verdict: [Likely True / Misleading / False / Unverified]
        Explanation: [Short and factual reasoning]\n\n
        Text:\n" . $request->input('content');

        try {
            // 🔐 API Call to OpenRouter
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                'HTTP-Referer' => url('/'),
                'X-Title' => 'Fake News Detector Laravel App',
                'Content-Type' => 'application/json',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are a professional multilingual fact-checking AI.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                    ]);

            // 🧾 Handle response
            if ($response->successful()) {
                $data = $response->json();
                $aiText = trim($data['choices'][0]['message']['content'] ?? 'No response from AI.');
            } else {
                $aiText = 'API Error: ' . ($response->json()['error']['message'] ?? 'Unknown error.');
            }

        } catch (\Exception $e) {
            $aiText = 'Connection Error: ' . $e->getMessage();
        }

        // 🎯 Extract Verdict
        $verdict = $this->extractVerdict($aiText);

        // 💾 Update the database record
        $news->update([
            'verdict' => $verdict,
            'ai_response' => $aiText,
        ]);

        // 🚀 Return JSON (for AJAX)
        return response()->json([
            'success' => true,
            'id' => $news->id,
            'content' => $news->content,
            'verdict' => $news->verdict,
            'ai_response' => $news->ai_response,
        ]);
    }

    /**
     * Extract a clean verdict from AI text
     */
    private function extractVerdict($text)
    {
        $t = strtolower($text);

        if (str_contains($t, 'likely true') || str_contains($t, 'mostly true') || str_contains($t, 'true')) {
            return 'Likely True';
        } elseif (str_contains($t, 'misleading') || str_contains($t, 'partly false') || str_contains($t, 'half true')) {
            return 'Misleading';
        } elseif (str_contains($t, 'false') || str_contains($t, 'incorrect') || str_contains($t, 'fake')) {
            return 'False';
        } elseif (str_contains($t, 'unverified') || str_contains($t, 'no verification')) {
            return 'Unverified';
        }

        return 'Unverified';
    }
}
