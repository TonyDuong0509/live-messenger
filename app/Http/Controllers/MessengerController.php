<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\NotifyService;
use App\Traits\FileUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessengerController extends Controller
{
    use FileUploadTrait;

    public function index(): View
    {
        return view('messenger.index');
    }

    public function search(Request $request)
    {
        $getRecords = null;
        $input = $request['query'];
        $records = User::where('id', '!=', auth()->user()->id)
            ->where('name', 'LIKE', "%{$input}%")
            ->orWhere('user_name', 'LIKE', "%{$input}%")
            ->paginate(10);

        if ($records->total() < 1) {
            $getRecords = "<p class='text-center' style='margin-top: 60%; font-size: 24px;'>Không tìm thấy người dùng này! 🥺</p>";
        }

        foreach ($records as $record) {
            $getRecords .= view('messenger.components.search-item', compact('record'))->render();
        };

        return response()->json([
            'records' => $getRecords,
            'last_page' => $records->lastPage(),
        ]);
    }

    public function fetchIdInfo(Request $request)
    {
        $fetch = User::where('id', $request['id'])->first();

        return response()->json([
            'fetch' => $fetch
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate(
            [
                'id' => ['required', 'integer'],
                'temporaryMsgId' => ['required'],
                'attachment' => ['nullable', 'max:1024', 'image']
            ]
        );

        $attachmentPath = $this->uploadFile($request, 'attachment');
        $message = new Message();
        $message->from_id = Auth::user()->id;
        $message->to_id = $request->id;
        $message->body = $request->message;
        if ($attachmentPath) $message->attachment = json_encode($attachmentPath);
        $message->save();

        return response()->json(
            [
                'message' => $message->attachment ? $this->messageCard($message, true) : $this->messageCard($message),
                'tempID' => $request->temporaryMsgId
            ]
        );
    }

    public function messageCard($message, $attachment = false)
    {
        return view('messenger.components.message-card', compact('message', 'attachment'))->render();
    }
}
