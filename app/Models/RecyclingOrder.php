<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecyclingOrder extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'weight',
        'price_per_kg',
        'total_price',
        'description',
        'image',
        'pickup_address',
        'status',
        'petugas_id',
        'admin_notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}