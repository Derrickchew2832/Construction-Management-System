@extends('layouts.projectmanagerapp')

@section('title', 'Manage Quotes')

@section('content')
<div class="container mt-4">
    <h1>Manage Quotes for {{ $project->name }}</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Contractor</th>
                <th>Quoted Price</th>
                <th>Quote Document</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotes as $quote)
                <tr>
                    <td>{{ $quote->name }}</td>
                    <td>{{ $quote->pivot->quoted_price }}</td>
                    <td><a href="{{ Storage::url($quote->pivot->quote_document_path) }}" target="_blank">View Document</a></td>
                    <td>{{ ucfirst($quote->pivot->status) }}</td>
                    <td>
                        @if($quote->pivot->status == 'pending')
                            <form action="{{ route('project_manager.projects.approveQuote', [$project, $quote]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">Approve</button>
                            </form>
                            <form action="{{ route('project_manager.projects.rejectQuote', [$project, $quote]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger">Reject</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
