<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id', 'delivery_id', 'status', 'invoice_no'
    ];

    public function transaction_details()
    {
        return $this->hasMany('App\Transaction_Detail');
    }

    public function transfer()
    {
        return $this->hasOne('App\Transfer');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    public function delivery()
    {
        return $this->belongsTo('App\delivery');
    }
}
