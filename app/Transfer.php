<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfers';
    // protected $dates= ['transfer_date'];

    protected $fillable = [
        'transaction_id', 'transfer_date', 'transfer_time', 'sender_name', 'sender_phone', 'sender_bank', 'sender_acc_no', 'amount', 'receiver_name', 'receiver_bank', 'receiver_acc_no', 'file', 'notes'
    ];



    public function transaction()
    {
        return $this->belongsTo('App\Transaction');
    }
}
