<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasFactory;

    protected $fillable = [ 'query_id', 'query_sql', 'csv_file_path', 'updated_by', 'updated_at', 'status', 'fail_reason' ];

    public function queryRelation()
    {
        return $this->belongsTo(Query::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}
