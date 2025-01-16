<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body d-flex">
                <form action="" method="POST" enctype="multipart/form-data" class="profile-form">
                    @csrf
                    <div class="file profile-file">
                        <img src="{{ auth()->user()->avatar }}" alt="Upload" class="img-fluid profile-image-preview">
                        <label for="select_file"><i class="fal fa-camera-alt"></i></label>
                        <input id="select_file" type="file" hidden name="avatar">
                    </div>
                    <p>Cập Nhật Thông Tin.</p>
                    <input type="text" placeholder="Họ và tên" value="{{ auth()->user()->name }}" name="name">
                    <input type="text" placeholder="Tên người dùng" value="{{ auth()->user()->user_name }}"
                        name="user_id">
                    <input type="email" placeholder="Email" value="{{ auth()->user()->email }}" name="email">
                    <p>Thay Đổi Mật Khẩu</p>
                    <div class="row">
                        <div class="col-xl-12">
                            <input type="password" placeholder="Mật khẩu hiện tại" name="current_password">
                        </div>
                        <div class="col-xl-12">
                            <input type="password" placeholder="Mật khẩu mới" name="password">
                        </div>
                        <div class="col-xl-12">
                            <input type="password" placeholder="Xác nhận mật khẩu mới" name="password_confirmation">
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary save profile-save">Cập nhật</button>
                    </div>
                </form>
                <div><button type="button" data-bs-dismiss="modal" style="font-size: 22px;">x</button></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.profile-form').on('submit', function(event) {
                event.preventDefault();
                let formData = new FormData(this);
                let saveBtn = $('.profile-save');

                $.ajax({
                    method: 'POST',
                    url: "{{ route('profile.update') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        saveBtn.text('Đang xử lý...');
                        saveBtn.prop('disabled', true);
                    },
                    success: function(response) {
                        window.location.reload();
                    },
                    error: function(xhr, status, error) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(index, value) {
                            notyf.error(value[0]);
                        });
                        saveBtn.text('Cập nhật');
                        saveBtn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
