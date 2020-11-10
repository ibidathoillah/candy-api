<?php

namespace GetCandy\Api\Http\Requests\Baskets;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateLineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Category::class);
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'basket_id' => 'sometimes|hashid_is_valid:baskets',
            'variants' => 'array|unique_lines',
        ];

        $req_variants = [["id"=> $this->id,"quantity"=>$this->quantity]];

        $variants = app('api')->productVariants()->getByHashedId(
            collect($req_variants)->pluck('id')->toArray()
        );

        foreach ($req_variants ?? [] as $i => $v) {
            $variant = $variants->first(function ($variant) use ($v) {
                return $variant->encodedId() === $v['id'] ?? null;
            });
            if ($variant) {
                $max = $variant->max_qty>0 ? $variant->max_qty : 999999 ;
                $rules["variants.{$i}.quantity"] = 'required|numeric|min:1|min_quantity:'.$variant->min_qty.'|max:'.$max.'|min_batch:'.$variant->min_batch.'|in_stock:'.$v['id'] ?? '0';
            }
            $rules["variants.{$i}.id"] = 'required|hashid_is_valid:product_variants';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'variants.*.id.hashid_is_valid' => trans('getcandy::validation.hashid_is_valid'),
            'variants.*.quantity.min_quantity' => trans('getcandy::validation.min_qty'),
            'variants.*.quantity.max' => trans('getcandy::validation.max_qty'),
            'variants.*.quantity.min_batch' => trans('getcandy::validation.min_batch'),
        ];
    }
}
