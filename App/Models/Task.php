<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * Get the project that owns the task using DB facade.
     */
    public function getProjectByTaskId($taskId)
    {
        return DB::table('tasks')
            ->join('projects', 'tasks.project_id', '=', 'projects.id')
            ->where('tasks.id', $taskId)
            ->select('projects.*')
            ->first();
    }

    /**
     * Get the contractor (user) assigned to the task using DB facade.
     */
    public function getAssignedContractor($taskId)
    {
        return DB::table('tasks')
            ->join('users', 'tasks.assigned_contractor_id', '=', 'users.id')
            ->where('tasks.id', $taskId)
            ->select('users.*')
            ->first();
    }

    /**
     * Get all the task contractors (quotes) related to this task using DB facade.
     */
    public function getTaskContractors($taskId)
    {
        return DB::table('task_contractor')
            ->join('users', 'task_contractor.contractor_id', '=', 'users.id')
            ->where('task_contractor.task_id', $taskId)
            ->select('task_contractor.*', 'users.name as contractor_name')
            ->get();
    }

    /**
     * Get all the task invitations for this task using DB facade.
     */
    public function getTaskInvitations($taskId)
    {
        return DB::table('task_invitations')
            ->join('users', 'task_invitations.contractor_id', '=', 'users.id')
            ->where('task_invitations.task_id', $taskId)
            ->select('task_invitations.*', 'users.name as contractor_name', 'users.email')
            ->get();
    }

    /**
     * Get task by its ID.
     */
    public function getTaskById($taskId)
    {
        return DB::table('tasks')
            ->where('id', $taskId)
            ->first();
    }

    /**
     * Get all tasks for a specific project.
     */
    public function getTasksByProjectId($projectId)
    {
        return DB::table('tasks')
            ->where('project_id', $projectId)
            ->get();
    }
}
