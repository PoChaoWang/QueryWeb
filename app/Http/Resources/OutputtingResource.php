<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutputtingResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'query_id'=> new QueryResource($this->query),
            'sheet_id' => $this->sheet_id,
            'sheet_name' => $this->sheet_name,
            'append' => $this->append,
        ];
    }
}
