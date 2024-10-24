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
        $isMainContractor = ($user->id == $project->main_contractor_id);
    
        // Fetch all tasks for the project and join with contractor emails
        $tasks = DB::table('tasks')
            ->leftJoin('users', 'tasks.assigned_contractor_id', '=', 'users.id') // Left join to get the assigned contractor email
            ->where('tasks.project_id', $projectId)
            ->select('tasks.*', 'users.email as contractor_email') // Select the contractor email
            ->get();
    
        // Convert tasks to a collection
        $tasks = collect($tasks);
    
        // Categorize tasks
        $categorizedTasks = [
            'under_negotiation' => $tasks->filter(function ($task) {
                return $task->category == 'under_negotiation';
            }),
            'due_date' => $tasks->filter(function ($task) {
                return $task->category == 'due_date' && $task->due_date == now()->toDateString();
            }),
            'priority_1' => $tasks->filter(function ($task) {
                return $task->category == 'priority_1';
            }),
            'priority_2' => $tasks->filter(function ($task) {
                return $task->category == 'priority_2';
            }),
            'completed' => $tasks->filter(function ($task) {
                return $task->category == 'completed';
            }),
            'verified' => $tasks->filter(function ($task) {
                return $task->category == 'verified';
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
            'project' => $project,
            'userRole' => $userRole,
            'isMainContractor' => $isMainContractor,
            'dueDateCountdown' => $dueDateCountdown,
            'totalProjectDays' => $totalProjectDays,
            'projectManagerName' => $projectManager ? $projectManager->name : 'N/A',
            'mainContractorName' => $mainContractor ? $mainContractor->name : 'N/A',
            'contractors' => $contractors,
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
        // Fetch the project to get its start and end dates
        $project = DB::table('projects')->where('id', $projectId)->first();
        
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.']);
        }
    
        // Validate the input, including custom validation for dates
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => [
                'required',
                'date',
                'before_or_equal:due_date', // Start date must be before or equal to due date
                function ($attribute, $value, $fail) use ($project) {
                    // Validate that the start date is within the project start and end dates
                    if ($value < $project->start_date || $value > $project->end_date) {
                        $fail('The start date must be within the project\'s start and end dates.');
                    }
                }
            ],
            'due_date' => [
                'required',
                'date',
                'after_or_equal:start_date', // Due date must be after or equal to start date
                function ($attribute, $value, $fail) use ($project) {
                    // Validate that the due date is within the project start and end dates
                    if ($value < $project->start_date || $value > $project->end_date) {
                        $fail('The due date must be within the project\'s start and end dates.');
                    }
                }
            ],
            'contractor_email' => 'required|email',
            'category' => 'required|string|in:under_negotiation,due_date,priority_1,priority_2,completed,verified',
            'task_pdf' => 'nullable|file|mimes:pdf|max:2048',
        ]);
    
        // Fetch the contractor by email
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

    // Calculate total project days
    $startDate = \Carbon\Carbon::parse($project->start_date);
    $endDate = \Carbon\Carbon::parse($project->end_date);
    $totalProjectDays = $startDate->diffInDays($endDate);

    // Pass all necessary data to the view
    return view('tasks.quote', [
        'project' => $project,
        'tasks' => $tasks,
        'projectId' => $projectId,
        'projectManagerName' => $projectManager ? $projectManager->name : 'N/A',
        'mainContractorName' => $mainContractor ? $mainContractor->name : 'N/A',
        'isMainContractor' => $isMainContractor,
        'totalProjectDays' => $totalProjectDays // Pass the total project days to the view
    ]);
}


