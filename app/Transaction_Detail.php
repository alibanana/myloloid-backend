<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction_Detail extends Model
{
    protected $table = 'transaction_details';

    protected $fillable = [
        'transaction_id', 'product_id', 'colour_id', 'size_id', 'quantity'
    ];

    public function transaction()
    {
        return $this->belongsTo('App\Transaction');
    }

    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    public function colour()
    {
        return $this->belongsTo('App\Colour');
    }

    public function size()
    {
        return $this->belongsTo('App\Size');
    }
}
