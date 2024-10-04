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
    <link rel="stylesheet" href="{{ asset('css/task-styles.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar collapsed" id="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-center mb-4">
                <h5>{{ $project->name }}</h5>
            </div>
            <ul class="nav flex-column">
                <!-- Common options -->
                <li class="nav-item">
                    <a href="{{ route('tasks.index', ['projectId' => $projectId]) }}" class="nav-link">
                        <i class="fas fa-tasks"></i> <span>Tasks</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-image"></i> <span>Photos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
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

                <!-- Show 'Supply' only to Contractors -->
                @if ($roleName == 'contractor' && !$isMainContractor)
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-box"></i> <span>Supply</span>
                        </a>
                    </li>
                @endif

                @if ($isMainContractor)
                    <li class="nav-item">
                        <a href="{{ route('tasks.quote', ['projectId' => $projectId]) }}" class="nav-link">
                            <i class="fas fa-file-invoice"></i> <span>Quotes</span>
                        </a>
                    </li>
                @endif

                <!-- Invite Button for Project Manager -->
                @if ($roleName == 'project_manager')
                    <li class="nav-item">
                        <!-- Change to a link that navigates to the invite page -->
                        <a href="{{ route('tasks.inviteClientForm', ['projectId' => $projectId]) }}" class="nav-link">
                            <i class="fas fa-user-plus"></i> Invite
                        </a>
                    </li>
                @endif



                <!-- Statistics -->
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
                    <div id="current-date-time" class="mb-1"></div> <!-- Smaller and above the exit button -->
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

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        let isSidebarHovered = false;

        // Set sidebar to be collapsed by default
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');

        sidebar.addEventListener('mouseenter', () => {
            sidebar.classList.add('expanded');
            mainContent.classList.add('expanded');
        });

        sidebar.addEventListener('mouseleave', () => {
            sidebar.classList.remove('expanded');
            mainContent.classList.remove('expanded');
        });

        // Calculate remaining days and set the project status
        const projectStartDate = new Date('{{ $project->start_date }}');
        const projectDueDate = new Date('{{ $project->end_date }}');
        const currentDate = new Date();
        const statusElement = document.getElementById('project-status');

        // Calculate and display project status based on dates
        if (currentDate < projectStartDate) {
            statusElement.innerHTML = 'Project hasn\'t started yet';
        } else if (currentDate >= projectStartDate && currentDate <= projectDueDate) {
            const timeDifference = projectDueDate.getTime() - currentDate.getTime();
            const daysRemaining = Math.ceil(timeDifference / (1000 * 3600 * 24)); // Convert milliseconds to days
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
    </script>
</body>

</html>
