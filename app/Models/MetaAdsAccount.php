<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaAdsAccount extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'account_name','created_at', 'updated_at'];

    protected $primaryKey = 'account_id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
