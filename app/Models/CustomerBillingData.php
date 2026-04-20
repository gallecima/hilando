<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBillingData extends Model
{
    protected $fillable = [
        'customer_id',
        'business_name',
        'document_number',
        'tax_status',
        'invoice_type',
        'address_line',
        'city',
        'province',
        'postal_code',
        'country',
        'is_default',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
