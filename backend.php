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
    echo json_encode(['error' => 'Invalid category']);
    exit;
}

$prompt = $prompts[$category];

// Prepare the payload for Ollama
$payload = [
    'model' => 'gemma:2b',
    'prompt' => $prompt,
    'max_tokens' => 80,
    'temperature' => 0.8
];

// Initialize cURL
$ch = curl_init('http://192.168.1.110:11434/v1/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors or bad response
if ($response === false || $httpCode !== 200) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to communicate with Ollama server']);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Parse the response and extract the text and usage data
$responseData = json_decode($response, true);
$text = $responseData['choices'][0]['text'] ?? 'No response';
$usage = $responseData['usage'] ?? ['prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0];

// Return the response with text and usage data
echo json_encode([
    'text' => trim($text),
    'usage' => $usage
]);
?>  