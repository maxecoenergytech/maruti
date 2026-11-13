<?php

namespace App\Models;

use App\Enums\FollowUpStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject',
        'assigned_user_id',
        'client_id',
        'prospect_id',
        'quotation_id',
        'due_date',
        'due_time',
        'status',
        'outcome_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => FollowUpStatus::class,
            'due_date' => 'date',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === FollowUpStatus::Pending &&
            $this->due_date &&
            $this->due_date->isPast() &&
            !$this->due_date->isToday();
    }

    public function isDueToday(): bool
    {
        return $this->status === FollowUpStatus::Pending &&
            $this->due_date &&
            $this->due_date->isToday();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', FollowUpStatus::Pending);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', FollowUpStatus::Pending)
            ->whereDate('due_date', '<', Carbon::today());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->where('status', FollowUpStatus::Pending)
            ->whereDate('due_date', Carbon::today());
    }

    public function scopeVisibleTo(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('assigned_user_id', $user->id);
    }
}
