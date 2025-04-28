<?php
header('Content-Type: application/json');

$prompts = [
    'cringe' => "You’re a Gen Z meme lord turned life coach. Using viral slang (yeet, no cap, glow-up), craft a brief tongue-in-cheek inspirational quote that pokes fun at everyday cringe but ends on a motivational note.",
    'stoic' => "As a modern Stoic philosopher, write a short inspirational quote about accepting what you cannot change, mastering your emotions, and focusing on virtue. Keep it timeless and grounded.",
    'nihil' => "From a nihilist’s viewpoint, compose a pithy inspirational quote that acknowledges life’s inherent meaninglessness yet finds freedom or purpose in creating one’s own values.",
    'pokemon' => "Channel the voice of a Pokémon Trainer offering pep talk advice. Include a motivational line about teamwork, resilience, and evolving—just like a Pikachu powering up.",
    'gwash' => "In the formal 18th-century style of George Washington, write a dignified inspirational quote on leadership, duty, and perseverance. Use “shall”, “therefore”, etc., to evoke his voice."
];

// Get the category from the POST request
$category = isset($_POST['category']) ? $_POST['category'] : '';

if (!array_key_exists($category, $prompts)) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'description' => 'Invalid category', 'text' => '', 'usage' => []]);
    exit;
}

$prompt = $prompts[$category];
$cacheDir = "cache/$category";
$tokenFile = 'token_usage.json';

// Ensure cache directory exists
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Initialize response variables
$responseData = [
    'status' => 503,
    'description' => 'Ollama failed, using cached response',
    'text' => 'No response',
    'usage' => ['prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0]
];

// Try to get response from Ollama
$payload = [
    'model' => 'gemma:2b',
    'prompt' => $prompt,
    'max_tokens' => 80,
    'temperature' => 0.8
];

$ch = curl_init('http://192.168.1.110:11434/v1/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5-second timeout

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle Ollama success
if ($response !== false && $httpCode === 200 && empty($curlError)) {
    $json = json_decode($response, true);
    if (isset($json['choices'][0]['text'])) {
        $responseData = [
            'status' => 200,
            'description' => 'Ollama success',
            'text' => trim($json['choices'][0]['text']),
            'usage' => $json['usage'] ?? ['prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0]
        ];

        // Save response to cache
        $timestamp = date('YmdHis');
        $cacheFile = "$cacheDir/$timestamp.json";
        file_put_contents($cacheFile, json_encode([
            'text' => $responseData['text'],
            'usage' => $responseData['usage']
        ]));
    }
}

// Fallback to cached response if Ollama fails
if ($responseData['status'] !== 200) {
    $cachedFiles = glob("$cacheDir/*.json");
    if (!empty($cachedFiles)) {
        $randomFile = $cachedFiles[array_rand($cachedFiles)];
        $cachedResponse = json_decode(file_get_contents($randomFile), true);
        if ($cachedResponse) {
            $responseData = [
                'status' => 503,
                'description' => 'Ollama failed, using cached response',
                'text' => $cachedResponse['text'],
                'usage' => $cachedResponse['usage']
            ];
        }
    }
}

// Update total token usage
$totalTokens = 0;
if (file_exists($tokenFile)) {
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    $totalTokens = $tokenData['total_tokens'] ?? 0;
}
$totalTokens += $responseData['usage']['total_tokens'];
file_put_contents($tokenFile, json_encode(['total_tokens' => $totalTokens]));

// Return the response
echo json_encode($responseData);
?>