<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'financial_year',
        'sequence_no',
        'revision_number',
        'is_latest_revision',
        'parent_quotation_id',
        'client_id',
        'client_name',
        'billing_address',
        'shipping_address',
        'gstin',
        'state',
        'state_code',
        'quotation_date',
        'valid_until',
        'accepted_at',
        'status',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'is_interstate',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_tax',
        'round_off',
        'grand_total',
        'amount_in_words',
        'terms_and_conditions',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'accepted_at' => 'datetime',
            'is_latest_revision' => 'boolean',
            'is_interstate' => 'boolean',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_amount' => 'decimal:2',
            'total_tax' => 'decimal:2',
            'round_off' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Quotation $quotation) {
            if ($quotation->isDirty('status')) {
                $from = $quotation->getOriginal('status');
                $to = $quotation->status;

                $fromValue = $from instanceof QuotationStatus ? $from->value : (string) $from;
                $toValue = $to instanceof QuotationStatus ? $to->value : (string) $to;

                if ($to === QuotationStatus::Accepted && is_null($quotation->accepted_at)) {
                    $quotation->accepted_at = now();
                }

                $quotation->activities()->create([
                    'user_id' => auth()->id(),
                    'from_status' => $fromValue,
                    'to_status' => $toValue,
                    'remarks' => request()->input('activity_remarks') ?: "Status updated from {$fromValue} to {$toValue}",
                ]);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function parentQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(QuotationActivity::class)->latest('created_at');
    }

    public function isLocked(): bool
    {
        return $this->status === QuotationStatus::Accepted;
    }

    public function canBeEdited(): bool
    {
        return !$this->isLocked();
    }

    public function scopeLatestRevision(Builder $query): Builder
    {
        return $query->where('is_latest_revision', true);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', QuotationStatus::Accepted);
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

        // Executive sees quotations they created or whose client is assigned to them
        return $query->where(function ($q) use ($user) {
            $q->where('created_by_user_id', $user->id)
                ->orWhereHas('client', function ($clientQ) use ($user) {
                    $clientQ->where('assigned_user_id', $user->id);
                });
        });
    }
}
