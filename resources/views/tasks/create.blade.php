@extends('layouts.management')

@section('content')
<div class="container mt-5">
    <h3>Create New Task</h3>
    <form action="{{ route('tasks.store', $project->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="title">Task Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="description">Task Description</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" name="due_date" id="due_date" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="category">Task Category</label>
            <select name="category" id="category" class="form-control" required>
                <option value="under_negotiation">Under Negotiation</option>
                <option value="follow_up">Follow Up</option>
                <option value="priority_1">Priority 1</option>
                <option value="priority_2">Priority 2</option>
                <option value="completed">Completed</option>
                <option value="verified">Verified</option>
            </select>
        </div>

        <div class="form-group">
            <label for="invited_contractors">Invite Contractors</label>
            <select multiple name="invited_contractors[]" id="invited_contractors" class="form-control">
                @foreach($contractors as $contractor)
                    <option value="{{ $contractor->id }}">{{ $contractor->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create Task</button>
    </form>
</div>
@endsection
