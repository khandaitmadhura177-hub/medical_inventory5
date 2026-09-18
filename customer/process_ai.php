<?php
header('Content-Type: application/json');

// Security: Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['response' => 'Direct access restricted.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';

$apiKey = "YOUR_GEMINI_API_KEY_HERE"; 

if(empty($query)) { 
    echo json_encode(['response' => 'The neural link is empty. Please provide input.']); 
    exit; 
}

// System Instruction: Tell the AI how to behave (e.g., as a medical assistant)
$structuredQuery = "You are a helpful assistant for MIMS (Medical Information Management System). " . $query;

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
$payload = json_encode([
    "contents" => [["parts" => [["text" => $structuredQuery]]]]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);

if(curl_errno($ch)) {
    echo json_encode(['response' => 'Neural Link Timeout. Please check connection.']);
    curl_close($ch);
    exit;
}
curl_close($ch);

$result = json_decode($response, true);
$reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? "I'm having trouble connecting to the medical database right now.";

echo json_encode(['response' => $reply]);
?>