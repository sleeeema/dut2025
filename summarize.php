<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['input_text'])) {
    $input_text = $_POST['input_text'];

    $url = "https://api.openai.com/v1/chat/completions";
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "Tu es un assistant qui résume les textes."],
            ["role" => "user", "content" => "Résumé moi ce texte : " . $input_text]
        ],
        "temperature" => 0.5,
        "max_tokens" => 300
    ];

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . OPENAI_API_KEY
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    $summary = $result['choices'][0]['message']['content'] ?? "Erreur lors du résumé.";

    echo json_encode(['summary' => $summary]);
} else {
    echo json_encode(['summary' => 'Aucun texte fourni.']);
}
?>
