<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'price', 'is_available'];

    // A product belongs to only one category
    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    // A product has many photos
    public function photos()
    {
        return $this->hasMany('App\Photo');
    }

    // A product may have more than one materials
    public function materials()
    {
        return $this->belongsToMany('App\Material');
    }
    
    // A product may have more than one sizes
    public function sizes()
    {
        return $this->belongsToMany('App\Size');
    }
    
    // A product may have more than one sizes
    public function colours()
    {
        return $this->belongsToMany('App\Colour');
    }

    public function transaction_details()
    {
        return $this->hasMany('App\Transaction_Detail');
    }
}
