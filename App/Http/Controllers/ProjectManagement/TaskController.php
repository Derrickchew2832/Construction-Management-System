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
        ->select('tasks.*', 'tasks.category', 'task_invitations.contractor_id as assigned_to', 'users.email as contractor_email')
        ->get();
        

    // Convert tasks to a collection
    $tasks = collect($tasks);

     // Categorize tasks
     $categorizedTasks = [
        'under_negotiation' => $tasks->filter(function ($task) {
            return $task->category == 'under_negotiation'; // Use category field
        }),
        'due_date' => $tasks->filter(function ($task) {
            return $task->category == 'due_date' && $task->due_date == now()->toDateString(); // Exact due date
        }),
        'priority_1' => $tasks->filter(function ($task) {
            return $task->category == 'priority_1'; // Use category field
        }),
        'priority_2' => $tasks->filter(function ($task) {
            return $task->category == 'priority_2'; // Use category field
        }),
        'completed' => $tasks->filter(function ($task) {
            return $task->category == 'completed'; // Use category field
        }),
        'verified' => $tasks->filter(function ($task) {
            return $task->category == 'verified'; // Use category field
        }),
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
    $dueDateCountdown = $this->calculateDueDate($project->start_date, $project->end_date);

    $totalProjectDays = \Carbon\Carbon::parse($project->start_date)->diffInDays(\Carbon\Carbon::parse($project->end_date));

    // Pass data to the view
    return view('tasks.index', [
        'projectId' => $projectId,
        'tasks' => $tasks,
        'categorizedTasks' => $categorizedTasks,
        'taskCounts' => $taskCounts,
        'project' => $project,  // Ensure the project is passed to the view
        'userRole' => $userRole,
        'isMainContractor' => $isMainContractor,
        'dueDateCountdown' => $dueDateCountdown,
        'totalProjectDays' => $totalProjectDays, 
        'projectManagerName' => $projectManager ? $projectManager->name : 'N/A',
        'mainContractorName' => $mainContractor ? $mainContractor->name : 'N/A',
        'contractors' => $contractors, // Pass contractors to the view
    ]);
}


