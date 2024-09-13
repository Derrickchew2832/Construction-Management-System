<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index($projectId)
    {
        // Fetch the project by its ID
        $project = DB::table('projects')->where('id', $projectId)->first();
        
        // Check if the project exists
        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }

        // Fetch all tasks for the project
        $tasks = DB::table('tasks')->where('project_id', $projectId)->get();

        // Categorize tasks
        $categorizedTasks = [
            'due_today' => $tasks->filter(function ($task) {
                return $task->due_date == now()->toDateString();
            }),
            'priority_1' => $tasks->filter(function ($task) {
                return $task->priority == '1';
            }),
            'priority_2' => $tasks->filter(function ($task) {
                return $task->priority == '2';
            }),
            'priority_3' => $tasks->filter(function ($task) {
                return $task->priority == '3';
            }),
            'completed' => $tasks->filter(function ($task) {
                return $task->status == 'completed';
            }),
            'verified' => $tasks->filter(function ($task) {
                return $task->status == 'verified';
            }),
        ];

        // Count the tasks for each category
        $taskCounts = [
            'due_today' => $categorizedTasks['due_today']->count(),
            'priority_1' => $categorizedTasks['priority_1']->count(),
            'priority_2' => $categorizedTasks['priority_2']->count(),
            'priority_3' => $categorizedTasks['priority_3']->count(),
            'completed' => $categorizedTasks['completed']->count(),
            'verified' => $categorizedTasks['verified']->count(),
        ];

        // Pass data to the view
        return view('tasks.index', [
            'categorizedTasks' => $categorizedTasks,
            'taskCounts' => $taskCounts,  // Send task counts to the view
            'project' => $project  // Pass the project object to the view
        ]);
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
        // Validate task input
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'priority' => 'required|string',
            'status' => 'required|string',
            'assigned_to' => 'required|integer', // Include assigned contractor ID
        ]);

        // Insert task data into database
        DB::table('tasks')->insert([
            'project_id' => $projectId,
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'priority' => $data['priority'],
            'status' => $data['status'],
            'assigned_to' => $data['assigned_to'], // Assign task to a contractor
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Redirect to task index page with success message
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
}
