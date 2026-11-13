<?php

namespace App\Models;

use App\Enums\TargetPeriodType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_type',
        'financial_year',
        'month',
        'target_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_type' => TargetPeriodType::class,
            'target_amount' => 'decimal:2',
            'month' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMonthNameAttribute(): string
    {
        if ($this->month === 0 || $this->period_type === TargetPeriodType::Yearly) {
            return 'Full Year';
        }

        return self::monthOptions()[$this->month] ?? "Month {$this->month}";
    }

    public static function monthOptions(): array
    {
        return [
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
            1 => 'January',
            2 => 'February',
            3 => 'March',
        ];
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

        return $query->where('user_id', $user->id);
    }
}
