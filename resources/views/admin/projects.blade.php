@extends('layouts.adminapp')

@section('title', 'Project Details')

@section('content')
<div class="container mt-4">
    <h3 class="text-start text-primary">Project Details</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive mt-3">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Project Name</th>
                    <th>Description</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Budget</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                    <tr>
                        <td>{{ $project->name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($project->description, 50) }}</td>
                        <td>{{ $project->start_date }}</td>
                        <td>{{ $project->end_date }}</td>
                        <td>${{ number_format($project->total_budget, 2) }}</td>
                        <td>
                            <button onclick="openEditModal({{ $project->id }})" class="btn btn-primary btn-sm me-2">Edit</button>
                            <button onclick="confirmDelete({{ $project->id }})" class="btn btn-danger btn-sm">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
                <button type="button" class="btn-close" onclick="closeEditModal()" aria-label="Close"></button>
            </div>
            <form id="editProjectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="projectName" class="form-label">Project Name</label>
                        <input type="text" name="name" id="projectName" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="projectDescription" class="form-label">Description</label>
                        <textarea name="description" id="projectDescription" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="projectStartDate" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="projectStartDate" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="projectEndDate" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="projectEndDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="projectBudget" class="form-label">Total Budget ($)</label>
                        <input type="number" step="0.01" name="total_budget" id="projectBudget" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Styles for modal and table -->
<style>
    .table {
        border-radius: 8px;
        overflow: hidden;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .text-primary {
        color: #007bff !important;
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }
    .modal.show {
        display: flex;
    }
</style>

<!-- JavaScript for opening, closing modal, and delete confirmation -->
<script>
    function openEditModal(projectId) {
        fetch(`/admin/projects/${projectId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('projectName').value = data.name;
                document.getElementById('projectDescription').value = data.description;
                document.getElementById('projectStartDate').value = data.start_date;
                document.getElementById('projectEndDate').value = data.end_date;
                document.getElementById('projectBudget').value = data.total_budget;

                document.getElementById('editProjectForm').action = `/admin/projects/${projectId}`;
                document.getElementById('editProjectModal').classList.add('show');
            });
    }

    function closeEditModal() {
        document.getElementById('editProjectModal').classList.remove('show');
    }

    function confirmDelete(projectId) {
        if (confirm('Are you sure you want to delete this project?')) {
            fetch(`/admin/projects/${projectId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    location.reload();
                } else {
                    alert(data.error || 'Failed to delete the project.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete the project.');
            });
        }
    }
</script>
@endsection
