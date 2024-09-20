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

    <!-- Custom CSS for the management board -->
    <link rel="stylesheet" href="{{ asset('css/management.css') }}">
</head>
<body style="background-color: #f5f6fa;">
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar bg-dark text-light p-3 vh-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5>{{ $project->name }} <i class="fas fa-caret-down"></i></h5>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-light" style="font-size: 0.9rem;">Tasks</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-light" style="font-size: 0.9rem;">Photos</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-light" style="font-size: 0.9rem;">Files</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-light" style="font-size: 0.9rem;">Supply</a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <header class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newTaskModal">+ New Task</button>
                </div>
                <div class="d-flex align-items-center">
                    @php
                        $roleName = strtolower(auth()->user()->role->name);
                        if($roleName == 'project_manager'){
                            $exiturl = route('project_manager.projects.manage', ['projectId' => $project->id]);
                        }else if($roleName == 'contractor'){
                            $exiturl = route('contractor.projects.index');
                        }

                    @endphp
                    <!-- Updated Exit button to redirect to the management page -->
                    <button class="btn btn-danger btn-sm mr-2" onclick="window.location.href='{{ $exiturl }}'">Exit</button>

                </div>
            </header>

            <!-- Yield content from specific views -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom JavaScript for Drag-and-Drop functionality -->
    <script>
        const taskCards = document.querySelectorAll('.task-card');
        const taskLists = document.querySelectorAll('.task-list');

        taskCards.forEach(card => {
            card.addEventListener('dragstart', dragStart);
            card.addEventListener('dragend', dragEnd);
        });

        taskLists.forEach(list => {
            list.addEventListener('dragover', dragOver);
            list.addEventListener('drop', dragDrop);
        });

        let draggedTask = null;

        function dragStart() {
            draggedTask = this;
            setTimeout(() => (this.style.display = 'none'), 0);
        }

        function dragEnd() {
            setTimeout(() => (this.style.display = 'block'), 0);
            draggedTask = null;
        }

        function dragOver(e) {
            e.preventDefault();
        }

        function dragDrop() {
            this.appendChild(draggedTask);
        }
    </script>
</body>
</html>
