@extends('layouts.management')

@section('content')
<div class="container">
    <h3>Project Statistics</h3>
    <ul>
        <li><strong>Number of Contractors:</strong> {{ $contractorCount }}</li>
        <li><strong>Tasks Completed:</strong> {{ $completedTasksCount }}</li>
        <li><strong>Main Contractor:</strong> {{ $mainContractorName }}</li>
    </ul>
</div>
@endsection
