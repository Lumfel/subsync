<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Announcement;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Facility;
use App\Models\FinancialRecord;
use App\Models\HoaExpense;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Officer;
use App\Models\OfficerFile;
use App\Models\PaymentSubmission;
use App\Models\Resident;

echo "--- SubSync Database Cleanup: Removing Clutter & Legacy Test Data ---\n\n";

// 1. ANNOUNCEMENTS
$deletedAnnouncements = Announcement::whereIn('title', [
    'Wiw',
    'MONTHLY MEETING',
    'Sample',
    'A draft',
    'Financial Transparency Digest — October 2026',
    'Test System Announcement',
])->delete();
echo "1. Announcements: Deleted {$deletedAnnouncements} clutter announcement(s).\n";

// 2. ISSUES
$clutterIssues = IssueReport::whereIn('title', ['oooo', ',.,.,'])->get();
$deletedIssuesCount = 0;
foreach ($clutterIssues as $issue) {
    $issue->responses()->delete();
    $issue->media()->delete();
    $issue->delete();
    $deletedIssuesCount++;
}
echo "2. Issues: Deleted {$deletedIssuesCount} spam/clutter issue report(s).\n";

// 3. FACILITIES (Delete old duplicate facilities with generic/short names)
$deletedFacilities = Facility::whereIn('id', [1, 2, 3, 4, 8])->delete();
echo "3. Facilities: Deleted {$deletedFacilities} duplicate/old facility marker(s).\n";

// 4. HOA EXPENSES (Delete old 5 low-value duplicate expenses)
$deletedExpenses = HoaExpense::whereIn('id', [1, 2, 3, 4, 5])->delete();
echo "4. HOA Expenses: Deleted {$deletedExpenses} duplicate/legacy expense record(s).\n";

// 5. PAYMENTS & FINANCIAL RECORDS
$deletedPayments = PaymentSubmission::where('reference_no', '121212121')->delete();
$deletedFinRecs = FinancialRecord::where('description', 'like', '%121212121%')->delete();
echo "5. Payments: Deleted {$deletedPayments} dummy payment(s) and {$deletedFinRecs} linked ledger record(s).\n";

// 6. EVENTS
$deletedEvents = Event::where('title', 'www')->delete();
echo "6. Events: Deleted {$deletedEvents} dummy event(s).\n";

// 7. OFFICER FILES
$deletedFiles = OfficerFile::where('original_name', 'money.xlsx')->delete();
echo "7. Officer Files: Deleted {$deletedFiles} dummy file(s).\n";

// 8. CONVERSATIONS
$clutterConvs = Conversation::whereIn('title', ['Community Chat', 'hello', 'idk'])->get();
$deletedConvsCount = 0;
foreach ($clutterConvs as $conv) {
    $conv->messages()->delete();
    $conv->participants()->delete();
    $conv->delete();
    $deletedConvsCount++;
}
echo "8. Conversations: Deleted {$deletedConvsCount} test chat thread(s).\n";

// 9. RESIDENTS CLEANUP
// Delete disposable dummy accounts
$deletedResidents = Resident::whereIn('email', [
    'example@paul.com',
    'jurriel42@gmail.com',
    'juan@gmail.com',
    'koosujung150@gmail.com',
])->delete();
echo "9. Residents: Deleted {$deletedResidents} disposable dummy resident account(s).\n";

// Ensure proponents are properly named and mapped to valid subdivision households
$cleanHh1 = Household::where('block_lot_number', 'Blk 1 Lot 5')->first();
$cleanHh2 = Household::where('block_lot_number', 'Blk 2 Lot 3')->first();
$cleanHh4 = Household::where('block_lot_number', 'Blk 4 Lot 2')->first();

if ($cleanHh1) {
    Resident::where('email', 'paulfelippegtibooc@gmail.com')->update([
        'name' => 'Paul Felippe G. Tibo-oc',
        'house_id' => $cleanHh1->id,
        'status' => 'Active',
    ]);
}

if ($cleanHh2) {
    Resident::where('email', 'jurriel24@gmail.com')->update([
        'name' => 'Jurriel A. Guanzon',
        'house_id' => $cleanHh2->id,
        'status' => 'Active',
    ]);
}

if ($cleanHh4) {
    Resident::where('email', 'barbasgaming1@gmail.com')->update([
        'name' => 'Christian J. Barbas Jr.',
        'house_id' => $cleanHh4->id,
        'status' => 'Active',
    ]);
    Resident::where('email', 'hughz2004@gmail.com')->update([
        'name' => 'Christian Barbas',
        'house_id' => $cleanHh4->id,
        'status' => 'Active',
    ]);
}

// 10. OLD UNUSED HOUSEHOLDS
$deletedHouseholds = Household::whereIn('id', [4, 6, 7, 8, 9, 10, 22, 23, 25])->delete();
echo "10. Households: Deleted {$deletedHouseholds} legacy/duplicate household(s).\n";

// 11. OFFICERS
$deletedOfficers = Officer::where('email', 'anita@gmail.com')->delete();
echo "11. Officers: Deleted {$deletedOfficers} dummy officer account(s).\n";

echo "\n--- Cleanup Complete! Checking Current System State ---\n";
echo "Households:        " . Household::count() . "\n";
echo "Residents:         " . Resident::count() . "\n";
echo "Announcements:     " . Announcement::count() . "\n";
echo "Issues:            " . IssueReport::count() . "\n";
echo "Recommendations:   " . \App\Models\Recommendation::count() . "\n";
echo "PaymentSubmissions:" . PaymentSubmission::count() . "\n";
echo "Facilities:        " . Facility::count() . "\n";
echo "HOA Expenses:      " . HoaExpense::count() . "\n";
echo "HOA Policies:      " . \App\Models\HoaPolicy::count() . "\n";
echo "Events:            " . Event::count() . "\n";
echo "Conversations:     " . Conversation::count() . "\n";
