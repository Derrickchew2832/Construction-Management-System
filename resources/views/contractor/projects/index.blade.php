@extends('layouts.contractorapp')

@section('title', 'Projects')

@section('content')
    <div class="container mt-4">
        <!-- Smaller Title for Project Page -->
        <h2 class="text-primary font-weight-bold mb-4">Projects</h2> <!-- Enhanced styling for the title -->

        <div class="d-flex justify-content-end align-items-center mb-3">
            <!-- Sort and Search Input -->
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary me-3 sort-btn" id="sortButton">Sort A-Z</button>
                <input type="text" class="form-control search-bar" placeholder="Search projects" id="searchInput">
            </div>
        </div>

        <!-- Project Cards Section -->
        <div class="row" id="projectCards">
            @foreach ($projects as $project)
                @if ($project->can_access_management)
                    <!-- Only show the project if contractor is main -->
                    <div class="col-md-4 mb-4 project-card" data-is-favorite="{{ $project->is_favorite ? '1' : '0' }}"
                        data-title="{{ strtolower($project->name) }}">
                        <div class="card position-relative h-100 shadow-sm border-0">
                            <!-- Added shadow for better appearance -->
                            <!-- Ribbon based on project status -->
                            @if ($project->ribbon === 'Completed')
                                <div class="ribbon bg-success">Completed</div>
                            @elseif ($project->ribbon === 'In Progress')
                                <div class="ribbon bg-warning">In Progress</div>
                            @endif

                            <div class="card-body">
                                <!-- Adjusted Font Size for Project Title -->
                                <h5 class="card-title project-title text-dark font-weight-bold">{{ $project->name }}</h5>
                                <p class="card-text text-muted">{{ Str::limit($project->description, 100) }}</p>
                                <!-- Limited description length -->

                                <!-- Favorite Button and Project Management -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <!-- Favorite Button -->
                                        @php
                                            $isFavorite = $project->is_favorite ? 'fas' : 'far';
                                        @endphp
                                        <a href="#" class="btn btn-link text-warning favorite-btn"
                                            data-project-id="{{ $project->id }}">
                                            <i class="{{ $isFavorite }} fa-star"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Enter Project Button -->
                                <a href="{{ route('tasks.index', $project->id) }}"
                                    class="btn btn-outline-primary btn-block">
                                    Enter Project
                                </a>

                                <!-- Completed Project Message -->
                                @if ($project->ribbon === 'Completed')
                                    <div class="alert alert-info mt-3" role="alert">
                                        This project is completed. All actions are disabled.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Additional Styling for Better UI -->
    <style>
        /* Ribbon Styling */
        .ribbon {
            position: absolute;
            top: -5px;
            left: -5px;
            padding: 5px 10px;
            font-size: 12px;
            color: white;
            text-transform: uppercase;
            border-radius: 3px;
            z-index: 10;
        }

        .ribbon.bg-warning {
            background-color: #f0ad4e;
        }

        .ribbon.bg-success {
            background-color: #28a745;
        }

        /* Project Card Title Styling */
        .project-title {
            font-size: 1.1rem;
        }

        /* Sort Button and Search Bar Styling */
        .me-3 {
            margin-right: 1rem;
        }

        .sort-btn {
            height: 38px;
            width: auto;
            white-space: nowrap;
        }

        .search-bar {
            height: 38px;
            min-width: 200px;
        }

        /* Card Styling */
        .card {
            transition: box-shadow 0.3s;
        }

        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 1.5rem;
        }
    </style>

    <!-- Sort and Search Scripts -->
    <script>
        let isAscending = true;

        // Sort Functionality with Favorite Projects Sticking to Top
        document.getElementById('sortButton').addEventListener('click', function() {
            let projectCards = Array.from(document.querySelectorAll('#projectCards .project-card'));

            projectCards.sort(function(a, b) {
                let aIsFavorite = a.getAttribute('data-is-favorite') ===
                '1'; // Check if project is favorite
                let bIsFavorite = b.getAttribute('data-is-favorite') === '1';

                if (aIsFavorite && !bIsFavorite) return -1; // Favorites stay on top
                if (!aIsFavorite && bIsFavorite) return 1;

                // Sort alphabetically depending on the current sort order (isAscending)
                let aTitle = a.getAttribute('data-title');
                let bTitle = b.getAttribute('data-title');
                let comparison = aTitle.localeCompare(bTitle);

                return isAscending ? comparison : -comparison; // Reverse the order if not ascending
            });

            isAscending = !isAscending; // Toggle the sorting order
            document.getElementById('sortButton').textContent = isAscending ? 'Sort A-Z' : 'Sort Z-A';

            let projectContainer = document.getElementById('projectCards');
            projectContainer.innerHTML = '';
            projectCards.forEach(function(card) {
                projectContainer.appendChild(card);
            });
        });

        // Search Functionality with Favorite Projects Sticking to Top
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let searchQuery = this.value.toLowerCase();
            let projectCards = document.querySelectorAll('#projectCards .project-card');

            projectCards.forEach(function(card) {
                let title = card.getAttribute('data-title');
                let isFavorite = card.getAttribute('data-is-favorite') === '1';

                // Always display favorite projects and filter non-favorite projects based on search
                if (isFavorite || title.includes(searchQuery)) {
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
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
                            icon.classList.add('fas'); // Filled star for favorite
                            icon.classList.remove('far'); // Remove outline star
                            alert('Project added to favorites!');
                        } else {
                            icon.classList.add('far'); // Outline star for non-favorite
                            icon.classList.remove('fas'); // Remove filled star
                            alert('Project removed from favorites!');
                        }

                        // Ensure favorites stay on top after toggling
                        document.getElementById('sortButton').click();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the favorite status.');
                    });
            });
        });
    </script>
@endsection
