<?php

namespace App\Http\Requests;

use App\Services\OrderNumberFormatterService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $formatter = app(OrderNumberFormatterService::class);

        $this->merge([
            'ebay_order_number' => $formatter->formatEbayOrderNumber($this->input('ebay_order_number')),
            'amazon_order_number' => $formatter->formatAmazonOrderNumber($this->input('amazon_order_number')),
            'ad_fee_charges' => $this->input('ad_fee_charges') ?: 0.00,
        ]);
    }

    public function rules(): array
    {
        return [
            'order_date' => ['required', 'date'],
            'ebay_order_number' => ['required', 'string', 'max:255'],
            'ebay_tracking_number' => ['nullable', 'string', 'max:255'],
            'customer_price' => ['required', 'numeric', 'min:0'],
            'ad_fee_charges' => ['nullable', 'numeric', 'min:0'],
            'amazon_order_number' => ['required', 'string', 'regex:/^\d{3}-\d{7}-\d{7}$/'],
            'supplier_cost' => ['required', 'numeric', 'min:0'],
            'ebay_net' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:Pending,Purchased,Shipped,Delivered,Completed,Cancelled,Refunded'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'ebay_order_number.regex' => 'The eBay order number must be in the format XX-XXXXX-XXXXX (e.g. 27-14904-69608).',
            'amazon_order_number.regex' => 'The Amazon order number must be in the format XXX-XXXXXXX-XXXXXXX (e.g. 304-9841814-4365162).',
        ];
    }
}
