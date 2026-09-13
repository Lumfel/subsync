<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\Conversation;
use App\Models\Message;

// Step 1: Get the login page to extract the CSRF token
$loginPage = Http::get('http://127.0.0.1:8000/login');
if (!$loginPage->successful()) {
    echo "Failed to get login page: " . $loginPage->body() . PHP_EOL;
    exit(1);
}

$csrfToken = null;
if (preg_match('/name="csrf-token" content="([^"]+)"/', $loginPage->body(), $matches)) {
    $csrfToken = $matches[1];
} else {
    die('CSRF token not found in login page');
}

echo "CSRF Token: " . $csrfToken . PHP_EOL;

// Step 2: Login with the CSRF token
$loginResponse = Http::asForm()->post('http://127.0.0.1:8000/login', [
    '_token' => $csrfToken,
    'email' => 'resident1@subsync.com',
    'password' => 'Resident@123'
]);

if (!$loginResponse->successful()) {
    echo "Login failed: " . $loginResponse->body() . PHP_EOL;
    exit(1);
}

// Get cookies from login response
$cookies = $loginResponse->headers()->get('Set-Cookie');

// Step 3: Now test the messages API with the session
$headers = [
    'Cookie' => implode('; ', $cookies),
    'X-CSRF-TOKEN' => $csrfToken, // Note: for API requests, we still need the CSRF token if the route is web middleware
    'Accept' => 'application/json',
    'Content-Type' => 'application/json'
];

// Get threads
echo PHP_EOL . "Getting message threads..." . PHP_EOL;
$threadsResponse = Http::withHeaders($headers)->get('http://127.0.0.1:8000/api/messages/threads');
if ($threadsResponse->successful()) {
    $threads = $threadsResponse->json();
    echo "Found " . count($threads) . " threads:" . PHP_EOL;
    foreach ($threads as $thread) {
        echo "  - ID: " . $thread['id'] . ", Title: " . $thread['title'] . ", Last message: " . $thread['last_message'] . PHP_EOL;
        if ($thread['title'] === 'Community Chat') {
            echo "    ^ This is our group chat and it's the first thread due to sorting!" . PHP_EOL;
        }
    }
} else {
    echo "Failed to get threads: " . $threadsResponse->body() . PHP_EOL;
}

// Get messages from community chat (we need to find the ID of the community chat)
$communityChatId = null;
foreach ($threads as $thread) {
    if ($thread['title'] === 'Community Chat') {
        $communityChatId = $thread['id'];
        break;
    }
}

if ($communityChatId === null) {
    echo "Community chat not found!" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Getting messages from Community Chat (ID {$communityChatId})..." . PHP_EOL;
$messagesResponse = Http::withHeaders($headers)->get("http://127.0.0.1:8000/api/messages/{$communityChatId}");
if ($messagesResponse->successful()) {
    $messages = $messagesResponse->json();
    echo "Found " . count($messages) . " messages in community chat:" . PHP_EOL;
    foreach ($messages as $msg) {
        echo "  - [" . $msg['sender_type'] . "] " . $msg['sender_name'] . ": " . $msg['content'] . " (" . $msg['created_at'] . ")" . PHP_EOL;
    }
} else {
    echo "Failed to get messages: " . $messagesResponse->body() . PHP_EOL;
}

// Send a test message to community chat
echo PHP_EOL . "Sending test message to Community Chat..." . PHP_EOL;
$sendResponse = Http::withHeaders($headers)->post("http://127.0.0.1:8000/api/messages/{$communityChatId}", [
    'content' => 'This is a test message from the community chat implementation at ' . now()->toDateTimeString()
]);

if ($sendResponse->successful()) {
    $result = $sendResponse->json();
    if ($result['success']) {
        echo "Message sent successfully!" . PHP_EOL;
        echo "New message: [" . $result['message']['sender_type'] . "] " .
             $result['message']['sender_name'] . ": " . $result['message']['content'] .
             " (" . $result['message']['created_at'] . ")" . PHP_EOL;
    } else {
        echo "Failed to send message: " . $result['message'] . PHP_EOL;
    }
} else {
    echo "Failed to send message: " . $sendResponse->body() . PHP_EOL;
}

// Verify the message was added
echo PHP_EOL . "Verifying message was added..." . PHP_EOL;
$verifyResponse = Http::withHeaders($headers)->get("http://127.0.0.1:8000/api/messages/{$communityChatId}");
if ($verifyResponse->successful()) {
    $messages = $verifyResponse->json();
    echo "Now have " . count($messages) . " messages in community chat:" . PHP_EOL;
    foreach ($messages as $msg) {
        echo "  - [" . $msg['sender_type'] . "] " . $msg['sender_name'] . ": " . $msg['content'] . " (" . $msg['created_at'] . ")" . PHP_EOL;
    }
} else {
    echo "Failed to verify messages: " . $verifyResponse->body() . PHP_EOL;
}

echo PHP_EOL . "Test completed." . PHP_EOL;