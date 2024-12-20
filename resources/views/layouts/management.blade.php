<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Management Board')</title>

    <!-- Include Bootstrap CSS and Font Awesome -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/task-styles.css') }}">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS applied directly in the layout -->
    <style>
        /* Sidebar Styling */
        .sidebar {
            width: 60px;
            background-color: #2c3e50;
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 20px;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
        }

        .sidebar.expanded {
            width: 200px;
        }

        .sidebar .nav-link {
            color: #ffffff;
            font-size: 1rem;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease-in-out;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px 10px;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.expanded .nav-link span {
            display: inline;
        }

        .sidebar .nav-link i {
            margin-right: 15px;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .sidebar .nav-link:hover {
            background-color: #34495e;
        }

        .sidebar .sidebar-header h5 {
            color: #fff;
            font-size: 18px;
            margin-bottom: 20px;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header h5 {
            opacity: 0;
        }

        .sidebar.expanded .sidebar-header h5 {
            opacity: 1;
        }

        .sidebar .active {
            background-color: #2980b9;
            color: #ffffff;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 60px;
            padding: 20px;
            background-color: #ecf0f1;
            min-height: 100vh;
            width: calc(100% - 60px);
            box-sizing: border-box;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 200px;
            width: calc(100% - 200px);
        }

        /* Standardized Button Styles */
        .btn-danger {
            font-size: 12px;
            padding: 5px 15px;
        }

        /* Project Status Styling */
        #project-status {
            font-weight: bold;
        }

        /* Scrollbar for Sidebar */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: #6c757d;
            border-radius: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background-color: #2c3e50;
        }

        /* Statistics Box Styling */
        .stats-box {
            background-color: #34495e;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            color: #fff;
        }

        .stats-box h6 {
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }

        .stats-box p {
            font-size: 14px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        .stats-box i {
            margin-right: 5px;
        }

        /* Button Panel Styling */
        .button-panel {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }

        .button-panel .btn {
            margin-right: 10px;
        }

        /* Current Date Styling */
        #current-date-time {
            font-size: 12px;
            /* Smaller size for current date and time */
        }
    </style>
</head>

<body>
    @stack('scripts')
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar collapsed" id="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-center mb-4">
                <h5>{{ $project->name }}</h5>
            </div>
            <ul class="nav flex-column">
                <!-- Common options for all roles -->
                <li class="nav-item">
                    <a href="{{ route('tasks.index', ['projectId' => $projectId]) }}" class="nav-link">
                        <i class="fas fa-tasks"></i> <span>Tasks</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('tasks.photos.view', ['projectId' => $projectId]) }}" class="nav-link">
                        <i class="fas fa-image"></i> <span>Photos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('tasks.files.view', ['projectId' => $projectId]) }}" class="nav-link">
                        <i class="fas fa-file-alt"></i> <span>Files</span>
                    </a>
                </li>
                

                <!-- Role-specific options -->
                @php
                    $roleName = strtolower(auth()->user()->role->name);
                    $isMainContractor = DB::table('project_contractor')
                        ->where('project_id', $project->id)
                        ->where('contractor_id', auth()->user()->id)
                        ->where('main_contractor', 1)
                        ->exists();
                @endphp

                <!-- Contractor-specific options -->
                @if ($roleName == 'contractor' && !$isMainContractor)
                    <li class="nav-item">
                        <a href="{{ route('tasks.supply_order', ['projectId' => $projectId]) }}" class="nav-link">
                            <i class="fas fa-box"></i> <span>Supply</span>
                        </a>
                    </li>
                @endif

                <!-- Main Contractor options -->
                @if ($isMainContractor)
                    <li class="nav-item">
                        <a href="{{ route('tasks.quote', ['projectId' => $projectId]) }}" class="nav-link">
                            <i class="fas fa-file-invoice"></i> <span>Quotes</span>
                        </a>
                    </li>
                @endif

                <!-- Project Manager-specific options -->
                @if ($roleName == 'project_manager')
                    <li class="nav-item">
                        <a href="{{ route('tasks.inviteClientForm', ['projectId' => $projectId]) }}" class="nav-link">
                            <i class="fas fa-user-plus"></i> <span>Invite</span>
                        </a>
                    </li>
                @endif

                <!-- Common option for viewing statistics (available to all roles) -->
                <li class="nav-item">
                    <a href="{{ route('tasks.statistics', ['projectId' => $projectId]) }}" class="nav-link">
                        <i class="fas fa-chart-bar"></i> <span>Statistics</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content collapsed" id="mainContent">
            <header class="d-flex justify-content-between align-items-center mb-3">
                <!-- Left side: Project details -->
                <div>
                    <h4 class="text-primary mb-0">{{ $project->name }}</h4>
                    <small>
                        Managed by: {{ $projectManagerName }} | Main Contractor: {{ $mainContractorName }} |
                        Project Start Date: {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }} |
                        Due Date: {{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }} |
                        <span id="project-status"></span> |
                        Total Project Days: <span id="total-project-days">{{ $totalProjectDays }}</span>
                    </small>
                </div>

                <!-- Right side: Exit button -->
                <div class="d-flex align-items-center flex-column">
                    <div id="current-date-time" class="mb-1"></div>
                    @php
                        $exiturl = '';
                        if ($roleName == 'project_manager') {
                            $exiturl = route('project_manager.projects.manage', ['projectId' => $project->id]);
                        } elseif ($roleName == 'contractor') {
                            $exiturl = route('contractor.projects.index');
                        } elseif ($roleName == 'client') {
                            $exiturl = route('client.projects.dashboard');
                        }
                    @endphp
                    <button class="btn btn-danger btn-sm" onclick="window.location.href='{{ $exiturl }}'">Exit</button>
                    @if ($isMainContractor && $project->status !== 'completed')
                        <!-- Project Ended button only for Main Contractor -->
                        <button class="btn btn-warning btn-sm mt-2" id="endProjectBtn" data-toggle="modal" data-target="#endProjectModal">
                            Project Ended
                        </button>
                        
                    @endif        
                </div>
            </header>

            <!-- Yield content from specific views -->
            @yield('content')
        </div>
    </div>

    <!-- Confirmation Modal for Ending Project -->
    <div class="modal fade" id="endProjectModal" tabindex="-1" role="dialog" aria-labelledby="endProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="endProjectModalLabel">Confirm End Project</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to end the project? This action cannot be undone, and no further changes can be made by any users.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmEndProjectBtn">Yes, End Project</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Custom Error Message Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel">Error</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="errorModalMessage">
                <!-- Error message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Sidebar expansion and collapse functionality
        const mySidebarElement = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        mySidebarElement.classList.add('collapsed');
        mainContent.classList.add('collapsed');

        mySidebarElement.addEventListener('mouseenter', () => {
            mySidebarElement.classList.add('expanded');
            mainContent.classList.add('expanded');
        });

        mySidebarElement.addEventListener('mouseleave', () => {
            mySidebarElement.classList.remove('expanded');
            mainContent.classList.remove('expanded');
        });

        // Calculate remaining days and set the project status
        const projectStartDate = new Date('{{ $project->start_date }}');
        const projectDueDate = new Date('{{ $project->end_date }}');
        const currentDate = new Date();
        const statusElement = document.getElementById('project-status');

        if (currentDate < projectStartDate) {
            statusElement.innerHTML = 'Project hasn\'t started yet';
        } else if (currentDate >= projectStartDate && currentDate <= projectDueDate) {
            const timeDifference = projectDueDate.getTime() - currentDate.getTime();
            const daysRemaining = Math.ceil(timeDifference / (1000 * 3600 * 24));
            statusElement.innerHTML = daysRemaining + ' days remaining';
        } else {
            statusElement.innerHTML = 'Project has already ended';
        }

        // Display current date and time
        function updateDateTime() {
            const currentDate = new Date();
            const formattedDate = currentDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
            const formattedTime = currentDate.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
            document.getElementById('current-date-time').innerHTML =
                `Current Date: ${formattedDate} | Time: ${formattedTime}`;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Event listener for confirming project end
        $('#confirmEndProjectBtn').on('click', function() {
            const projectId = '{{ $project->id }}';

            $.ajax({
                url: '/projects/' + projectId + '/end',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#endProjectModal').modal('hide');
                        showCustomErrorModal('Project has been marked as completed.');
                        location.reload();
                    } else {
                        showCustomErrorModal(response.message || 'An error occurred while ending the project.');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    if (response && response.message) {
                        showCustomErrorModal(response.message);
                    } else {
                        showCustomErrorModal('An unexpected error occurred. Please try again.');
                    }
                }
            });
        });

        // Function to display error messages in the custom modal
        function showCustomErrorModal(message) {
            $('#errorModalMessage').text(message); // Set the error message
            $('#errorModal').modal('show'); // Show the modal
        }
    });
