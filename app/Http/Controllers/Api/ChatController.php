<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OpenAIService;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'message'    => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $response = OpenAIService::sendChatMessage(
            $data['message'],
            $data['session_id'] ?? null
        );

        return response()->json(['response' => $response]);
    }
}
