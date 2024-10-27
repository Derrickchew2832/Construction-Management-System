@extends('layouts.clientapp')

@section('title', 'Projects')

@section('content')
    <div class="container mt-4">
        <h2 class="text-primary mb-4" style="font-weight: bold;">Projects</h2> <!-- Enhanced title styling -->

        <div class="d-flex justify-content-end align-items-center mb-3">
            <!-- Sort and Search Input moved to the right -->
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary me-3 w-auto" id="sortButton">Sort A-Z</button>
                <input type="text" class="form-control w-auto search-bar" placeholder="Search projects" id="searchInput" style="max-width: 200px;">
            </div>
        </div>

        <div class="row" id="projectCards">
            @foreach ($projects as $project)
                <div class="col-md-4 mb-4 project-card">
                    <div class="card shadow-sm position-relative same-height border-0">
                        <div class="card-body">
                            <!-- Ribbon based on project status -->
                            @php
                                $projectStatus = strtolower($project->status);
                                $ribbonClass = ($projectStatus === 'completed') ? 'bg-success' : ($projectStatus === 'in progress' ? 'bg-warning' : 'bg-secondary');
                            @endphp
                            <div class="ribbon {{ $ribbonClass }}">{{ ucfirst($projectStatus) }}</div>

                            <h5 class="card-title text-primary">{{ $project->name }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($project->description, 100) }}</p> <!-- Limiting description length -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <!-- Favorite Button -->
                                    @php
                                        $isFavorite = $project->is_favorite ? 'fas' : 'far';
                                    @endphp
                                    <a href="#" class="btn btn-link text-warning favorite-btn" data-project-id="{{ $project->id }}">
                                        <i class="{{ $isFavorite }} fa-star"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Enter Project Button -->
                            <a href="{{ route('tasks.index', $project->id) }}" class="btn btn-outline-primary btn-block">
                                Enter Project
                            </a>

                            @if ($project->status === 'completed')
                                <div class="alert alert-info mt-2" role="alert">
                                    This project is completed. All actions are disabled.
                                </div>
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

        .ribbon.bg-secondary {
            background-color: #6c757d;
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
            width: 150px;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-link {
            font-size: 1.2rem;
        }
    </style>

    <script>
        // Sorting functionality
        let isAscending = true;
        document.getElementById('sortButton').addEventListener('click', function () {
            let projectCards = Array.from(document.querySelectorAll('.project-card'));
            projectCards.sort(function (a, b) {
                let titleA = a.querySelector('.card-title').textContent.trim().toLowerCase();
                let titleB = b.querySelector('.card-title').textContent.trim().toLowerCase();
                return isAscending ? titleA.localeCompare(titleB) : titleB.localeCompare(titleA);
            });

            isAscending = !isAscending;
            let sortButtonText = isAscending ? 'Sort A-Z' : 'Sort Z-A';
            document.getElementById('sortButton').textContent = sortButtonText;

            let projectContainer = document.getElementById('projectCards');
            projectContainer.innerHTML = '';
            projectCards.forEach(function (card) {
                projectContainer.appendChild(card);
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function () {
            let searchValue = this.value.toLowerCase();
            let projectCards = document.querySelectorAll('.project-card');

            projectCards.forEach(function (card) {
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
        document.querySelectorAll('.favorite-btn').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                let icon = this.querySelector('i');
                let projectId = this.getAttribute('data-project-id');
                let isFavorite = icon.classList.contains('fas');

                icon.classList.toggle('fas');
                icon.classList.toggle('far');

                fetch(`/client/projects/${projectId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"').getAttribute('content')
                    },
                    body: JSON.stringify({
                        is_favorite: !isFavorite
                    })
                }).then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                }).then(data => {
                    if (data.is_favorite) {
                        alert('Project added to favorites!');
                    } else {
                        alert('Project removed from favorites!');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the favorite status.');
                });
            });
        });
    </script>
@endsection
