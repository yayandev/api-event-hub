<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity',
        'sold_quantity',
        'reserved_quantity',
        'min_purchase',
        'max_purchase',
        'sale_start_date',
        'sale_end_date',
        'is_active',
        'sort_order',
        'benefits',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'sold_quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'min_purchase' => 'integer',
        'max_purchase' => 'integer',
        'sale_start_date' => 'datetime',
        'sale_end_date' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'benefits' => 'array',
    ];

    protected $appends = [
        'available_quantity',
        'is_sale_active',
        'formatted_price'
    ];

    /**
     * Get the event that owns the ticket type.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the available quantity attribute.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->sold_quantity - $this->reserved_quantity);
    }

    /**
     * Check if the ticket sale is currently active.
     */
    public function getIsSaleActiveAttribute(): bool
    {
        $now = now();

        return $this->is_active &&
            $now >= $this->sale_start_date &&
            $now <= $this->sale_end_date;
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Scope to get only active tickets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tickets by event.
     */
    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope to get tickets that are currently on sale.
     */
    public function scopeOnSale($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where('sale_start_date', '<=', $now)
            ->where('sale_end_date', '>=', $now);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Check if ticket type has available quantity.
     */
    public function hasAvailableQuantity($requestedQuantity = 1): bool
    {
        return $this->available_quantity >= $requestedQuantity;
    }

    /**
     * Check if the requested quantity is within purchase limits.
     */
    public function isValidPurchaseQuantity($quantity): bool
    {
        return $quantity >= $this->min_purchase && $quantity <= $this->max_purchase;
    }
}
