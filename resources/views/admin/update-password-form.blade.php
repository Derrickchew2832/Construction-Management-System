<form action="{{ route('admin.updatePassword') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Password</button>
</form>
