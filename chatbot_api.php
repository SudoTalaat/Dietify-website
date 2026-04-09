<?php
require_once __DIR__ . '/config.php';

// ── Only logged-in users ─────────────────────────────────────────────────────
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to use the chat assistant.']);
    exit;
}

// ── Read JSON body ───────────────────────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'message';

// ── Handle actions ───────────────────────────────────────────────────────────


// ── Send message ─────────────────────────────────────────────────────────────
$userMessage = trim($input['message'] ?? '');
if ($userMessage === '' && $action === 'message') {
    http_response_code(400);
    echo json_encode(['error' => 'Message cannot be empty.']);
    exit;
}

// ── Database Table and Session Initialization ───────────────────────────────
$userId = $_SESSION['user_id'] ?? 'anonymous';
if (!isset($_SESSION['chat_goal'])) {
    $_SESSION['chat_goal'] = 'general';
}
$goal = $_SESSION['chat_goal'];

// ── Handle Clear Action ──────────────────────────────────────────────────────
if ($action === 'clear') {
    $stmt = $conn->prepare("DELETE FROM messages WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    echo json_encode(['status' => 'cleared']);
    exit;
}

// ── Handle Set Goal Action ───────────────────────────────────────────────────
if ($action === 'set_goal') {
    $allowed = ['weight_loss', 'muscle_gain', 'general'];
    $goal = in_array($input['goal'] ?? '', $allowed) ? $input['goal'] : 'general';
    $_SESSION['chat_goal'] = $goal;
    echo json_encode(['status' => 'goal_set', 'goal' => $goal]);
    exit;
}

// ── Handle Get History Action ────────────────────────────────────────────────
if ($action === 'get_history') {
    $stmt = $conn->prepare("SELECT role, message as content FROM messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 50");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    echo json_encode([
        'history' => $history,
        'goal' => $goal,
        'service_online' => (!empty($_ENV['GROQ_API_KEY']))
    ]);
    exit;
}

// ── API key from .env ────────────────────────────────────────────────────────
$apiKey = $_ENV['GROQ_API_KEY'] ?? '';
if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Chat service is not configured.']);
    exit;
}

// ── Save User Message to Database ───────────────────────────────────────────
if ($action === 'message') {
    $stmt = $conn->prepare("INSERT INTO messages (user_id, role, message) VALUES (?, 'user', ?)");
    $stmt->bind_param("ss", $userId, $userMessage);
    $stmt->execute();
}

// ── Build Context from Database (Last 10 messages) ──────────────────────────
$stmt = $conn->prepare("SELECT role, message as content FROM messages WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
$history = array_reverse($history); // Re-order back to chronological for API

// ── System prompt ────────────────────────────────────────────────────────────
$goalDescriptions = [
    'weight_loss' => 'The user wants to lose weight. Suggest low-calorie, high-fiber, protein-rich meals. Avoid suggesting fried or high-sugar foods.',
    'muscle_gain' => 'The user wants to build muscle. Suggest high-protein meals with adequate carbs for energy. Include chicken, eggs, beans, rice, and dairy.',
    'general' => 'The user wants to eat healthier in general. Suggest balanced, nutritious meals with variety.',
];
$goalContext = $goalDescriptions[$goal] ?? $goalDescriptions['general'];

$systemPrompt = <<<PROMPT
You are a friendly Healthy Food Assistant. Your job is to help users choose healthy, affordable, and practical meals.

RULES:
1. Focus on common, affordable foods: rice, eggs, chicken, vegetables, beans, lentils, oats, fruits, bread, dairy.
2. Keep answers SHORT — 2 to 4 lines maximum. Be concise and practical.
3. Give simple meal suggestions with brief preparation tips when asked.
4. You may use emojis sparingly to be friendly (🥗🍳🥚🍗🥦).
5. NEVER give medical advice or diagnose conditions.
6. If the user asks about diseases, medications, or medical conditions, respond ONLY with: "Please consult a doctor for medical advice. I can only help with general food suggestions! 🩺"
7. When giving dietary advice, include this disclaimer if relevant: "This is general advice, not medical guidance."

USER GOAL:
{$goalContext}

Be helpful, warm, and encouraging. Remember previous messages in the conversation.
PROMPT;

// ── Build messages array for API ─────────────────────────────────────────────
$messages = [['role' => 'system', 'content' => $systemPrompt]];
foreach ($history as $msg) {
    if ($msg['content'] !== $userMessage || $msg['role'] !== 'user') { // Avoid duplicating current message if already in history
        // Actually, since I just saved the user message, it might be in the history results already.
        // But typically it's safer to just build history first then add current.
        // In this logic, I saved then fetched. So it IS in history.
    }
    $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
}

// ── Call Groq API via cURL ───────────────────────────────────────────────────
$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'llama-3.1-8b-instant',
        'messages' => $messages,
        'max_tokens' => 256,
        'temperature' => 0.7,
    ]),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'AI service returned an error.']);
    exit;
}

$data = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? '';

// ── Save Assistant Response to Database ─────────────────────────────────────
if ($reply !== '') {
    $stmt = $conn->prepare("INSERT INTO messages (user_id, role, message) VALUES (?, 'assistant', ?)");
    $stmt->bind_param("ss", $userId, $reply);
    $stmt->execute();
}

// ── Return reply ─────────────────────────────────────────────────────────────
echo json_encode(['reply' => $reply]);
