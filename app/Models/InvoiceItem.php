<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'rate',
        'amount',
        'period',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
