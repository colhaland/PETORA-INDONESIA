<?php

declare(strict_types=1);

namespace App\Modules\Pets\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pets.create');
    }

    public function rules(): array
    {
        return ['name' => 'required|string|max:255', 'species' => 'required|string|max:30', 'customer_id' => 'required|integer'];
    }
}
