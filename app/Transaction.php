<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'secondary_user_id','seller_email','product_id','quantity','transaction_ref','status',
       'amount','accept_transaction','dispute','description'
    ];

    public function secondary_user()
    {
        return $this->belongsTo('App\User', 'secondary_user_id');
    }

    // public function payment()
    // {
    //     return $this->hasOne('App\Payment');
    // }

    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    public function payment()
    {
        return $this->hasOne('App\Payment');
    }
}
