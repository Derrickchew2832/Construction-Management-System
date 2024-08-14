@extends('layouts.projectmanagerapp')

@section('title', 'Invite Contractor')

@section('content')
<div class="container mt-4">
    <h1>Invite Contractor to {{ $project->name }}</h1>
    <form action="{{ route('project_manager.projects.storeInvite', $project) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="contractor_id">Select Contractor</label>
            <select name="contractor_id" id="contractor_id" class="form-control" required>
                @foreach($contractors as $contractor)
                    <option value="{{ $contractor->id }}">{{ $contractor->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Invite Contractor</button>
    </form>
</div>
@endsection
