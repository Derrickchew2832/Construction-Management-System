@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Invite Users to Project</h1>
    <form action="{{ route('projectmanager.projects.sendInvitation', $project->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">User Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Send Invitation</button>
    </form>
</div>
@endsection
