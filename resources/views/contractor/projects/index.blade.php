@extends('layouts.contractorapp')

@section('title', 'Projects')

@section('content')
    <div class="container mt-4">
        <h1>Projects</h1>
        <div class="d-flex justify-content-end align-items-center mb-3">
            <!-- Sort and Search Input -->
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary me-3 sort-btn" id="sortButton">Sort A-Z</button>
                <input type="text" class="form-control search-bar" placeholder="Search projects" id="searchInput">
            </div>
        </div>

        <div class="row" id="projectCards">
            @foreach ($projects as $project)
                <div class="col-md-4 mb-4">
                    <div class="card position-relative h-100">
                        <div class="card-body">
                            <!-- Ribbon based on project status -->
                            @if ($project->ribbon === 'Completed')
                                <div class="ribbon bg-success">Completed</div>
                            @elseif ($project->ribbon === 'In Progress')
                                <div class="ribbon bg-warning">In Progress</div>
                            @elseif ($project->ribbon === 'Declined')
                                <div class="ribbon bg-danger">Declined</div>
                            @elseif ($project->ribbon === 'Quote Submitted')
                                <div class="ribbon bg-info">Quote Submitted</div>
                            @else
                                <div class="ribbon bg-primary">Quote Required</div>
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
                                    <a href="#" class="btn btn-link favorite-btn"
                                        data-project-id="{{ $project->id }}">
                                        <i class="{{ $isFavorite }} fa-star"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- View Project Details Button -->
                            <a href="{{ route('contractor.projects.show', $project->id) }}"
                                class="btn btn-outline-success btn-block mb-2">View Project Details</a>

                            <!-- Enter Project Button (Only when project is started and contractor is main) -->
                            @if ($project->can_access_management)
                                <a href="{{ route('tasks.index', $project->id) }}"
                                    class="btn btn-outline-primary btn-block">
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
            background-color: #f0ad4e; /* Orange color for In Progress */
        }

        .ribbon.bg-danger {
            background-color: #dc3545; /* Red for Declined */
        }

        .ribbon.bg-info {
            background-color: #17a2b8; /* Light blue for Quote Submitted */
        }

        .ribbon.bg-success {
            background-color: #28a745; /* Green for Completed */
        }

        .ribbon.bg-primary {
            background-color: #007bff; /* Blue for Quote Required */
        }

        /* Sort Button and Search Input Alignment */
        .me-3 {
            margin-right: 1rem; /* Ensure a gap between Sort and Search */
        }

        .sort-btn {
            height: 38px; /* Match search input height */
            width: auto; /* Automatically adjust to fit text */
            white-space: nowrap; /* Ensure the text fits in one row */
        }

        .search-bar {
            height: 38px; /* Adjust the height to match sort button */
            min-width: 200px; /* Ensure the search bar has a minimum width */
        }

        .d-flex {
            display: flex;
        }
    </style>

    <script>
        // Sort Functionality
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

        // Search Functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let searchQuery = this.value.toLowerCase();
            let projectCards = document.querySelectorAll('#projectCards .col-md-4');

            projectCards.forEach(function(card) {
                let title = card.querySelector('.card-title').textContent.toLowerCase();
                let description = card.querySelector('.card-text').textContent.toLowerCase();
                if (title.includes(searchQuery) || description.includes(searchQuery)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Toggle Favorite
        document.querySelectorAll('.favorite-btn').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                let icon = this.querySelector('i');
                let projectId = this.getAttribute('data-project-id');
                let isFavorite = icon.classList.contains('fas'); // Determine current favorite state

                // Send AJAX request to toggle favorite state
                fetch(`/contractor/projects/${projectId}/favorite`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            is_favorite: !isFavorite // Send the opposite state
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Update the star icon based on the response from the server
                        if (data.is_favorite) {
                            icon.classList.add('fas');  // Filled star for favorite
                            icon.classList.remove('far'); // Remove outline star
                            alert('Project added to favorites!');
                        } else {
                            icon.classList.add('far'); // Outline star for non-favorite
                            icon.classList.remove('fas'); // Remove filled star
                            alert('Project removed from favorites!');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the favorite status.');
                    });
            });
        });
    </script>
@endsection
