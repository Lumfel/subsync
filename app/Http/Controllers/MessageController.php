<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConvParticipant;
use App\Models\Message;
use App\Models\Officer;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MessageController extends Controller
{
    private function currentActor(): array
    {
        $activeRole = session('active_role');

        if (session('admin_id')) {
            return ['type' => 'admin', 'id' => session('admin_id'), 'name' => session('admin_name', 'Admin')];
        }

        if ($activeRole === 'officer' && Auth::guard('officer')->check()) {
            $officer = Auth::guard('officer')->user();

            return ['type' => 'officer', 'id' => $officer->id, 'name' => $officer->name];
        }

        if ($activeRole === 'resident' && Auth::guard('resident')->check()) {
            $resident = Auth::guard('resident')->user();

            return ['type' => 'resident', 'id' => $resident->id, 'name' => $resident->name];
        }

        if ($activeRole === 'resident' && session('resident_id')) {
            $resident = Resident::query()->find(session('resident_id'));

            return ['type' => 'resident', 'id' => session('resident_id'), 'name' => $resident?->name];
        }

        if (Auth::guard('resident')->check()) {
            $resident = Auth::guard('resident')->user();

            return ['type' => 'resident', 'id' => $resident->id, 'name' => $resident->name];
        }

        if (Auth::guard('officer')->check()) {
            $officer = Auth::guard('officer')->user();

            return ['type' => 'officer', 'id' => $officer->id, 'name' => $officer->name];
        }

        if ($activeRole === 'officer' && session('officer_id')) {
            $officer = Officer::query()->find(session('officer_id'));

            return ['type' => 'officer', 'id' => session('officer_id'), 'name' => $officer?->name];
        }

        return ['type' => null, 'id' => null, 'name' => null];
    }

    private function canAccessConversation(array $actor, int $conversationId): bool
    {
        if ($actor['type'] === 'admin') {
            return true;
        }

        if ($actor['type'] === 'resident') {
            return ConvParticipant::where('conversation_id', $conversationId)
                ->where('resident_id', $actor['id'])
                ->exists();
        }

        if ($actor['type'] === 'officer') {
            return ConvParticipant::where('conversation_id', $conversationId)
                ->where('officer_id', $actor['id'])
                ->exists();
        }

        return false;
    }

    /** 'accepted' | 'pending' | null (admin is always accepted). */
    private function participantStatus(array $actor, int $conversationId): ?string
    {
        if ($actor['type'] === 'admin') {
            return 'accepted';
        }

        $q = ConvParticipant::where('conversation_id', $conversationId);
        if ($actor['type'] === 'resident') {
            $q->where('resident_id', $actor['id']);
        } elseif ($actor['type'] === 'officer') {
            $q->where('officer_id', $actor['id']);
        } else {
            return null;
        }

        return $q->value('status');
    }

    private function displayTitleForActor(Conversation $conversation, array $actor): string
    {
        if ($actor['type'] !== 'admin') {
            return $conversation->title ?? '';
        }

        $participant = $conversation->participants->first();

        return $participant?->resident?->name
            ?? $participant?->officer?->name
            ?? $conversation->title
            ?? '';
    }

    /**
     * Ensure group conversation exists for residents.
     * Participant sync is cached for 60s — new residents join within a minute,
     * which is acceptable and avoids 2-3 queries on every threads() call.
     */
    private function ensureGroupConversationExists(): int
    {
        $groupConv = Conversation::firstOrCreate(['title' => 'Community Chat']);

        if (Cache::has('group.chat.participants.synced')) {
            return $groupConv->id;
        }

        $activeResidentIds = Resident::where('status', 'Active')->pluck('id');
        $participantIds = ConvParticipant::where('conversation_id', $groupConv->id)
            ->where('participant_type', 'resident')
            ->whereNotNull('resident_id')
            ->pluck('resident_id');
        $toAdd = $activeResidentIds->diff($participantIds)->values();

        if ($toAdd->isNotEmpty()) {
            ConvParticipant::insert($toAdd->map(fn ($residentId) => [
                'conversation_id' => $groupConv->id,
                'resident_id' => $residentId,
                'participant_type' => 'resident',
                'created_at' => now(),
            ])->all());
        }

        Cache::put('group.chat.participants.synced', true, 60);

        return $groupConv->id;
    }

    /** GET /api/messages/officers — active officers a resident/officer can message */
    public function officers()
    {
        return response()->json(
            Officer::where('status', 'Active')->orderBy('name')->get(['id', 'name'])
        );
    }

    /** GET /api/messages/residents — active residents staff can message (lightweight) */
    public function residents()
    {
        return response()->json(
            Resident::where('status', 'Active')->orderBy('name')->get(['id', 'name'])
        );
    }

    /** GET /api/messages/requests — pending message requests for the current user */
    public function requests()
    {
        $actor = $this->currentActor();

        if (! $actor['type']) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 401);
        }

        if ($actor['type'] === 'admin') {
            return response()->json([]);
        }

        $column = $actor['type'] === 'resident' ? 'resident_id' : 'officer_id';

        $convs = Conversation::whereHas('participants', fn ($q) => $q->where($column, $actor['id'])->where('status', 'pending'))
            ->with([
                'messages' => fn ($q) => $q->latest()->limit(1),
                'participants.resident:id,name',
                'participants.officer:id,name',
            ])
            ->latest('id')
            ->get();

        $result = $convs->map(function ($c) {
            $initiator = $c->participants->first(fn ($p) => $p->status === 'accepted');

            return [
                'id' => $c->id,
                'title' => $c->title,
                'from' => $initiator?->resident?->name ?? $initiator?->officer?->name ?? '',
                'last_message' => $c->messages->first()?->content ?? '',
                'last_time' => $c->messages->first()?->created_at ? Carbon::parse($c->messages->first()->created_at)->format('M d, g:i A') : '',
            ];
        });

        return response()->json($result);
    }

    /** POST /api/messages/{convId}/accept — accept a message request */
    public function accept(int $convId)
    {
        $actor = $this->currentActor();
        if (! $actor['type']) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 401);
        }

        Conversation::findOrFail($convId);

        if ($actor['type'] === 'admin') {
            return response()->json(['success' => true]);
        }

        $column = $actor['type'] === 'resident' ? 'resident_id' : 'officer_id';
        $participant = ConvParticipant::where('conversation_id', $convId)->where($column, $actor['id'])->first();
        abort_unless($participant, 403);

        if ($participant->status !== 'accepted') {
            $participant->update(['status' => 'accepted']);
        }

        return response()->json(['success' => true]);
    }

    /** POST /api/messages/{convId}/reject — decline a message request (deletes it for both) */
    public function reject(int $convId)
    {
        $actor = $this->currentActor();
        if (! $actor['type']) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 401);
        }

        $conv = Conversation::findOrFail($convId);
        abort_if($actor['type'] === 'admin', 403);

        $column = $actor['type'] === 'resident' ? 'resident_id' : 'officer_id';
        $participant = ConvParticipant::where('conversation_id', $convId)->where($column, $actor['id'])->first();
        abort_unless($participant && $participant->status === 'pending', 403);

        Message::where('conversation_id', $convId)->delete();
        ConvParticipant::where('conversation_id', $convId)->delete();
        $conv->delete();

        return response()->json(['success' => true]);
    }

    /** GET /api/messages/threads — list all conversations for current user */
    public function threads()
    {
        $actor = $this->currentActor();

        if (! $actor['type']) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 401);
        }

        if ($actor['type'] === 'resident') {
            // For residents, ensure group conversation exists and include it
            $groupConvId = $this->ensureGroupConversationExists();

            $convs = Conversation::whereHas('participants', fn ($q) => $q->where('resident_id', $actor['id'])->where('status', 'accepted'))
                ->orWhere(function ($query) use ($groupConvId) {
                    $query->where('id', $groupConvId);
                })
                ->with([
                    'messages' => fn ($q) => $q->latest()->limit(1),
                    'participants.resident:id,name',
                    'participants.officer:id,name',
                ])
                ->get();
        } elseif ($actor['type'] === 'officer') {
            $convs = Conversation::whereHas('participants', fn ($q) => $q->where('officer_id', $actor['id'])->where('status', 'accepted'))
                ->with([
                    'messages' => fn ($q) => $q->latest()->limit(1),
                    'participants.resident:id,name',
                    'participants.officer:id,name',
                ])
                ->latest('id')
                ->get();
        } else {
            $convs = Conversation::with([
                'messages' => fn ($q) => $q->latest()->limit(1),
                'participants.resident:id,name',
                'participants.officer:id,name',
            ])
                ->latest('id')
                ->get();
        }

        $result = $convs->map(fn ($c) => [
            'id' => $c->id,
            'title' => $this->displayTitleForActor($c, $actor),
            'last_message' => $c->messages->first()?->content ?? '',
            'last_time' => $c->messages->first()?->created_at ? Carbon::parse($c->messages->first()->created_at)->format('M d, g:i A') : '',
        ]);

        return response()->json($result);
    }

    /** GET /api/messages/{convId} — messages in a conversation */
    public function show(int $convId)
    {
        $actor = $this->currentActor();
        Conversation::findOrFail($convId);
        abort_unless($this->canAccessConversation($actor, $convId), 403);

        $messages = Message::where('conversation_id', $convId)
            ->with('resident:id,name', 'officer:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(function ($m) use ($actor) {
                $isMe = match ($actor['type']) {
                    'resident' => $actor['id'] !== null && $m->resident_id === $actor['id'],
                    'officer' => $actor['id'] !== null && $m->officer_id === $actor['id'],
                    'admin' => $m->sender_type === 'admin',
                    default => false,
                };

                return [
                    'id' => $m->id,
                    'sender_type' => $m->sender_type,
                    'sender_name' => $m->resident?->name ?? $m->officer?->name ?? 'Admin',
                    'content' => $m->content,
                    'is_me' => $isMe,
                    'created_at' => $m->created_at ? Carbon::parse($m->created_at)->format('g:i A') : '',
                ];
            });

        return response()->json($messages);
    }

    /** POST /api/messages/{convId} — send a message */
    public function send(Request $request, int $convId)
    {
        $data = $request->validate(['content' => 'required|string|max:4000']);
        $data['content'] = trim($data['content']);
        $actor = $this->currentActor();

        if (! $actor['type']) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please log in again.'], 401);
        }

        Conversation::findOrFail($convId);
        abort_unless($this->canAccessConversation($actor, $convId), 403);

        // Message requests must be accepted before you can reply.
        if ($actor['type'] !== 'admin' && $this->participantStatus($actor, $convId) !== 'accepted') {
            return response()->json(['success' => false, 'message' => 'Accept the message request before replying.'], 403);
        }

        $msg = ['conversation_id' => $convId, 'content' => $data['content']];
        if ($actor['type'] === 'resident') {
            $msg['resident_id'] = $actor['id'];
            $msg['sender_type'] = 'resident';
        } elseif ($actor['type'] === 'officer') {
            $msg['officer_id'] = $actor['id'];
            $msg['sender_type'] = 'officer';
        } else {
            $msg['sender_type'] = 'admin';
        }

        $msg['created_at'] = now();

        $message = Message::create($msg);

        return response()->json(['success' => true, 'message' => [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $actor['name'] ?? 'Admin',
            'content' => $message->content,
            'is_me' => true,
            'created_at' => $message->created_at ? Carbon::parse($message->created_at)->format('g:i A') : now()->format('g:i A'),
        ]]);
    }

    /** DELETE /api/messages/{convId} — delete a conversation and all its messages */
    public function destroy(int $convId)
    {
        $actor = $this->currentActor();
        if (! $actor['type']) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 401);
        }

        abort_unless($this->canAccessConversation($actor, $convId), 403);

        $conv = Conversation::findOrFail($convId);
        abort_if($conv->title === 'Community Chat' && $actor['type'] !== 'admin', 403);
        Message::where('conversation_id', $convId)->delete();
        ConvParticipant::where('conversation_id', $convId)->delete();
        $conv->delete();

        return response()->json(['success' => true]);
    }

    /** POST /api/messages/start — start a new conversation */
    public function start(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'recipient_type' => 'nullable|in:resident,officer',
            'recipient_id' => 'nullable|integer',
            'resident_id' => 'nullable|integer|exists:residents,id',
            'officer_id' => 'nullable|integer|exists:officers,id',
        ]);
        $actor = $this->currentActor();

        if (! $actor['type']) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please log in again.'], 401);
        }

        $conv = Conversation::create(['title' => trim($request->title)]);

        if ($actor['type'] === 'resident') {
            ConvParticipant::create([
                'conversation_id' => $conv->id,
                'resident_id' => $actor['id'],
                'participant_type' => 'resident',
                'status' => 'accepted',
            ]);
        } elseif ($actor['type'] === 'officer') {
            ConvParticipant::create([
                'conversation_id' => $conv->id,
                'officer_id' => $actor['id'],
                'participant_type' => 'officer',
                'status' => 'accepted',
            ]);
        } elseif ($actor['type'] === 'admin') {
            // Admin starts a conversation — the recipient gets a message request.
            if ($request->filled('resident_id')) {
                ConvParticipant::create([
                    'conversation_id' => $conv->id,
                    'resident_id' => $request->resident_id,
                    'participant_type' => 'resident',
                    'status' => 'pending',
                ]);
            }
            if ($request->filled('officer_id')) {
                ConvParticipant::create([
                    'conversation_id' => $conv->id,
                    'officer_id' => $request->officer_id,
                    'participant_type' => 'officer',
                    'status' => 'pending',
                ]);
            }
        }

        // Also add the selected recipient (for resident→officer or officer→resident conversations)
        if ($request->filled('recipient_type') && $request->filled('recipient_id')) {
            if ($request->recipient_type === 'officer' && $actor['type'] !== 'officer') {
                abort_unless(Officer::whereKey($request->recipient_id)->exists(), 422);
                ConvParticipant::create([
                    'conversation_id' => $conv->id,
                    'officer_id' => $request->recipient_id,
                    'participant_type' => 'officer',
                    'status' => 'pending',
                ]);
            } elseif ($request->recipient_type === 'resident' && $actor['type'] !== 'resident') {
                abort_unless(Resident::whereKey($request->recipient_id)->exists(), 422);
                ConvParticipant::create([
                    'conversation_id' => $conv->id,
                    'resident_id' => $request->recipient_id,
                    'participant_type' => 'resident',
                    'status' => 'pending',
                ]);
            }
        }

        return response()->json(['success' => true, 'conversation' => [
            'id' => $conv->id,
            'title' => $conv->title,
            'last_message' => '',
            'last_time' => '',
        ]]);
    }
}
