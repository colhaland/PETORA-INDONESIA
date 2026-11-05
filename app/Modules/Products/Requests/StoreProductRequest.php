<?php
declare(strict_types=1);
namespace App\Modules\Products\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('products.create'); }
    public function rules(): array
    {
        return ['name' => 'required|string|max:255', 'sku' => 'required|string|max:100', 'sell_price' => 'nullable|numeric|min:0'];
    }
}
