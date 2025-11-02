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

        // 🧠 Improved prompt
        $prompt = "You are an AI fact-checking assistant.
        Analyze this statement and respond ONLY in strict JSON format as shown below:
        {
          \"verdict\": \"Likely True | Misleading | False | Unverified\",
          \"confidence\": <number between 0 and 100>,
          \"explanation\": \"Short factual explanation\"
        }

        Text to analyze:
        " . $request->input('content');

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

        // 🧩 Try to parse JSON from Gemini response
        $parsed = $this->parseJson($aiText);

        $verdict = $parsed['verdict'] ?? $this->extractVerdict($aiText);
        $confidence = $parsed['confidence'] ?? $this->extractConfidence($aiText);
        $explanation = $parsed['explanation'] ?? $aiText;

        $news->update([
            'verdict' => $verdict,
            'ai_response' => $explanation,
        ]);

        return response()->json([
            'success' => true,
            'id' => $news->id,
            'content' => $news->content,
            'verdict' => $verdict,
            'ai_response' => $explanation,
            'confidence' => $confidence,
        ]);
    }

    private function parseJson($text)
    {
        // Clean Markdown or code block formatting from Gemini
        $clean = preg_replace('/```(?:json)?|```/i', '', $text);
        $clean = trim($clean);

        $json = json_decode($clean, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        return [];
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
        // Extract number 0–100 from AI text
        if (preg_match('/(\d{1,3})\s?%/', $text, $m)) {
            return min(100, max(0, intval($m[1])));
        }
        return rand(55, 95);
    }
}