public function respondToTaskQuote(Request $request, $projectId, $taskId)
{
    // Fetch the current quote
    $quoteId = $request->input('quote_id');
    $quote = DB::table('task_contractor')->where('id', $quoteId)->first();

    // Fetch the main contractor's approved quote price for the project
    $mainContractorQuote = DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('main_contractor', true)
        ->where('status', 'approved')
        ->value('quoted_price');

    // Get the logged-in user
    $currentUser = Auth::user();

    // Check if the current user is allowed to respond (based on `suggested_by`)
    if ($quote->suggested_by == $currentUser->id) {
        return response()->json(['success' => false, 'message' => 'You cannot respond at this time. Wait for the other party to respond.'], 403);
    }

    // Validate the contractor's quote against the main contractor's quote
    if ($request->action == 'accept' || $request->action == 'suggest') {
        if ($quote->quoted_price > $mainContractorQuote) {
            return response()->json(['success' => false, 'message' => 'The quoted price cannot be higher than the main contractor\'s quote.'], 403);
        }
    }

    // Handle actions (accept, reject, suggest)
    if ($request->action == 'accept') {
        // Fetch task due date
        $task = DB::table('tasks')->where('id', $taskId)->first();

        // Determine if the task is due today, and set the category accordingly
        $category = ($task->due_date == now()->toDateString()) ? 'due_date' : 'priority_2';

        // Update the task with the contractor who submitted the quote
        DB::table('tasks')
            ->where('id', $taskId)
            ->update([
                'assigned_contractor_id' => $quote->contractor_id, // Update to the contractor who submitted the quote
                'status' => 'approved', // Mark the task status as accepted
                'updated_at' => now(),
                'category' => $category, // Set category based on due date or priority
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
        $newPrice = $request->input('new_price');
        if ($newPrice > $mainContractorQuote) {
            return response()->json(['success' => false, 'message' => 'The suggested price cannot be higher than the main contractor\'s quote.'], 403);
        }

        DB::table('task_contractor')
            ->where('id', $quoteId)  // Update by quoteId
            ->update([
                'quote_suggestion' => $newPrice,
                'quote_pdf' => $request->file('new_pdf')->store('task_quotes', 'public'),
                'suggested_by' => $currentUser->id,
                'status' => 'suggested', // Status becomes suggested for Contractor to act next
                'is_final' => 0,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Quote suggestion submitted successfully.']);
    }
}


public function inviteClientForm($projectId)
{
    // Fetch the project based on projectId
    $project = DB::table('projects')->where('id', $projectId)->first();

    // Check if the project exists
    if (!$project) {
        return redirect()->back()->with('error', 'Project not found.');
    }

    // Calculate total project days
    $startDate = \Carbon\Carbon::parse($project->start_date);
    $endDate = \Carbon\Carbon::parse($project->end_date);
    $totalProjectDays = $startDate->diffInDays($endDate);

    // Fetch project manager name and main contractor name
    $projectManagerName = DB::table('users')->where('id', $project->project_manager_id)->value('name');
    $mainContractorName = DB::table('users')->where('id', $project->main_contractor_id)->value('name');

    // Fetch all invitations related to this project
    $invitations = DB::table('project_invitations_client')->where('project_id', $projectId)->get();

    // Pass project, project manager, main contractor, invitation data, and total project days to the view
    return view('tasks.invite', compact('project', 'invitations', 'projectId', 'projectManagerName', 'mainContractorName', 'totalProjectDays'));
}




public function inviteClient(Request $request, $projectId)
{
    // Validate email input
    $request->validate([
        'email' => 'required|email'
    ]);

    // Check if the project exists
    $project = DB::table('projects')->where('id', $projectId)->first();
    if (!$project) {
        return redirect()->back()->with('error', 'Project not found.');
    }

    // Find the client by email in the users table
    $client = DB::table('users')->where('email', $request->email)->first();
    if (!$client) {
        return redirect()->back()->with('error', 'Client not found.');
    }

    // Insert invitation into the database without generating a token
    DB::table('project_invitations_client')->insert([
        'project_id' => $projectId,
        'email' => $request->email,
        'client_id' => $client->id, // Associate the client ID
        'invited_by' => auth()->user()->id,  // The project manager sending the invite
        'status' => 'pending',  // Default status is 'pending'
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return redirect()->back()->with('success', 'Invitation sent successfully!');
}



// Method to update invitation status
public function updateInvitationStatus(Request $request, $invitationId)
{
    // Validate that the status is either 'accepted' or 'rejected'
    $request->validate([
        'status' => 'required|in:accepted,rejected',
    ]);

    // Update the status of the invitation
    DB::table('project_invitations_client')->where('id', $invitationId)->update([
        'status' => $request->status,
        'updated_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Invitation status updated!');
}


public function statistics($projectId)
{
    // Retrieve project details
    $project = DB::table('projects')->where('id', $projectId)->first();
    
    // Retrieve the project manager's name
    $projectManagerName = DB::table('users')
        ->where('id', $project->project_manager_id)
        ->value('name');
    
    // Retrieve the main contractor's name if available
    $mainContractorName = DB::table('users')
        ->where('id', $project->main_contractor_id)
        ->value('name');

    // Calculate total project days
    $startDate = \Carbon\Carbon::parse($project->start_date);
    $endDate = \Carbon\Carbon::parse($project->end_date);
    $totalProjectDays = $endDate->diffInDays($startDate);

    // Task Status Distribution
    $taskStatusData = DB::table('tasks')
        ->select('status', DB::raw('COUNT(*) as total'))
        ->where('project_id', $projectId)
        ->groupBy('status')
        ->get();

    // Task Distribution by Category
    $taskCategoryData = DB::table('tasks')
        ->select('category', DB::raw('COUNT(*) as total'))
        ->where('project_id', $projectId)
        ->groupBy('category')
        ->get();

    // Project Budget Allocation (Total Budget vs Budget Remaining)
    $projectBudgetData = DB::table('projects')
        ->select('total_budget', 'budget_remaining')
        ->where('id', $projectId)
        ->first();

    // Fetch contractor names along with task assignments
    $contractorAssignmentData = DB::table('tasks')
        ->join('users', 'tasks.assigned_contractor_id', '=', 'users.id')
        ->select('users.name as contractor_name', DB::raw('COUNT(tasks.id) as total_tasks'))
        ->where('tasks.project_id', $projectId)
        ->whereNotNull('tasks.assigned_contractor_id')
        ->groupBy('users.name')
        ->get();

    // Task Completion Percentage (completed vs total)
    $completedTasksCount = DB::table('tasks')
        ->where('project_id', $projectId)
        ->where('category', 'completed')
        ->count();
    $totalTasksCount = DB::table('tasks')
        ->where('project_id', $projectId)
        ->count();

    // Handling role-based data for charts

    // Main Contractor's Quoted Price
    $mainContractorQuote = DB::table('project_contractor')
        ->where('project_id', $projectId)
        ->where('main_contractor', true)
        ->where('status', 'approved')
        ->value('quoted_price');

    // For Main Contractor - Tasks Quoted Price
    $mainContractorTasksQuotedPrice = DB::table('task_contractor')
        ->join('tasks', 'task_contractor.task_id', '=', 'tasks.id')
        ->where('tasks.project_id', $projectId)
        ->where('task_contractor.is_final', true)
        ->where('task_contractor.status', 'approved')
        ->where('task_contractor.contractor_id', $project->main_contractor_id)
        ->sum('task_contractor.quoted_price');

    // Contractor's Task Accepted Price
    $acceptedTasks = DB::table('task_contractor')
        ->join('tasks', 'task_contractor.task_id', '=', 'tasks.id')
        ->where('tasks.project_id', $projectId)
        ->where('task_contractor.is_final', true)
        ->where('task_contractor.status', 'approved')
        ->sum('task_contractor.quoted_price');

    // Supply Ordered Price (Using 'quoted_price' instead of 'total_price')
    $supplyOrderTotal = DB::table('supply_orders')
        ->where('project_id', $projectId)
        ->sum('quoted_price');  // Updated to reflect 'quoted_price' from supply_orders table

    // Pass all variables to the view
    return view('tasks.statistics', compact(
        'project', 'taskStatusData', 'taskCategoryData', 'projectBudgetData',
        'contractorAssignmentData', 'completedTasksCount', 'totalTasksCount', 'projectId', 
        'projectManagerName', 'mainContractorName', 'totalProjectDays', 
        'mainContractorQuote', 'mainContractorTasksQuotedPrice', 'acceptedTasks', 'supplyOrderTotal'
    ));
}



    public function updateStatus(Request $request, $taskId)
    {
        // Validate the new status
        $data = $request->validate([
            'status' => 'required|string|in:pending,approved,rejected', // Validate task acceptance status
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

    public function updateCategory(Request $request, $projectId, $taskId)
    {
        // Validate the category input to ensure it's within the allowed categories
        $request->validate([
            'category' => 'required|string|in:under_negotiation,due_date,priority_1,priority_2,completed,verified',
        ]);
    
        // Update the task's category and record the current time for `updated_at`
        DB::table('tasks')
            ->where('id', $taskId)
            ->update([
                'category' => $request->input('category'),
                'updated_at' => now(),
            ]);
    
        // Fetch the updated task along with the assigned contractor's email from the `users` table
        $updatedTask = DB::table('tasks')
            ->leftJoin('users', 'tasks.assigned_contractor_id', '=', 'users.id')
            ->where('tasks.id', $taskId)
            ->select('tasks.id', 'tasks.category', 'users.email as contractor_email')
            ->first();
    
        // Log the updated task details for debugging
        \Log::info("Updated Task with Contractor Email: ", (array) $updatedTask);
    
        // Return the response with the updated task details
        return response()->json([
            'success' => true,
            'task' => [
                'id' => $updatedTask->id,
                'category' => $updatedTask->category,
                'contractor_email' => $updatedTask->contractor_email ?? 'Unassigned', // Use 'Unassigned' if no contractor is assigned
            ],
        ]);
    }

    public function viewPhotos($projectId)
{
    // Fetch the project details
    $project = DB::table('projects')->where('id', $projectId)->first();
    
    if (!$project) {
        return redirect()->back()->withErrors(['error' => 'Project not found.']);
    }

    // Fetch the project manager's name
    $projectManager = DB::table('users')->where('id', $project->project_manager_id)->first();
    $projectManagerName = $projectManager ? $projectManager->name : 'Unknown';

    // Fetch the main contractor's name
    $mainContractor = DB::table('users')
                        ->join('project_contractor', 'users.id', '=', 'project_contractor.contractor_id')
                        ->where('project_contractor.project_id', $projectId)
                        ->where('project_contractor.main_contractor', 1)
                        ->first();
    $mainContractorName = $mainContractor ? $mainContractor->name : 'No Main Contractor';

    // Calculate total project days
    $totalProjectDays = \Carbon\Carbon::parse($project->start_date)
                          ->diffInDays(\Carbon\Carbon::parse($project->end_date));

    // Fetch the photos for the project and join with tasks using 'title' column
    $photos = DB::table('photos')
                ->leftJoin('tasks', 'photos.task_id', '=', 'tasks.id')
                ->select('photos.*', 'tasks.title as task_name') // Changed 'tasks.name' to 'tasks.title'
                ->where('photos.project_id', $projectId)
                ->get();

    // Fetch the tasks related to the project for the task dropdown
    $tasks = DB::table('tasks')
               ->where('project_id', $projectId)
               ->get();

    // Pass all the required data to the view
    return view('tasks.photos', compact(
        'photos', 
        'projectId', 
        'project', 
        'projectManagerName', 
        'mainContractorName', 
        'totalProjectDays', 
        'tasks'
    ));
}

public function uploadPhoto(Request $request, $projectId)
{
    // Validate the uploaded data
    $request->validate([
        'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'nullable|string',
        'task_id' => 'required|exists:tasks,id'
    ]);

    // Store the uploaded photo in the storage
    $photoPath = $request->file('photo')->store('photos', 'public');

    // Insert the photo details into the database
    DB::table('photos')->insert([
        'project_id' => $projectId,
        'task_id' => $request->task_id, // Associate the photo with a task
        'uploaded_by' => Auth::id(),
        'photo_path' => $photoPath,
        'description' => $request->description,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Redirect back with a success message
    return redirect()->back()->with('success', 'Photo uploaded successfully.');
}


public function viewFiles($projectId)
{
    // Fetch the project details
    $project = DB::table('projects')->where('id', $projectId)->first();

    // Fetch the project manager name
    $projectManager = DB::table('users')->where('id', $project->project_manager_id)->first();
    $projectManagerName = $projectManager ? $projectManager->name : 'Unknown';

    // Fetch the main contractor name
    $mainContractor = DB::table('users')
                        ->join('project_contractor', 'users.id', '=', 'project_contractor.contractor_id')
                        ->where('project_contractor.project_id', $projectId)
                        ->where('project_contractor.main_contractor', 1)
                        ->first();
    $mainContractorName = $mainContractor ? $mainContractor->name : 'No Main Contractor';

    // Calculate total project days
    $totalProjectDays = \Carbon\Carbon::parse($project->start_date)->diffInDays(\Carbon\Carbon::parse($project->end_date));

    // Fetch the files for the project
    $files = DB::table('documents')->where('project_id', $projectId)->get();

    // Pass all the required data to the view
    return view('tasks.files', compact('files', 'projectId', 'project', 'projectManagerName', 'mainContractorName', 'totalProjectDays'));
}



    public function endProject(Request $request, $projectId)
    {
        $project = DB::table('projects')->where('id', $projectId)->first();

        // Check if the user is the main contractor
        if (auth()->user()->id == $project->main_contractor_id && $project->status !== 'completed') {
            DB::table('projects')->where('id', $projectId)->update([
                'status' => 'completed',
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized or project already completed.'], 403);
    }

    

}


