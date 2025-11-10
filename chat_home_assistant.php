<?php
// chat_home_assistant.php
header('Content-Type: text/plain; charset=utf-8');

// Load env
$cfgFile = __DIR__ . '/env.php';
if (!file_exists($cfgFile)) {
	http_response_code(500);
	echo "Server not configured. Add your OpenAI key in env.php.";
	exit;
}
$config = require $cfgFile;
$apiKey = trim($config['OPENAI_API_KEY'] ?? '');
$model = $config['OPENAI_MODEL'] ?? 'gpt-4o-mini';
if ($apiKey === '') {
	http_response_code(500);
	echo "Missing OpenAI API key. Update env.php.";
	exit;
}

// Ensure uploads dir
$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0775, true); }
$imageUrl = null;
if (!empty($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
	$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: 'jpg';
	$basename = 'home_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . preg_replace('/[^a-zA-Z0-9]/','', $ext);
	$dest = $uploadsDir . '/' . $basename;
	if (@move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
		$imageUrl = 'uploads/' . $basename;
	}
}

// Read input: prefer JSON { messages: [...] }
$raw = file_get_contents('php://input');
$asJson = null;
if ($raw && (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false)) {
	try { $asJson = json_decode($raw, true); } catch (Throwable $e) { $asJson = null; }
}

$messages = null;
if (is_array($asJson) && isset($asJson['messages']) && is_array($asJson['messages'])) {
	$messages = $asJson['messages'];
}

$systemPrompt = "You are FoodieAds' helpful homepage assistant. Help visitors navigate the site (registration, pricing, delivery options, dashboard, contact), answer food-related questions, and provide concise, friendly guidance. Avoid admin-only actions. Keep replies under 120 words unless asked to elaborate.";

if (!$messages) {
	$message = trim($_POST['command'] ?? $_POST['message'] ?? '');
	if ($message === '' && !$imageUrl) {
		echo "Please type a question.";
		exit;
	}
	$userContent = $message;
	if ($imageUrl) { $userContent .= "\n[Image uploaded: $imageUrl]"; }
	$messages = [
		["role" => "system", "content" => $systemPrompt],
		["role" => "user", "content" => $userContent]
	];
} else {
	// Ensure a system prompt exists at the top
	$hasSystem = false;
	foreach ($messages as $m) { if (($m['role'] ?? '') === 'system') { $hasSystem = true; break; } }
	if (!$hasSystem) { array_unshift($messages, ["role" => "system", "content" => $systemPrompt]); }
	if ($imageUrl) {
		$messages[] = ["role" => "user", "content" => "[Image uploaded: $imageUrl]"];
	}
}

$payload = [
	"model" => $model,
	"messages" => $messages,
	"temperature" => 0.6,
];

try {
	$ch = curl_init('https://api.openai.com/v1/chat/completions');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Authorization: Bearer ' . $apiKey,
	]);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	$response = curl_exec($ch);
	if ($response === false) {
		throw new Exception('Request failed: ' . curl_error($ch));
	}
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($code < 200 || $code >= 300) {
		throw new Exception('OpenAI error: HTTP ' . $code . ' ' . $response);
	}
	$data = json_decode($response, true);
	$text = $data['choices'][0]['message']['content'] ?? '';
	if (!$text) {
		throw new Exception('Empty response from model');
	}
	if ($imageUrl) {
		$text .= "\n\n(Your image was uploaded: $imageUrl)";
	}
	echo $text;
} catch (Throwable $e) {
	http_response_code(500);
	echo 'Error: ' . $e->getMessage();
}
