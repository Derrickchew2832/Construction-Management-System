<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Project Manager</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="{{ route('project_manager.projects.index') }}">Project Manager Dashboard</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('project_manager.projects.index') }}">Projects</a>
                        </li>
                        <li class="nav-item">
                            @if(isset($project))
                                <a class="nav-link" href="{{ route('project_manager.projects.quotes', ['project' => $project->id]) }}">Quotes</a>
                            @else
                                <a class="nav-link" href="#">Quotes</a> <!-- Or handle differently -->
                            @endif
                        </li>
                    </ul>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('project_manager.profile') }}">Profile</a>
                                <div class="dropdown-divider"></div>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Your custom script -->
    <script>
        // Modal Setup for Project Action (Approve, Reject, Suggest)
        

       // Delete Project with Confirmation
       document.querySelectorAll('.delete-project-btn').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();  // Ensure the default action is stopped

                let projectId = this.getAttribute('data-project-id');

                // Confirm before deleting
                if (confirm('Are you sure you want to delete this project?')) {
                    // Send AJAX request to delete the project
                    fetch(`/contractor/projects/${projectId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Project deleted successfully!');
                            // Remove the project card from the DOM
                            this.closest('.col-md-4').remove();
                        } else {
                            alert('Failed to delete the project.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the project.');
                    });
                }
            });
        });
    </script>
</body>
</html>
