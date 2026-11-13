<?php

namespace App\Models;

use App\Enums\FollowUpStatus;
use App\Enums\ProspectSource;
use App\Enums\ProspectStage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_person',
        'company_name',
        'phone',
        'email',
        'source',
        'requirement',
        'expected_value',
        'stage',
        'lost_reason',
        'next_follow_up_date',
        'assigned_user_id',
        'converted_client_id',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'stage' => ProspectStage::class,
            'source' => ProspectSource::class,
            'expected_value' => 'decimal:2',
            'next_follow_up_date' => 'date',
            'converted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Prospect $prospect) {
            // When next_follow_up_date is updated, sync linked follow-up automatically
            if ($prospect->next_follow_up_date && !$prospect->is_converted) {
                $existing = $prospect->followUps()
                    ->where('status', FollowUpStatus::Pending)
                    ->latest('id')
                    ->first();

                if ($existing) {
                    $existing->update([
                        'due_date' => $prospect->next_follow_up_date,
                        'assigned_user_id' => $prospect->assigned_user_id,
                    ]);
                } else {
                    $prospect->followUps()->create([
                        'subject' => 'Follow-up with ' . $prospect->contact_person . ($prospect->company_name ? " ({$prospect->company_name})" : ''),
                        'assigned_user_id' => $prospect->assigned_user_id,
                        'due_date' => $prospect->next_follow_up_date,
                        'status' => FollowUpStatus::Pending,
                    ]);
                }
            }
        });
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'prospect_id');
    }

    public function getIsConvertedAttribute(): bool
    {
        return !is_null($this->converted_client_id);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('stage', ProspectStage::openStages());
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
