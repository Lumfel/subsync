<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Conversation;
use App\Models\ConvParticipant;
use App\Models\Resident;

// Find resident
$resident = Resident::where('email', 'resident1@subsync.com')->first();
if (!$resident) {
    echo "Resident not found\n";
    exit(1);
}
echo "Found resident: " . $resident->name . PHP_EOL;

// Check/create community chat
$groupConv = Conversation::where('title', 'Community Chat')->first();
if (!$groupConv) {
    $groupConv = Conversation::create(['title' => 'Community Chat']);
    echo "Created community chat conversation with ID: " . $groupConv->id . PHP_EOL;
} else {
    echo "Community chat already exists with ID: " . $groupConv->id . PHP_EOL;
}

// Ensure resident is participant
$participant = ConvParticipant::where('conversation_id', $groupConv->id)
    ->where('resident_id', $resident->id)
    ->first();
if (!$participant) {
    ConvParticipant::create([
        'conversation_id' => $groupConv->id,
        'resident_id' => $resident->id,
        'participant_type' => 'resident'
    ]);
    echo "Added resident as participant to community chat" . PHP_EOL;
} else {
    echo "Resident already participant in community chat" . PHP_EOL;
}

// Add all active residents if not already participants
$activeResidents = Resident::where('status', 'Active')->get();
$existingParticipants = $groupConv->participants->pluck('resident_id')->toArray();
$addedCount = 0;

foreach ($activeResidents as $activeResident) {
    if (!in_array($activeResident->id, $existingParticipants)) {
        ConvParticipant::create([
            'conversation_id' => $groupConv->id,
            'resident_id' => $activeResident->id,
            'participant_type' => 'resident'
        ]);
        $addedCount++;
    }
}

if ($addedCount > 0) {
    echo "Added " . $addedCount . " additional residents to community chat" . PHP_EOL;
} else {
    echo "All active residents already participate in community chat" . PHP_EOL;
}

echo "Total participants: " . $groupConv->participants->count() . PHP_EOL;