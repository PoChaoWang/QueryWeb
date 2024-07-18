<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueryResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $createdBy = (new User())->setConnectionByClient('data_studio')->find($this->created_by);
        $updatedBy = (new User())->setConnectionByClient('data_studio')->find($this->updated_by);

        return [
            'id' => $this->id,
            'name'=> $this->name,
            'query_sql' => $this->query_sql,
            'created_at' => (new Carbon($this->created_at))->format('Y-m-d'),
            'created_by' => new UserResource($createdBy),
            'updated_at' => (new Carbon($this->updated_at))->format('Y-m-d'),
            'updated_by' => new UserResource($updatedBy),
        ];
    }
}
