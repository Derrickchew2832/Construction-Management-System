<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Management Board')</title>

    <!-- Include Bootstrap CSS and Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Custom CSS applied directly in the layout -->
    <style>
        /* Sidebar Styling */
        .sidebar {
            width: 270px;
            background-color: #343a40;
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 20px;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
        }

        .sidebar .sidebar-header h5 {
            color: #fff;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .sidebar .active {
            background-color: #007bff;
            color: #ffffff;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 270px; /* Align with the sidebar width */
            padding: 20px;
            background-color: #f5f6fa;
            min-height: 100vh;
            width: calc(100% - 270px); /* Ensure it fills the remaining space */
            box-sizing: border-box; /* Ensure padding does not overflow */
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
            background-color: #343a40;
        }

        /* Statistics Box Styling */
        .stats-box {
            background-color: #495057;
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
    </style>
</head>

<body>

    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-center mb-4">
                <h5>{{ $project->name }} <i class="fas fa-caret-down"></i></h5>
            </div>
            <ul class="nav flex-column">
                <!-- Common options -->
                <li class="nav-item">
                    <a href="{{ route('tasks.index', ['projectId' => $projectId]) }}" class="nav-link">
                        <i class="fas fa-tasks"></i> Tasks
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-image"></i> Photos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-file-alt"></i> Files
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

                <!-- Show 'Supply' only to Contractors -->
                @if ($roleName == 'contractor' && !$isMainContractor)
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-box"></i> Supply
                        </a>
                    </li>
                @endif

                @if ($isMainContractor)
                    <li class="nav-item">
                        <a href="{{ route('tasks.quote', ['projectId' => $projectId]) }}" class="nav-link">
                            <i class="fas fa-file-invoice"></i> Quotes
                        </a>
                    </li>
                @endif

                <!-- Invite Button for Project Manager -->
                @if ($roleName == 'project_manager')
                    <li class="nav-item">
                        <a href="{{ route('tasks.invite', ['projectId' => $projectId]) }}" class="btn btn-primary mt-4">
                            <i class="fas fa-user-plus"></i> Invite
                        </a>
                    </li>
                @endif

                <!-- Statistics -->
                <li class="nav-item">
                    <a href="{{ route('tasks.statistics', ['projectId' => $projectId]) }}" class="nav-link">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="d-flex justify-content-between align-items-center mb-3">
                <!-- Left side: Project details -->
                <div>
                    <h4 class="text-muted mb-0">{{ $project->name }}</h4>
                    <small>Managed by: {{ $projectManagerName }} | Main Contractor: {{ $mainContractorName }}</small>
                    <small> |
                        Project Status:
                        <span id="project-status"></span>
                    </small>
                </div>

                <!-- Right side: Exit button -->
                <div class="d-flex align-items-center">
                    @php
                        $exiturl = '';
                        if ($roleName == 'project_manager') {
                            $exiturl = route('project_manager.projects.manage', ['projectId' => $project->id]);
                        } elseif ($roleName == 'contractor') {
                            $exiturl = route('contractor.projects.index');
                        } elseif ($roleName == 'client') {
                            $exiturl = route('client.projects.index');
                        }
                    @endphp
                    <button class="btn btn-danger btn-sm"
                        onclick="window.location.href='{{ $exiturl }}'">Exit</button>
                </div>
            </header>

          

            <!-- Yield content from specific views -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom JavaScript for Countdown Functionality -->
    <script>
        // Get project start date and due date from the server (these values would be dynamic in production)
        const projectStartDate = new Date('{{ $project->start_date }}'); // Project start date
        const projectDueDate = new Date('{{ $project->end_date }}'); // Project due date
        const currentDate = new Date(); // Current date
        const statusElement = document.getElementById('project-status'); // The element to show status

        // Check if the project hasn't started yet
        if (currentDate < projectStartDate) {
            statusElement.innerHTML = 'Project hasn\'t started yet';
        }
        // Check if the project is in progress
        else if (currentDate >= projectStartDate && currentDate <= projectDueDate) {
            const timeDifference = projectDueDate.getTime() - currentDate.getTime();
            const daysRemaining = Math.ceil(timeDifference / (1000 * 3600 * 24)); // Convert milliseconds to days

            statusElement.innerHTML = daysRemaining + ' days remaining';
        }
        // Check if the project has ended
        else if (currentDate > projectDueDate) {
            statusElement.innerHTML = 'Project has already ended';
        }
    </script>
</body>

</html>
