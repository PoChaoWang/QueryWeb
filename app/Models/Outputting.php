<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outputting extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = ['query_id', 'sheet_id', 'sheet_name', 'append'];

    public function queryRelation()
    {
        return $this->belongsTo(Query::class);
    }
}
