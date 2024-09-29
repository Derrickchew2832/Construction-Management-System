@extends('layouts.management')

@section('content')
<div class="container">
    <h3>Invite Contractors to Project</h3>
    <form method="POST" action="{{ route('tasks.invite', ['projectId' => $projectId]) }}">
        @csrf
        <div class="form-group">
            <label for="email">Contractor Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Send Invitation</button>
    </form>
</div>
@endsection
