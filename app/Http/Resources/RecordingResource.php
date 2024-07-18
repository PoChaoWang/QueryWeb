<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordingResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $updatedBy = (new User())->setConnectionByClient('data_studio')->find($this->updated_by);

        return [
            "id"=> $this->id,
            'query_id'=> new QueryResource($this->query),
            'query_sql' => $this->query_sql,
            "csv_file_path" => $this->csv_file_path,
            'updated_at' => (new Carbon($this->updated_at))->format('Y-m-d H:i:s'),
            'updated_by' => new UserResource($updatedBy),
            'status' => $this->status,
            'fail_reason' => $this->fail_reason,
            'outputting_status' => $this->outputting_status,
            'outputting_fail_reason' => $this->outputting_fail_reason
        ];
    }
}
