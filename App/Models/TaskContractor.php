<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TaskContractor extends Model
{
    protected $table = 'task_contractor';

    /**
     * Fetch the task contractor record by task and contractor IDs.
     */
    public function getTaskContractor($taskId, $contractorId)
    {
        return DB::table($this->table)
            ->where('task_id', $taskId)
            ->where('contractor_id', $contractorId)
            ->first();
    }

    /**
     * Fetch all contractors for a specific task.
     */
    public function getContractorsByTaskId($taskId)
    {
        return DB::table($this->table)
            ->join('users', 'task_contractor.contractor_id', '=', 'users.id')
            ->where('task_contractor.task_id', $taskId)
            ->select('task_contractor.*', 'users.name as contractor_name')
            ->get();
    }

    /**
     * Fetch all tasks that a contractor has been invited to or submitted a quote for.
     */
    public function getTasksByContractorId($contractorId)
    {
        return DB::table($this->table)
            ->join('tasks', 'task_contractor.task_id', '=', 'tasks.id')
            ->where('task_contractor.contractor_id', $contractorId)
            ->select('task_contractor.*', 'tasks.title as task_title')
            ->get();
    }

    /**
     * Create or update a contractor's quote for a specific task.
     */
    public function submitQuote($taskId, $contractorId, $quotedPrice, $quotePdf)
    {
        return DB::table($this->table)->updateOrInsert(
            ['task_id' => $taskId, 'contractor_id' => $contractorId],
            [
                'quoted_price' => $quotedPrice,
                'quote_pdf' => $quotePdf,
                'status' => 'submitted',
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Update the status of a contractor's quote for a specific task.
     */
    public function updateQuoteStatus($quoteId, $status)
    {
        return DB::table($this->table)
            ->where('id', $quoteId)
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
    }

    /**
     * Fetch all task contractor records with status filtering (optional).
     */
    public function getTaskContractorsByStatus($taskId, $status = null)
    {
        $query = DB::table($this->table)
            ->join('users', 'task_contractor.contractor_id', '=', 'users.id')
            ->where('task_contractor.task_id', $taskId)
            ->select('task_contractor.*', 'users.name as contractor_name');

        if ($status) {
            $query->where('task_contractor.status', $status);
        }

        return $query->get();
    }
}
