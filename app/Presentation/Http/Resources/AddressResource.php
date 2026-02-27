<?php

namespace App\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'customer_id'  => $this->customer_id,
            'cep'          => $this->cep,
            'street'       => $this->street,
            'number'       => $this->number,
            'complement'   => $this->complement,
            'neighborhood' => $this->neighborhood,
            'city'         => $this->city,
            'state'        => $this->state,
            'country'      => $this->country,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
