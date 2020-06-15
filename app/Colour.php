<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Colour extends Model
{
    protected $fillable = ['colour'];

    // A colour may be available in more than one product
    public function products()
    {
        return $this->belongsToMany('App\Product');
    }

    public function transaction_details()
    {
        return $this->hasMany('App\Transaction_Detail');
    }
}
