<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUserUpdateRequest;
use App\Services\NotifyService;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    use FileUploadTrait;

    public function update(Request $request): Response
    {
        $request->validate(
            [
                'avatar' => ['nullable', 'image', 'max:3000', 'mimes:png,jpg,jpeg,gif'],
                'name' => ['required', 'string', 'max:50'],
                'user_id' => ['required', 'string', 'max:50', 'unique:users,user_name,' . auth()->user()->id],
                'email' => ['required', 'email', 'max:100']
            ],
            [
                'avatar.image' => 'Không thể chọn file khác, vui lòng chọn file ảnh.',
                'avatar.mimes' => 'Ảnh phải là file PNG | JPG | JPEG | GIF',

                'name.required' => 'Họ và tên không được để trống.',
                'name.string' => 'Tên phải là chữ không được có số hay ký tự.',
                'name.max' => 'Tối đa 50 ký tự.',

                'user_id.required' => 'Tên người dùng không được để trống.',
                'user_id.max' => 'Tối đa 50 ký tự.',
                'user_id.unique' => 'Tên người dùng này đã tồn tại.',

                'email.required' => 'Email không được để trống.',
                'email.email' => 'Email không hợp lệ.',
                'email.max' => 'Email không được vượt quá 100 ký tự.'
            ]
        );

        $avatarPath = $this->uploadFile($request, 'avatar');
        $user = Auth::user();
        if ($avatarPath) $user->avatar = $avatarPath;
        $user->name = $request->name;
        $user->user_name = $request->user_id;
        $user->email = $request->email;

        if ($request->filled('current_password')) {
            $request->validate(
                [
                    'current_password' => ['required', 'current_password'],
                    'password' => ['required', 'confirmed', 'string', 'min:8']
                ],
                [
                    'current_password.required' => 'Mật khẩu hiện tại không được để trống',
                    'current_password.current_password' => 'Mật khẩu hiện tại không đúng',

                    'password.required' => 'Mật khẩu mới không được để trống',
                    'password.confirmed' => 'Mật khẩu mới và xác thực mật khẩu không trùng khớp',
                    'password.min' => 'Mật khẩu ít nhất 8 ký tự',
                ]
            );
            $user->password = bcrypt($request->password);
        }

        $user->save();

        NotifyService::SuccessNotification('Cập nhật thông tin thành công! 🎉');

        return response(['message' => 'Cập nhật thông tin thành công! 🎉'], 200);
    }
}
