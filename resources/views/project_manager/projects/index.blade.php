@extends('layouts.projectmanagerapp')

@section('title', 'Projects')

@section('content')
    <div class="container mt-4">
        <h1>Projects</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('project_manager.projects.create') }}" class="btn btn-primary">+ New Project</a>
            <div>
                <button class="btn btn-outline-secondary" id="sortButton">Sort A-Z</button>
                <input type="text" class="form-control d-inline-block w-auto" placeholder="Search projects" id="searchInput">
            </div>
        </div>

        <div class="row" id="projectCards">
            @foreach ($projects as $project)
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $project->name }}</h5>
                            <p class="card-text">{{ $project->description }}</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <!-- Display the number of people in the project -->
                                    <span>{{ $project->members_count }}</span>
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <!-- Favorite Button -->
                                    @php
                                        $isFavorite = $project->is_favorite ? 'fas' : 'far';
                                    @endphp
                                    <a href="#" class="btn btn-link favorite-btn"
                                        data-project-id="{{ $project->id }}">
                                        <i class="{{ $isFavorite }} fa-star"></i>
                                    </a>
                                    <!-- Settings Dropdown -->
                                    <div class="dropdown">
                                        <button class="btn btn-link dropdown-toggle" type="button"
                                            id="dropdownMenuButton{{ $project->id }}" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $project->id }}">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('project_manager.projects.edit', $project->id) }}">Edit</a>
                                            </li>
                                            <li>
                                                <form action="{{ route('project_manager.projects.delete', $project->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item">Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Invite Contractor Button -->
                            @if (!$project->contractors->contains('main_contractor', true))
                                <a href="{{ route('project_manager.projects.invite', $project->id) }}" class="btn btn-outline-primary btn-block mb-3" style="font-size: 1.1rem;">
                                    <i class="fas fa-user-plus"></i> Invite Contractor
                                </a>
                            @endif

                            <!-- View Project Details Button -->
                            <a href="{{ route('project_manager.projects.show', $project->id) }}" class="btn btn-outline-success btn-block">
                                View Project Details
                            </a>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.getElementById('sortButton').addEventListener('click', function() {
            let projectCards = Array.from(document.querySelectorAll('#projectCards .col-md-4'));
            projectCards.sort(function(a, b) {
                return a.querySelector('.card-title').textContent.trim().localeCompare(
                    b.querySelector('.card-title').textContent.trim()
                );
            });

            let projectContainer = document.getElementById('projectCards');
            projectContainer.innerHTML = '';
            projectCards.forEach(function(card) {
                projectContainer.appendChild(card);
            });
        });

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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
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
                        // Display success message
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
