<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'provinsi', 'kabupaten', 'kecamatan', 'alamat', 'notes'
    ];

    public function transaction()
    {
        return $this->hasOne('App\Transaction');
    }
}
