<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

// Sanitise
$entry = [
    'name'      => htmlspecialchars(trim($data['name']     ?? 'Unknown'),    ENT_QUOTES, 'UTF-8'),
    'course'    => htmlspecialchars(trim($data['course']   ?? '—'),          ENT_QUOTES, 'UTF-8'),
    'subject'   => htmlspecialchars(trim($data['subject']  ?? '—'),          ENT_QUOTES, 'UTF-8'),
    'instructor'=> htmlspecialchars(trim($data['instructor']?? '—'),         ENT_QUOTES, 'UTF-8'),
    'score'     => (int)($data['score']  ?? 0),
    'total'     => (int)($data['total']  ?? 60),
    'submitted_at' => date('Y-m-d H:i:s'),
];

$file = __DIR__ . '/results.json';

// Load existing results
$results = [];
if (file_exists($file)) {
    $contents = file_get_contents($file);
    $results  = json_decode($contents, true) ?? [];
}

// Prepend newest first
array_unshift($results, $entry);

// Save back
file_put_contents($file, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'score' => $entry['score'], 'total' => $entry['total']]);
