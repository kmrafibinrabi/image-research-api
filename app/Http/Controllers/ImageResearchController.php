<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageResearchController extends Controller
{
    public function analyze(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|max:2048',
                'question' => 'required|string|max:200',
            ]);

            if (!$request->hasFile('image')) {
                return response()->json(['error' => 'No image uploaded'], 422);
            }

            $image = $request->file('image');
            $question = $request->input('question');
            $imageBase64 = base64_encode(file_get_contents($image->getRealPath()));
            $mimeType = $image->getMimeType();

            \Log::info("🧠 Making OpenAI Request...", [
                'mime' => $mimeType,
                'question' => $question
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json'
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $question],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$imageBase64}"
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 300
            ]);

            if ($response->failed()) {
                \Log::error('❌ OpenAI API failed: ' . $response->body());
                return response()->json(['error' => 'AI processing failed'], 500);
            }

            return response()->json([
                'answer' => $response['choices'][0]['message']['content'] ?? 'No answer returned.'
            ]);
        } catch (\Throwable $e) {
            \Log::error('🔥 Laravel Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}