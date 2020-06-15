<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // A category has many products
    public function products()
    {
        return $this->hasMany('App\Product');
    }
}
