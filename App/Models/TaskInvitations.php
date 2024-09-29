<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TaskInvitation
{
    protected $table = 'task_invitations';

    /**
     * Get all invitations for a given contractor.
     *
     * @param int $contractorId
     * @return \Illuminate\Support\Collection
     */
    public function getInvitationsByContractor($contractorId)
    {
        return DB::table($this->table)
            ->join('tasks', 'task_invitations.task_id', '=', 'tasks.id')
            ->where('task_invitations.contractor_id', $contractorId)
            ->select('task_invitations.*', 'tasks.title as task_title', 'tasks.description')
            ->get();
    }

    /**
     * Get pending invitations by task ID.
     *
     * @param int $taskId
     * @return \Illuminate\Support\Collection
     */
    public function getPendingInvitationsByTask($taskId)
    {
        return DB::table($this->table)
            ->where('task_id', $taskId)
            ->where('status', 'pending')
            ->get();
    }

    /**
     * Invite a contractor to a task.
     *
     * @param int $taskId
     * @param int $contractorId
     * @param int $invitedBy
     * @param string $email
     * @return bool
     */
    public function inviteContractor($taskId, $contractorId, $invitedBy, $email)
    {
        return DB::table($this->table)->insert([
            'task_id' => $taskId,
            'contractor_id' => $contractorId,
            'invited_by' => $invitedBy,
            'email' => $email,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Accept an invitation.
     *
     * @param int $invitationId
     * @return int
     */
    public function acceptInvitation($invitationId)
    {
        return DB::table($this->table)
            ->where('id', $invitationId)
            ->update(['status' => 'accepted', 'updated_at' => now()]);
    }

    /**
     * Reject an invitation.
     *
     * @param int $invitationId
     * @return int
     */
    public function rejectInvitation($invitationId)
    {
        return DB::table($this->table)
            ->where('id', $invitationId)
            ->update(['status' => 'rejected', 'updated_at' => now()]);
    }

    /**
     * Check if a contractor is already invited to a specific task.
     *
     * @param int $taskId
     * @param int $contractorId
     * @return bool
     */
    public function isContractorAlreadyInvited($taskId, $contractorId)
    {
        return DB::table($this->table)
            ->where('task_id', $taskId)
            ->where('contractor_id', $contractorId)
            ->exists();
    }
}
