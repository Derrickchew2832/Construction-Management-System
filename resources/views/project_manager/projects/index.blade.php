@extends('layouts.projectmanagerapp')

@section('title', 'Projects')

@section('content')
    <div class="container mt-4">
        <h1>Projects</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('project_manager.projects.create') }}" class="btn btn-primary">+ New Project</a>
            <div class="d-flex align-items-center">
                <!-- Sort and Search Button with proper spacing and size -->
                <button class="btn btn-outline-secondary me-3 w-auto" id="sortButton">Sort A-Z</button>
                <input type="text" class="form-control w-auto" placeholder="Search projects" id="searchInput">
            </div>
        </div>

        <div class="row" id="projectCards">
            @foreach ($projects as $project)
                <div class="col-md-4 mb-4 project-card">
                    <div class="card position-relative same-height">
                        <div class="card-body">
                            <!-- Ribbon based on project status -->
                            @if ($project->status === 'completed')
                                <div class="ribbon bg-success">Completed</div>
                            @elseif ($project->status === 'started')
                                <div class="ribbon bg-warning">In Progress</div>
                            @elseif (!$project->main_contractor)
                                <div class="ribbon bg-primary">Project Created</div>
                            @endif

                            <h5 class="card-title">{{ $project->name }}</h5>
                            <p class="card-text">{{ $project->description }}</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <!-- Display the number of people in the project -->
                                    <span>{{ $project->members_count ?? 0 }}</span>
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <!-- Favorite Button -->
                                    @php
                                        $isFavorite = $project->is_favorite ? 'fas' : 'far';
                                    @endphp
                                    <a href="#" class="btn btn-link favorite-btn" data-project-id="{{ $project->id }}">
                                        <i class="{{ $isFavorite }} fa-star"></i>
                                    </a>
                                    <!-- Settings Dropdown, hidden if project is in progress -->
                                    @if ($project->status !== 'started')
                                        <div class="dropdown">
                                            <button class="btn btn-link dropdown-toggle" type="button"
                                                id="dropdownMenuButton{{ $project->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $project->id }}">
                                                <li><a class="dropdown-item" href="{{ route('project_manager.projects.edit', $project->id) }}">Edit</a></li>
                                                <li>
                                                    <form action="{{ route('project_manager.projects.delete', $project->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item">Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Logic for showing buttons -->
                            @if (!$project->main_contractor)
                                <!-- Invite Contractor and View Project buttons -->
                                <a href="{{ route('project_manager.projects.invite', $project->id) }}" class="btn btn-outline-primary btn-block mb-3" style="font-size: 1.1rem;">
                                    <i class="fas fa-user-plus"></i> Invite Contractor
                                </a>
                                <a href="{{ route('project_manager.projects.show', $project->id) }}" class="btn btn-outline-success btn-block mb-2">
                                    View Project Details
                                </a>
                            @elseif ($project->can_access_management)
                                <!-- Enter Project button once contractor is selected -->
                                <a href="{{ route('project_manager.projects.manage', $project->id) }}" class="btn btn-outline-primary btn-block">
                                    Enter Project
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        /* Ribbon CSS */
        .ribbon {
            position: absolute;
            top: -5px;
            left: -5px;
            padding: 5px 10px;
            font-size: 12px;
            color: white;
            text-transform: uppercase;
            border-radius: 3px;
        }

        .ribbon.bg-warning {
            background-color: #f0ad4e;
        }

        .ribbon.bg-success {
            background-color: #28a745;
        }

        .ribbon.bg-primary {
            background-color: #007bff;
        }

        /* Ensure cards are sorted correctly */
        .same-height {
            height: 100%;
        }

        /* Adjust Sort Button and Search Box size and spacing */
        .me-3 {
            margin-right: 1rem;
        }

        .w-auto {
            width: 150px; /* Matching size for both search and sort buttons */
        }
    </style>

    <script>
        // Sorting functionality
        let isAscending = true; // Track the sorting order
        document.getElementById('sortButton').addEventListener('click', function() {
            let projectCards = Array.from(document.querySelectorAll('.project-card'));
            projectCards.sort(function(a, b) {
                let titleA = a.querySelector('.card-title').textContent.trim().toLowerCase();
                let titleB = b.querySelector('.card-title').textContent.trim().toLowerCase();
                return isAscending ? titleA.localeCompare(titleB) : titleB.localeCompare(titleA);
            });

            isAscending = !isAscending; // Toggle the sorting order for next click
            let sortButtonText = isAscending ? 'Sort A-Z' : 'Sort Z-A'; // Change button text based on order
            document.getElementById('sortButton').textContent = sortButtonText;

            let projectContainer = document.getElementById('projectCards');
            projectContainer.innerHTML = '';
            projectCards.forEach(function(card) {
                projectContainer.appendChild(card);
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            let searchValue = this.value.toLowerCase();
            let projectCards = document.querySelectorAll('.project-card');

            projectCards.forEach(function(card) {
                let projectName = card.querySelector('.card-title').textContent.toLowerCase();
                let projectDescription = card.querySelector('.card-text').textContent.toLowerCase();
                if (projectName.includes(searchValue) || projectDescription.includes(searchValue)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Toggle favorite status
        document.querySelectorAll('.favorite-btn').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                let icon = this.querySelector('i');
                let projectId = this.getAttribute('data-project-id');
                let isFavorite = icon.classList.contains('fas');

                // Toggle the favorite state visually
                icon.classList.toggle('fas');
                icon.classList.toggle('far');

                // Send AJAX request to toggle favorite state
                fetch(`/project_manager/projects/${projectId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        is_favorite: !isFavorite
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Favorite status updated:', data);
                    if (data.is_favorite) {
                        alert('Project added to favorites!');
                    } else {
                        alert('Project removed from favorites!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the favorite status.');
                });
            });
        });

        // Bootstrap dropdown fix
        document.querySelectorAll('.dropdown-toggle').forEach(function(dropdown) {
            dropdown.addEventListener('click', function(event) {
                event.preventDefault();
                let menu = this.nextElementSibling;
                menu.classList.toggle('show');
            });
        });
    </script>
@endsection
