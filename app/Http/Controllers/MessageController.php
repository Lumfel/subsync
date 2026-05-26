<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConvParticipant;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /** GET /api/messages/threads — list all conversations for current user */
    public function threads()
    {
        $isResident = Auth::guard('resident')->check();
        $isOfficer  = Auth::guard('officer')->check();
        $isAdmin    = !$isResident && !$isOfficer && session('admin_id');

        if ($isResident) {
            $residentId = Auth::guard('resident')->id();
            $convs = Conversation::whereHas('participants', fn($q) => $q->where('resident_id', $residentId))
                ->with(['messages' => fn($q) => $q->latest()->limit(1)])
                ->get();
        } else {
            // Admin and officers see all conversations
            $convs = Conversation::with(['messages' => fn($q) => $q->latest()->limit(1)])
                ->latest('id')
                ->get();
        }

        $result = $convs->map(fn($c) => [
            'id'           => $c->id,
            'title'        => $c->title,
            'last_message' => $c->messages->first()?->content ?? '',
            'last_time'    => $c->messages->first()?->created_at ? \Carbon\Carbon::parse($c->messages->first()->created_at)->format('M d, g:i A') : '',
        ]);

        return response()->json($result);
    }

    /** GET /api/messages/{convId} — messages in a conversation */
    public function show(int $convId)
    {
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

        $msg = ['conversation_id' => $convId, 'content' => $data['content']];

        if (Auth::guard('resident')->check()) {
            $msg['resident_id'] = Auth::guard('resident')->id();
            $msg['sender_type'] = 'resident';
        } elseif (Auth::guard('officer')->check()) {
            $msg['officer_id']  = Auth::guard('officer')->id();
            $msg['sender_type'] = 'officer';
        } else {
            $msg['sender_type'] = 'admin';
        }

        $msg['created_at'] = now();

        $message = Message::create($msg);

        $senderName = (Auth::guard('resident')->user()?->name) ?? (Auth::guard('officer')->user()?->name) ?? session('admin_name', 'Admin');

        return response()->json(['success' => true, 'message' => [
            'id'          => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $senderName,
            'content'     => $message->content,
            'created_at'  => $message->created_at ? \Carbon\Carbon::parse($message->created_at)->format('g:i A') : now()->format('g:i A'),
        ]]);
    }

    /** POST /api/messages/start — start a new conversation */
    public function start(Request $request)
    {
        $request->validate(['title' => 'required|string|max:200']);

        $conv = Conversation::create(['title' => $request->title]);

        // Prioritise authenticated guards before checking the admin session key,
        // so that a resident/officer logged in on the same browser as an admin
        // is still correctly registered as the conversation participant.
        if (Auth::guard('resident')->check()) {
            ConvParticipant::create([
                'conversation_id'  => $conv->id,
                'resident_id'      => Auth::guard('resident')->id(),
                'participant_type' => 'resident',
            ]);
        } elseif (Auth::guard('officer')->check()) {
            ConvParticipant::create([
                'conversation_id'  => $conv->id,
                'officer_id'       => Auth::guard('officer')->id(),
                'participant_type' => 'officer',
            ]);
        } elseif (session('admin_id')) {
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
            if ($request->recipient_type === 'officer' && !Auth::guard('officer')->check()) {
                ConvParticipant::create([
                    'conversation_id'  => $conv->id,
                    'officer_id'       => $request->recipient_id,
                    'participant_type' => 'officer',
                ]);
            } elseif ($request->recipient_type === 'resident' && !Auth::guard('resident')->check()) {
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
