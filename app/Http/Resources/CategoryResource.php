<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray($request)
{
          $data = parent::toArray($request); // ambil semua data dari model

    // Ubah photo jadi URL lengkap jika ada
    $data['photo'] = $this->photo ? asset('storage/' . $this->photo) : null;

    return $data;
}


}
