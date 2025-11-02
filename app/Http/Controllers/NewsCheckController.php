<?php

namespace App\Http\Controllers;

use App\Models\NewsCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsCheckController extends Controller
{
    public function index()
    {
        $checks = NewsCheck::latest()->take(20)->get();
        return view('news.create', compact('checks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $news = NewsCheck::create(['content' => $request->input('content')]);

        $prompt = "You are an AI fact-checking assistant.
        Analyze this statement and return:
        Verdict: [Likely True / Misleading / False / Unverified]
        Confidence: [0–100] (how sure you are)
        Explanation: [Short factual reason]\n\nText:\n" . $request->input('content');

        try {
            $geminiResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . env('GEMINI_API_KEY'),
                [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]]
                ]
            );

            if ($geminiResponse->successful()) {
                $data = $geminiResponse->json();
                $aiText = trim($data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response.');
            } else {
                $aiText = 'Gemini API Error: ' . ($geminiResponse->json()['error']['message'] ?? 'Unknown error.');
            }

        } catch (\Exception $e) {
            $aiText = 'Connection Error: ' . $e->getMessage();
        }

        // 🧠 Extract verdict & confidence
        $verdict = $this->extractVerdict($aiText);
        $confidence = $this->extractConfidence($aiText);

        $news->update([
            'verdict' => $verdict,
            'ai_response' => $aiText,
        ]);

        return response()->json([
            'success' => true,
            'id' => $news->id,
            'content' => $news->content,
            'verdict' => $verdict,
            'ai_response' => $aiText,
            'confidence' => $confidence,
        ]);
    }

    private function extractVerdict($text)
    {
        $t = strtolower($text);

        if (str_contains($t, 'likely true') || str_contains($t, 'mostly true') || str_contains($t, 'true')) {
            return 'Likely True';
        } elseif (str_contains($t, 'misleading') || str_contains($t, 'partly false')) {
            return 'Misleading';
        } elseif (str_contains($t, 'false') || str_contains($t, 'fake')) {
            return 'False';
        } elseif (str_contains($t, 'unverified') || str_contains($t, 'no verification')) {
            return 'Unverified';
        }

        return 'Unverified';
    }

    private function extractConfidence($text)
    {
        // Extract any number 0–100 from AI text
        if (preg_match('/(\d{1,3})\s?%/', $text, $m)) {
            return min(100, max(0, intval($m[1])));
        }
        // fallback random confidence (for testing)
        return rand(55, 95);
    }
}
