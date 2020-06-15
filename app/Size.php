<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $fillable = ['size'];

    // A size may be available in more than one products
    public function products()
    {
        return $this->belongsToMany('App\Product');
    }

    public function transaction_details()
    {
        return $this->hasMany('App\Transaction_Detail');
    }
}
