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

    /**
     * Create a new revision from this quotation.
     */
    public function createRevision(?User $user = null): Quotation
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
            $user ??= auth()->user();
            $rootParentId = $this->parent_quotation_id ?: $this->id;
            $rootParent = $this->parent_quotation_id ? $this->parentQuotation : $this;

            // Count existing revisions for this root quotation
            $existingRevisionsCount = Quotation::withTrashed()
                ->where('parent_quotation_id', $rootParentId)
                ->count();

            $nextRevNo = $existingRevisionsCount + 1;
            $baseNumber = preg_replace('/\/R\d+$/i', '', $rootParent->quotation_number);
            $newQuoteNumber = "{$baseNumber}/R{$nextRevNo}";

            // Mark all existing revisions in this family as not latest
            Quotation::where('id', $rootParentId)
                ->orWhere('parent_quotation_id', $rootParentId)
                ->update(['is_latest_revision' => false]);

            // Create new revision record
            $revision = new Quotation();
            $revision->fill([
                'quotation_number' => $newQuoteNumber,
                'financial_year' => $rootParent->financial_year,
                'sequence_no' => $rootParent->sequence_no,
                'revision_number' => $nextRevNo,
                'is_latest_revision' => true,
                'parent_quotation_id' => $rootParentId,
                'client_id' => $this->client_id,
                'client_name' => $this->client_name,
                'billing_address' => $this->billing_address ?: ($this->client?->billing_address ?: 'Guwahati, Assam'),
                'shipping_address' => $this->shipping_address,
                'gstin' => $this->gstin,
                'state' => $this->state ?: 'Assam',
                'state_code' => $this->state_code ?: '18',
                'quotation_date' => today(),
                'valid_until' => today()->addDays(15),
                'status' => QuotationStatus::Draft,
                'subtotal' => $this->subtotal ?? 0,
                'discount_amount' => $this->discount_amount ?? 0,
                'taxable_amount' => $this->taxable_amount ?? 0,
                'is_interstate' => (bool) $this->is_interstate,
                'cgst_amount' => $this->cgst_amount ?? 0,
                'sgst_amount' => $this->sgst_amount ?? 0,
                'igst_amount' => $this->igst_amount ?? 0,
                'total_tax' => $this->total_tax ?? 0,
                'round_off' => $this->round_off ?? 0,
                'grand_total' => $this->grand_total ?? 0,
                'amount_in_words' => $this->amount_in_words,
                'terms_and_conditions' => $this->terms_and_conditions,
                'notes' => $this->notes,
                'created_by_user_id' => $user?->id ?? $this->created_by_user_id,
            ]);
            $revision->save();

            // Clone line items
            foreach ($this->items as $item) {
                $revision->items()->create([
                    'product_id' => $item->product_id,
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'hsn_sac' => $item->hsn_sac,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_rate' => $item->unit_rate,
                    'discount_percentage' => $item->discount_percentage,
                    'gst_rate' => $item->gst_rate,
                    'taxable_value' => $item->taxable_value,
                    'total_amount' => $item->total_amount,
                    'sort_order' => $item->sort_order,
                ]);
            }

            // Log activities
            $this->activities()->create([
                'user_id' => $user?->id,
                'from_status' => $this->status->value,
                'to_status' => $this->status->value,
                'remarks' => "Created revision {$newQuoteNumber}",
            ]);

            $revision->activities()->create([
                'user_id' => $user?->id,
                'from_status' => null,
                'to_status' => QuotationStatus::Draft->value,
                'remarks' => "Revision created from {$this->quotation_number}",
            ]);

            return $revision;
        });
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
