<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageResearchController extends Controller
{
public function analyze(Request $request)
{
$request->validate([
'image' => 'required|image|max:2048',
'question' => 'required|string|max:200',
]);

$image = $request->file('image');
$question = $request->input('question');
$imageBase64 = base64_encode(file_get_contents($image->getRealPath()));

$response = Http::withHeaders([
'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
'Content-Type' => 'application/json'
])->post('https://api.openai.com/v1/chat/completions', [
'model' => 'gpt-4-vision-preview',
'messages' => [
[
'role' => 'user',
'content' => [
['type' => 'text', 'text' => $question],
[
'type' => 'image_url',
'image_url' => [
'url' => "data:{$image->getMimeType()};base64,{$imageBase64}"
]
]
]
]
],
'max_tokens' => 300
]);

if ($response->failed()) {
return response()->json(['error' => 'AI processing failed'], 500);
}

return response()->json([
'answer' => $response['choices'][0]['message']['content']
]);
}
}