private function calculateDueDate($startDate, $endDate)
{
    // Check if either start date or end date is missing
    if (!$startDate || !$endDate) {
        \Log::error("Missing start or end date for the project");
        return 'Project dates are not set';
    }

    // Parsing the dates
    $startDate = \Carbon\Carbon::parse($startDate); // Parse the start date into a Carbon instance
    $dueDate = \Carbon\Carbon::parse($endDate); // Parse the end date into a Carbon instance
    $today = now(); // Get the current date

    // Log the start and due dates for debugging purposes
    \Log::info("Start Date: " . $startDate);
    \Log::info("Due Date: " . $dueDate);
    \Log::info("Today Date: " . $today);

    // Total days of the project (from start to due date)
    $totalProjectDays = $startDate->diffInDays($dueDate, false);
    \Log::info("Total project days: " . $totalProjectDays); // Log total project days

    // Days remaining if the project is ongoing
    if ($today >= $startDate && $today <= $dueDate) {
        $daysRemaining = $today->diffInDays($dueDate);
        \Log::info("Days remaining: " . $daysRemaining); // Log remaining days
        return "$daysRemaining days remaining";
    }

    // Project hasn't started yet
    if ($today < $startDate) {
        $daysToStart = $today->diffInDays($startDate);
        \Log::info("Project starts in: " . $daysToStart . " days"); // Log days to start
        return "Project starts in $daysToStart days";
    }

    // Project already ended
    if ($today > $dueDate) {
        \Log::info("Project has already ended");
        return 'Project has already ended';
    }
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
        'category' => 'required|string|in:under_negotiation,due_date,priority_1,priority_2,completed,verified', // Updated from 'status' to 'category'
        'task_pdf' => 'nullable|file|mimes:pdf|max:2048',
    ]);

    $contractor = DB::table('users')->where('email', $data['contractor_email'])->first();

    if (!$contractor) {
        return response()->json(['success' => false, 'message' => 'Contractor not found.']);
    }

    $contractorRoleId = DB::table('roles')->where('name', 'contractor')->value('id');

    if ($contractor->role_id != $contractorRoleId) {
        return response()->json(['success' => false, 'message' => 'This user is not a contractor.']);
    }

    $isMainContractor = DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('contractor_id', $contractor->id)
        ->where('main_contractor', 1)
        ->exists();

    if ($isMainContractor) {
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
        'category' => 'under_negotiation', // Now it's 'category'
        'status' => 'pending', // Default task status on creation
        'task_pdf' => $taskPdfPath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('task_invitations')->insert([
        'task_id' => $taskId,
        'contractor_id' => $contractor->id,
        'invited_by' => Auth::user()->id,
        'email' => $data['contractor_email'],
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Task created and contractor invited successfully.']);
}


public function updateTaskStatus(Request $request, $taskId)
{
    // Validate new status
    $data = $request->validate([
        'status' => 'required|string|in:pending,approved,rejected', // Validate for task acceptance status
    ]);

    // Update task status (e.g., pending, approved, rejected)
    DB::table('tasks')
        ->where('id', $taskId)
        ->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

    return response()->json(['success' => true, 'message' => 'Task status updated successfully']);
}

    public function showQuote($projectId)
    {
        // Fetch the project by its ID
        $project = DB::table('projects')->where('id', $projectId)->first();

        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }

        // Fetch all tasks for the current project
        $tasks = DB::table('tasks')->where('project_id', $projectId)->get();

        if ($tasks->isEmpty()) {
            return redirect()->back()->with('error', 'No tasks found for this project.');
        }

        // Fetch all quotes for tasks in the current project
        foreach ($tasks as $task) {
            $task->quote = DB::table('task_contractor')->where('task_id', $task->id)->first();
        }

        // Fetch the project manager and main contractor
        $projectUsers = DB::table('users')
            ->whereIn('id', [$project->project_manager_id, $project->main_contractor_id])
            ->get()
            ->keyBy('id');

        $projectManager = $projectUsers->get($project->project_manager_id);
        $mainContractor = $projectUsers->get($project->main_contractor_id);

        // Determine if the current user is the main contractor
        $isMainContractor = auth()->user()->id == $project->main_contractor_id;

        // Pass all necessary data to the view
        return view('tasks.quote', [
            'project' => $project,
            'tasks' => $tasks,
            'projectId' => $projectId,
            'projectManagerName' => $projectManager ? $projectManager->name : 'N/A',
            'mainContractorName' => $mainContractor ? $mainContractor->name : 'N/A',
            'isMainContractor' => $isMainContractor
        ]);
    }

    public function respondToTaskQuote(Request $request, $projectId, $taskId)
    {
        // Fetch the current quote
        $quoteId = $request->input('quote_id');
        $quote = DB::table('task_contractor')->where('id', $quoteId)->first();

        // Get the logged-in user
        $currentUser = Auth::user();

        // Check if the current user is allowed to respond (based on `suggested_by`)
        if ($quote->suggested_by == $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'You cannot respond at this time. Wait for the other party to respond.'], 403);
        }

        // Handle actions (accept, reject, suggest)
        if ($request->action == 'accept') {
            // Update the task with the contractor who submitted the quote
            DB::table('tasks')
                ->where('id', $taskId)
                ->update([
                    'assigned_contractor_id' => $quote->contractor_id, // Update to the contractor who submitted the quote
                    'status' => 'approved', // Mark the task status as accepted
                    'updated_at' => now(),
                ]);
    
            // Update the task_contractor table to mark this contractor as a subcontractor
            DB::table('task_contractor')
                ->where('id', $quoteId)  // Update by quoteId
                ->update([
                    'status' => 'approved', // Approve the quote
                    'is_sub_contractor' => 1, // Mark as having a subcontractor
                    'is_final' => 1, // Mark as final
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true, 'message' => 'Quote accepted successfully.']);
        } elseif ($request->action == 'reject') {
            DB::table('task_contractor')
                ->where('id', $quoteId)  // Update by quoteId
                ->update([
                    'status' => 'rejected', // Reject the quote
                    'is_final' => 1, // Mark as final
                    'updated_at' => now(),
                ]);

            DB::table('tasks')
                ->where('id', $taskId)
                ->update([
                    'status' => 'rejected', // Update task status to rejected
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true, 'message' => 'Quote rejected successfully.']);
        } else {
            // Main Contractor suggests a new price
            DB::table('task_contractor')
                ->where('id', $quoteId)  // Update by quoteId
                ->update([
                    'quote_suggestion' => $request->input('new_price'),
                    'quote_pdf' => $request->file('new_pdf')->store('task_quotes', 'public'),
                    'suggested_by' => $currentUser->id,
                    'status' => 'suggested', // Status becomes suggested for Contractor to act next
                    'is_final' => 0,
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true, 'message' => 'Quote suggestion submitted successfully.']);
        }
    }

    public function invite($projectId)
    {
        // You can load any necessary data here, like project details, contractor emails, etc.
        return view('tasks.invite', compact('projectId'));
    }

    // Show statistics page for project
    public function statistics($projectId)
    {
        // Fetch contractor count, completed tasks, and main contractor's name
        $contractorCount = DB::table('project_contractor')->where('project_id', $projectId)->count();
        $completedTasksCount = DB::table('tasks')->where('project_id', $projectId)->where('status', 'completed')->count();
        $mainContractorName = DB::table('users')
            ->join('projects', 'users.id', '=', 'projects.main_contractor_id')
            ->where('projects.id', $projectId)
            ->value('users.name');

        // Pass the data to the view
        return view('tasks.statistics', compact('projectId', 'contractorCount', 'completedTasksCount', 'mainContractorName'));
    }

    public function viewTaskDetails($projectId, $taskId)
    {
        $task = DB::table('tasks')
            ->join('task_contractor', 'tasks.id', '=', 'task_contractor.task_id')
            ->join('users', 'task_contractor.contractor_id', '=', 'users.id')
            ->where('tasks.id', $taskId)
            ->where('tasks.project_id', $projectId)
            ->select(
                'tasks.title', 
                'tasks.description', 
                'tasks.start_date', 
                'tasks.due_date', 
                'tasks.status', 
                'tasks.task_pdf',
                'users.name as contractor_name', 
                'task_contractor.quoted_price', 
                'task_contractor.quote_pdf', 
                'task_contractor.quote_suggestion'
            )
            ->first();
    
        if (!$task) {
            \Log::error("Task not found for projectId: $projectId and taskId: $taskId");
            return response()->json(['error' => 'Task not found'], 404);
        }
    
        return view('tasks.taskdetails', compact('task'));
    }
    

}
