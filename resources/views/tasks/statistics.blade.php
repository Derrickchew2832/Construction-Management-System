@extends('layouts.management')

@section('content')
<div class="container">
    <h3>Project Statistics</h3>

    <!-- Flexbox container for horizontal alignment of graphs with spacing -->
    <div class="row">
        <!-- Task Completion Percentage -->
        <div class="col-md-4 mb-4">
            <div class="chart-container text-center" style="position: relative; height:300px; width:100%;">
                <canvas id="taskCompletionChart"></canvas>
                <p>Task Completion Percentage</p>
            </div>
        </div>

        <!-- Task Status Distribution -->
        <div class="col-md-4 mb-4">
            <div class="chart-container text-center" style="position: relative; height:300px; width:100%;">
                <canvas id="taskStatusChart"></canvas>
                <p>Task Status Distribution</p>
            </div>
        </div>

        <!-- Task Distribution by Category -->
        <div class="col-md-4 mb-4">
            <div class="chart-container text-center" style="position: relative; height:300px; width:100%;">
                <canvas id="taskCategoryChart"></canvas>
                <p>Task Distribution by Category</p>
            </div>
        </div>
    </div>

    <!-- Second row for the other graphs with spacing -->
    <div class="row">
        <!-- Project Budget Allocation (Only for Main Contractor) -->
        @if(Auth::user()->id == $project->main_contractor_id)
        <div class="col-md-6 mb-4">
            <div class="chart-container text-center" style="position: relative; height:300px; width:100%;">
                <canvas id="projectBudgetChart"></canvas>
                <p>Project Budget Allocation</p>
            </div>
        </div>
        @endif

        <!-- Number of Contractors Assigned (Only for Main Contractor) -->
        @if(Auth::user()->id == $project->main_contractor_id)
        <div class="col-md-6 mb-4">
            <div class="chart-container text-center" style="position: relative; height:300px; width:100%;">
                <canvas id="contractorAssignmentChart"></canvas>
                <p>Number of Contractors Assigned</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    console.log("Main Contractor Quote: {{ $mainContractorQuote ?? 'null' }}");
    console.log("Main Contractor Tasks Quoted Price: {{ $mainContractorTasksQuotedPrice ?? 'null' }}");
    // Task Completion Percentage Chart
    var taskCompletionCtx = document.getElementById('taskCompletionChart').getContext('2d');
    var taskCompletionChart = new Chart(taskCompletionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed Tasks', 'Remaining Tasks'],
            datasets: [{
                data: [{{ $completedTasksCount }}, {{ $totalTasksCount - $completedTasksCount }}],
                backgroundColor: ['#4CAF50', '#FF5722'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Task Completion Percentage',
                fontSize: 14
            },
            plugins: {
                datalabels: {
                    display: true,
                    color: 'white',
                    formatter: (value, context) => {
                        const percentage = ((value / {{ $totalTasksCount }}) * 100).toFixed(1);
                        return `${value} (${percentage}%)`; // Show value and percentage
                    }
                }
            }
        }
    });

    // Task Status Distribution Chart
    var taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
    var taskStatusChart = new Chart(taskStatusCtx, {
        type: 'pie',
        data: {
            labels: @json($taskStatusData->pluck('status')),
            datasets: [{
                data: @json($taskStatusData->pluck('total')),
                backgroundColor: ['#f39c12', '#2ecc71', '#e74c3c'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Task Status Distribution',
                fontSize: 14
            }
        }
    });

    // Task Distribution by Category Chart
    var taskCategoryCtx = document.getElementById('taskCategoryChart').getContext('2d');
    var taskCategoryChart = new Chart(taskCategoryCtx, {
        type: 'bar',
        data: {
            labels: @json($taskCategoryData->pluck('category')),
            datasets: [{
                label: 'Number of Tasks',
                data: @json($taskCategoryData->pluck('total')),
                backgroundColor: '#3498db',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Task Distribution by Category',
                fontSize: 14
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,  // Display only whole numbers
                        callback: function(value) { 
                            return Number.isInteger(value) ? value : null; 
                        }
                    }
                }
            }
        }
    });

    // Project Budget Allocation Chart (only for Main Contractor)
    @if(Auth::user()->id == $project->main_contractor_id)
    var projectBudgetCtx = document.getElementById('projectBudgetChart').getContext('2d');
    var projectBudgetChart = new Chart(projectBudgetCtx, {
        type: 'pie',
        data: {
            labels: ['Total Project Quoted Price', 'Total Tasks Quoted Price'],
            datasets: [{
                data: [
                    {{ $mainContractorQuote ?? 0 }}, 
                    {{ $mainContractorTasksQuotedPrice ?? 0 }}
                ],
                backgroundColor: ['#e74c3c', '#3498db'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Project Budget Allocation (Main Contractor)',
                fontSize: 14
            },
            plugins: {
                datalabels: {
                    display: true,
                    formatter: function(value, context) {
                        return '$' + value; // Display values as currency
                    }
                }
            }
        }
    });

    // Number of Contractors Assigned Chart (only for Main Contractor)
    var contractorAssignmentCtx = document.getElementById('contractorAssignmentChart').getContext('2d');
    var contractorAssignmentChart = new Chart(contractorAssignmentCtx, {
        type: 'bar',
        data: {
            labels: @json($contractorAssignmentData->pluck('contractor_name')),
            datasets: [{
                label: 'Number of Tasks Assigned',
                data: @json($contractorAssignmentData->pluck('total_tasks')),
                backgroundColor: '#9b59b6',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Number of Contractors Assigned',
                fontSize: 14
            },
            scales: {
                y: {
                    beginAtZero: true,
                    stepSize: 1 // Ensure whole numbers are displayed
                }
            }
        }
    });
    @endif
</script>
@endsection


