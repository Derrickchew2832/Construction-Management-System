<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $userRole = DB::table('roles')->where('id', $user->role_id)->value('name'); // Fetch role name

        // Check if the user is the Main Contractor for this project
        $isMainContractor = ($user->id == $project->main_contractor_id);  // Simplified check directly with the project's main_contractor_id

        // Fetch all tasks for the project and join with contractor emails
        $tasks = DB::table('tasks')
            ->join('task_invitations', 'tasks.id', '=', 'task_invitations.task_id')
            ->join('users', 'task_invitations.contractor_id', '=', 'users.id')
            ->where('tasks.project_id', $projectId)
            ->select('tasks.*', 'users.email as contractor_email') // Select contractor's email
            ->get();

        // Ensure that the due_date category is properly handled even if no tasks have a due date
        $categorizedTasks = [
            'under_negotiation' => $tasks->where('status', 'under_negotiation'),
            'due_date' => $tasks->filter(function ($task) {
                return !is_null($task->due_date); // Only include tasks with a due date
            }),
            'priority_1' => $tasks->where('status', 'priority_1'),
            'priority_2' => $tasks->where('status', 'priority_2'),
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
                return $task->status == 'priority_1';
            }),
            'priority_2' => $tasks->filter(function ($task) {
                return $task->status == 'priority_2';
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
            'due_today' => isset($categorizedTasks['due_date']) ? $categorizedTasks['due_date']->count() : 0,
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
    // Validate the input
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'start_date' => 'required|date|before_or_equal:due_date',
        'due_date' => 'required|date|after_or_equal:start_date',
        'contractor_email' => 'required|email',
        'status' => 'required|string|in:under_negotiation,due_date,priority_1,priority_2,completed,verified',
        'task_pdf' => 'nullable|file|mimes:pdf|max:2048',
    ]);

    \Log::info("Validating Contractor Email: {$data['contractor_email']}");

    $contractor = DB::table('users')->where('email', $data['contractor_email'])->first();

    if (!$contractor) {
        \Log::error("Contractor with email {$data['contractor_email']} not found.");
        return response()->json(['success' => false, 'message' => 'Contractor not found.']);
    }

    \Log::info("Fetched Contractor Details: " . json_encode($contractor));

    $contractorRoleId = DB::table('roles')->where('name', 'contractor')->value('id');
    
    if ($contractor->role_id != $contractorRoleId) {
        \Log::error("The user with email {$data['contractor_email']} is not a contractor.");
        return response()->json(['success' => false, 'message' => 'This user is not a contractor.']);
    }

    $isMainContractor = DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('contractor_id', $contractor->id)
        ->where('main_contractor', 1)
        ->exists();

    \Log::info("Is Contractor ID: {$contractor->id} the main contractor for Project ID: {$projectId}? " . ($isMainContractor ? 'Yes' : 'No'));

    if ($isMainContractor) {
        \Log::error("Contractor ID: {$contractor->id} is the main contractor for Project ID: {$projectId}. Task assignment rejected.");
        return response()->json(['success' => false, 'message' => 'The main contractor cannot be assigned to this task.']);
    }

    $taskPdfPath = null;
    if ($request->hasFile('task_pdf')) {
        $taskPdfPath = $request->file('task_pdf')->store('tasks/pdf', 'public');
    }

    $taskId = DB::table('tasks')->insertGetId([
        'project_id' => $projectId,
        'title' => $data['title'],
        'description' => $data['description'],
        'start_date' => $data['start_date'],
        'due_date' => $data['due_date'],
        'status' => $data['status'],
        'task_pdf' => $taskPdfPath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Log::info("Task created with ID: {$taskId}");

    DB::table('task_invitations')->insert([
        'task_id' => $taskId,
        'contractor_id' => $contractor->id,
        'invited_by' => Auth::user()->id,
        'email' => $data['contractor_email'],
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Log::info("Task invitation sent to Contractor ID: {$contractor->id} for Task ID: {$taskId}");

    return response()->json(['success' => true, 'message' => 'Task created and contractor invited successfully.']);
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
}
