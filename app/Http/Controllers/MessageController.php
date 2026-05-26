<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConvParticipant;
use App\Models\Message;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            $resident = Resident::find(session('resident_id'));
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
            $officer = Officer::find(session('officer_id'));
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

    /** GET /api/messages/threads — list all conversations for current user */
    public function threads()
    {
        $actor = $this->currentActor();

        if ($actor['type'] === 'resident') {
            $convs = Conversation::whereHas('participants', fn($q) => $q->where('resident_id', $actor['id']))
                ->with([
                    'messages' => fn($q) => $q->latest()->limit(1),
                    'participants.resident:id,name',
                    'participants.officer:id,name',
                ])
                ->get();
        } elseif ($actor['type'] === 'officer') {
            $convs = Conversation::whereHas('participants', fn($q) => $q->where('officer_id', $actor['id']))
                ->with([
                    'messages' => fn($q) => $q->latest()->limit(1),
                    'participants.resident:id,name',
                    'participants.officer:id,name',
                ])
                ->latest('id')
                ->get();
        } else {
            $convs = Conversation::with([
                'messages' => fn($q) => $q->latest()->limit(1),
                'participants.resident:id,name',
                'participants.officer:id,name',
            ])
                ->latest('id')
                ->get();
        }

        $result = $convs->map(fn($c) => [
            'id'           => $c->id,
            'title'        => $this->displayTitleForActor($c, $actor),
            'last_message' => $c->messages->first()?->content ?? '',
            'last_time'    => $c->messages->first()?->created_at ? \Carbon\Carbon::parse($c->messages->first()->created_at)->format('M d, g:i A') : '',
        ]);

        return response()->json($result);
    }

    /** GET /api/messages/{convId} — messages in a conversation */
    public function show(int $convId)
    {
        abort_unless($this->canAccessConversation($this->currentActor(), $convId), 403);

        $messages = Message::where('conversation_id', $convId)
            ->with('resident:id,name', 'officer:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'sender_type'  => $m->sender_type,
                'sender_name'  => $m->resident?->name ?? $m->officer?->name ?? 'Admin',
                'content'      => $m->content,
                'created_at'   => $m->created_at ? \Carbon\Carbon::parse($m->created_at)->format('g:i A') : '',
            ]);

        return response()->json($messages);
    }

    /** POST /api/messages/{convId} — send a message */
    public function send(Request $request, int $convId)
    {
        $data = $request->validate(['content' => 'required|string']);
        $actor = $this->currentActor();

        if (!$actor['type']) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please log in again.'], 401);
        }

        abort_unless($this->canAccessConversation($actor, $convId), 403);

        $msg = ['conversation_id' => $convId, 'content' => $data['content']];

        if ($actor['type'] === 'resident') {
            $msg['resident_id'] = $actor['id'];
            $msg['sender_type'] = 'resident';
        } elseif ($actor['type'] === 'officer') {
            $msg['officer_id']  = $actor['id'];
            $msg['sender_type'] = 'officer';
        } else {
            $msg['sender_type'] = 'admin';
        }

        $msg['created_at'] = now();

        $message = Message::create($msg);

        return response()->json(['success' => true, 'message' => [
            'id'          => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $actor['name'] ?? 'Admin',
            'content'     => $message->content,
            'created_at'  => $message->created_at ? \Carbon\Carbon::parse($message->created_at)->format('g:i A') : now()->format('g:i A'),
        ]]);
    }

    /** DELETE /api/messages/{convId} — delete a conversation and all its messages */
    public function destroy(int $convId)
    {
        $conv = Conversation::findOrFail($convId);
        Message::where('conversation_id', $convId)->delete();
        ConvParticipant::where('conversation_id', $convId)->delete();
        $conv->delete();
        return response()->json(['success' => true]);
    }

    /** POST /api/messages/start — start a new conversation */
    public function start(Request $request)
    {
        $request->validate(['title' => 'required|string|max:200']);
        $actor = $this->currentActor();

        if (!$actor['type']) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please log in again.'], 401);
        }

        $conv = Conversation::create(['title' => $request->title]);

        if ($actor['type'] === 'resident') {
            ConvParticipant::create([
                'conversation_id'  => $conv->id,
                'resident_id'      => $actor['id'],
                'participant_type' => 'resident',
            ]);
        } elseif ($actor['type'] === 'officer') {
            ConvParticipant::create([
                'conversation_id'  => $conv->id,
                'officer_id'       => $actor['id'],
                'participant_type' => 'officer',
            ]);
        } elseif ($actor['type'] === 'admin') {
            // Admin starts a conversation — optionally pre-add a resident or officer participant
            if ($request->filled('resident_id')) {
                ConvParticipant::create([
                    'conversation_id'  => $conv->id,
                    'resident_id'      => $request->resident_id,
                    'participant_type' => 'resident',
                ]);
            }
            if ($request->filled('officer_id')) {
                ConvParticipant::create([
                    'conversation_id'  => $conv->id,
                    'officer_id'       => $request->officer_id,
                    'participant_type' => 'officer',
                ]);
            }
        }

        // Also add the selected recipient (for resident→officer or officer→resident conversations)
        if ($request->filled('recipient_type') && $request->filled('recipient_id')) {
            if ($request->recipient_type === 'officer' && $actor['type'] !== 'officer') {
                ConvParticipant::create([
                    'conversation_id'  => $conv->id,
                    'officer_id'       => $request->recipient_id,
                    'participant_type' => 'officer',
                ]);
            } elseif ($request->recipient_type === 'resident' && $actor['type'] !== 'resident') {
                ConvParticipant::create([
                    'conversation_id'  => $conv->id,
                    'resident_id'      => $request->recipient_id,
                    'participant_type' => 'resident',
                ]);
            }
        }

        return response()->json(['success' => true, 'conversation' => [
            'id'           => $conv->id,
            'title'        => $conv->title,
            'last_message' => '',
            'last_time'    => '',
        ]]);
    }
}
