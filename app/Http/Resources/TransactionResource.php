<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $authUser = $request->user();

        return [
            'id' => $this->id,
            'tipo' => $this->from_user_id === $authUser->id ? 'enviada' : 'recebida',
            'valor' => $this->amount,
            'status' => $this->status,
            'data' => $this->created_at->format('d/m/Y H:i'),
            'remetente' => $this->fromUser?->nome,
            'destinatario' => $this->toUser?->nome,
        ];
    }
}