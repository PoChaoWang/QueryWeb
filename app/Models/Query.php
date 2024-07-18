<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Query extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'query_sql', 'created_by', 'updated_by'];

    public function setConnectionByClient($client)
    {
        $this->setConnection($client);
        return $this;
    }

    public function execute()
    {
        return DB::connection($this->getConnectionName())->select($this->query_sql);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function outputtings()
    {
        return $this->hasMany(Outputting::class);
    }

    public function recordings()
    {
        return $this->hasMany(Recording::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
