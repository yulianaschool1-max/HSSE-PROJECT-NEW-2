<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueFollowUpLog extends Model
{
    use HasFactory;

    protected $table = 'issue_follow_up_logs';

    protected $fillable = [
        'issue_follow_up_id',
        'user_id',
        'user_name',
        'action_type',
        'old_status',
        'new_status',
        'note',
        'attachment_path',
        'followed_up_at',
    ];

    protected function casts(): array
    {
        return [
            'followed_up_at' => 'datetime',
        ];
    }

    public function issueFollowUp(): BelongsTo
    {
        return $this->belongsTo(IssueFollowUp::class, 'issue_follow_up_id');
    }

    public function getFollowedUpByAttribute(): ?string
    {
        return $this->user_name;
    }

    public function getPreviousStatusAttribute(): ?string
    {
        return $this->old_status;
    }
}
