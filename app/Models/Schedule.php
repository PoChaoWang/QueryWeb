<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['query_id', 'week_day', 'time'];

    public function queryRelation()
    {
        return $this->belongsTo(Query::class);
    }
}
