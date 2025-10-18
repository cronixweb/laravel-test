<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    public const TYPE_PERSONAL = 'personal';
    public const TYPE_HOUSE = 'house';
    public const TYPE_WORK = 'work';

    public const TYPES = [
        self::TYPE_PERSONAL,
        self::TYPE_HOUSE,
        self::TYPE_WORK,
    ];

    protected $fillable = [
        'tenant_id',
        'description',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Tenant, self>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
