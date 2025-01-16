<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessengerController extends Controller
{
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
}
