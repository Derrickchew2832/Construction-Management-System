@extends('layouts.projectmanagerapp')

@section('title', 'View Quote')

@section('content')
<div class="container mt-4">
    <h1>Quote Details for {{ $project->name }}</h1>
    <p><strong>Contractor:</strong> {{ $contractor->name }}</p>
    <p><strong>Quoted Price:</strong> ${{ number_format($quote->quoted_price, 2) }}</p>
    <p><strong>Quote Document:</strong> <a href="{{ Storage::url($quote->quote_pdf) }}" target="_blank">View PDF</a></p>
    <p><strong>Status:</strong> {{ ucfirst($quote->status) }}</p>
</div>
@endsection