</script>
<!-- Custom Error Message Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel">Error</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="errorModalMessage">
                <!-- Error message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Sidebar expansion and collapse functionality
        const mySidebarElement = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        mySidebarElement.classList.add('collapsed');
        mainContent.classList.add('collapsed');

        mySidebarElement.addEventListener('mouseenter', () => {
            mySidebarElement.classList.add('expanded');
            mainContent.classList.add('expanded');
        });

        mySidebarElement.addEventListener('mouseleave', () => {
            mySidebarElement.classList.remove('expanded');
            mainContent.classList.remove('expanded');
        });

        // Calculate remaining days and set the project status
        const projectStartDate = new Date('{{ $project->start_date }}');
        const projectDueDate = new Date('{{ $project->end_date }}');
        const currentDate = new Date();
        const statusElement = document.getElementById('project-status');

        if (currentDate < projectStartDate) {
            statusElement.innerHTML = 'Project hasn\'t started yet';
        } else if (currentDate >= projectStartDate && currentDate <= projectDueDate) {
            const timeDifference = projectDueDate.getTime() - currentDate.getTime();
            const daysRemaining = Math.ceil(timeDifference / (1000 * 3600 * 24));
            statusElement.innerHTML = daysRemaining + ' days remaining';
        } else {
            statusElement.innerHTML = 'Project has already ended';
        }

        // Display current date and time
        function updateDateTime() {
            const currentDate = new Date();
            const formattedDate = currentDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
            const formattedTime = currentDate.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
            document.getElementById('current-date-time').innerHTML =
                `Current Date: ${formattedDate} | Time: ${formattedTime}`;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Event listener for confirming project end
        $('#confirmEndProjectBtn').on('click', function() {
            const projectId = '{{ $project->id }}';

            $.ajax({
                url: '/projects/' + projectId + '/end',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#endProjectModal').modal('hide');
                        showCustomErrorModal('Project has been marked as completed.');
                        location.reload();
                    } else {
                        showCustomErrorModal(response.message || 'An error occurred while ending the project.');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    if (response && response.message) {
                        showCustomErrorModal(response.message);
                    } else {
                        showCustomErrorModal('An unexpected error occurred. Please try again.');
                    }
                }
            });
        });

        // Function to display error messages in the custom modal
        function showCustomErrorModal(message) {
            $('#errorModalMessage').text(message); // Set the error message
            $('#errorModal').modal('show'); // Show the modal
        }
    });
</script>

    

</body>

</html>
