<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index($projectId)
    {
        // Fetch the project by its ID using DB facade
        $project = DB::table('projects')->where('id', $projectId)->first();

        // Check if the project exists
        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }

        // Fetch the project manager and main contractor
        $projectManager = DB::table('users')->where('id', $project->project_manager_id)->first();
        $mainContractor = DB::table('users')->where('id', $project->main_contractor_id)->first();

        // Fetch the logged-in user and their role
        $user = Auth::user();
        $userRole = $user->role->name;

        // Check if the user is the Main Contractor for this project
        $isMainContractor = $this->isMainContractor($projectId, $user->id);

        // Fetch all tasks for the project using DB facade
        $tasks = DB::table('tasks')->where('project_id', $projectId)->get();

        $categorizedTasks = [
            'under_negotiation' => $tasks->where('status', 'under_negotiation'),
            'due_date' => $tasks->where('due_date', '!=', null), // Filter tasks with a due date
            'priority_1' => $tasks->where('priority', 1),
            'priority_2' => $tasks->where('priority', 2),
            'completed' => $tasks->where('status', 'completed'),
            'verified' => $tasks->where('status', 'verified')
        ];

        // Count the tasks for each category
        $taskCounts = $this->countTasks($categorizedTasks);

        // Fetch contractors related to the project
        $contractors = DB::table('users')
                        ->join('project_contractor', 'users.id', '=', 'project_contractor.contractor_id')
                        ->where('project_contractor.project_id', $projectId)
                        ->select('users.id', 'users.name')
                        ->get();

        // Calculate the project due date countdown
        $dueDateCountdown = $this->calculateDueDate($project->end_date);

        // Pass data to the view
        return view('tasks.index', [
            'categorizedTasks' => $categorizedTasks,
            'taskCounts' => $taskCounts,
            'project' => $project,
            'userRole' => $userRole,
            'isMainContractor' => $isMainContractor,
            'dueDateCountdown' => $dueDateCountdown,
            'projectManagerName' => $projectManager ? $projectManager->name : 'N/A',
            'mainContractorName' => $mainContractor ? $mainContractor->name : 'N/A',
            'contractors' => $contractors, // Pass contractors to the view
        ]);
    }

    private function calculateDueDate($endDate)
    {
        $today = now(); // Get the current date
        $dueDate = \Carbon\Carbon::parse($endDate); // Parse the end date into a Carbon instance

        // Calculate the difference in days between today and the end date
        return $today->diffInDays($dueDate, false); // `false` ensures it returns a negative value if past due
    }

    private function getTasksDueToday($projectId)
    {
        $today = now()->toDateString(); // Get today's date

        // Fetch tasks where the project's due date is today
        return DB::table('tasks')
            ->join('projects', 'tasks.project_id', '=', 'projects.id')
            ->where('projects.id', $projectId)
            ->where('projects.end_date', $today)
            ->get();
    }

    // Function to check if the user is a Main Contractor for this project
    private function isMainContractor($projectId, $userId)
    {
        // Check if the user is marked as the Main Contractor in the project_contractor table
        $mainContractor = DB::table('project_contractor')
                            ->where('project_id', $projectId)
                            ->where('contractor_id', $userId)
                            ->where('main_contractor', true)  // Check if the user is the main contractor
                            ->first();

        return $mainContractor !== null;
    }

    // Helper function to categorize tasks
    private function categorizeTasks($tasks)
    {
        return [
            'under_negotiation' => $tasks->filter(function ($task) {
                return $task->status == 'under_negotiation';
            }),
            'due_today' => $tasks->filter(function ($task) {
                return $task->due_date == now()->toDateString();
            }),
            'priority_1' => $tasks->filter(function ($task) {
                return $task->priority == '1';
            }),
            'priority_2' => $tasks->filter(function ($task) {
                return $task->priority == '2';
            }),
            'completed' => $tasks->filter(function ($task) {
                return $task->status == 'completed';
            }),
            'verified' => $tasks->filter(function ($task) {
                return $task->status == 'verified';
            }),
        ];
    }

    private function countTasks($categorizedTasks)
    {
        return [
            'negotiation' => isset($categorizedTasks['under_negotiation']) ? $categorizedTasks['under_negotiation']->count() : 0,
            'due_today' => isset($categorizedTasks['due_today']) ? $categorizedTasks['due_today']->count() : 0,
            'priority_1' => isset($categorizedTasks['priority_1']) ? $categorizedTasks['priority_1']->count() : 0,
            'priority_2' => isset($categorizedTasks['priority_2']) ? $categorizedTasks['priority_2']->count() : 0,
            'completed' => isset($categorizedTasks['completed']) ? $categorizedTasks['completed']->count() : 0,
            'verified' => isset($categorizedTasks['verified']) ? $categorizedTasks['verified']->count() : 0,
        ];
    }

    public function create($projectId)
    {
        // Fetch contractors for assigning tasks
        $contractors = DB::table('project_contractor')
                        ->join('users', 'project_contractor.contractor_id', '=', 'users.id')
                        ->where('project_contractor.project_id', $projectId)
                        ->select('users.id', 'users.name')
                        ->get();

        return view('tasks.partials.create', compact('projectId', 'contractors')); // Pass contractors to view
    }

    public function store(Request $request, $projectId)
    {
        // Validate input
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date', // Ensure date range is valid
            'invite_contractor' => 'nullable|email',
        ]);

        // Count existing tasks for project and create task reference
        $taskCount = DB::table('tasks')->where('project_id', $projectId)->count();
        $taskReference = '#' . ($taskCount + 1);

        // Insert task into database
        DB::table('tasks')->insert([
            'project_id' => $projectId,
            'title' => $taskReference . ' ' . $data['title'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'due_date' => $data['due_date'],
            'status' => 'under_negotiation', // All tasks start here
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send email invite to contractor (optional)

        return redirect()->route('tasks.index', $projectId)->with('success', 'Task created successfully!');
    }

    public function updateTaskStatus(Request $request, $taskId)
    {
        // Validate new status
        $newStatus = $request->validate([
            'status' => 'required|string',
        ]);

        // Update task status
        DB::table('tasks')->where('id', $taskId)->update(['status' => $newStatus['status']]);

        return response()->json(['success' => true]);
    }

    public function validateContractor(Request $request)
    {
        $email = $request->input('email');

        // Check if the contractor exists in the 'users' table and their role is 'contractor'
        $contractor = DB::table('users')->where('email', $email)->where('role', 'contractor')->first();

        // Return JSON response indicating if the contractor is valid
        return response()->json([
            'valid' => $contractor ? true : false,
        ]);
    }
}