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
        $line = app('api')->basketLines()->getByHashedId($this->id);
        $variant = $line->variant;
      
        if ($variant) {
            $max = $variant->max_qty>0 ? $variant->max_qty : 999999 ;
            $rules["quantity"] = 'required|numeric|min:1|min_quantity:'.$variant->min_qty.'|max:'.$max.'|min_batch:'.$variant->min_batch;
        }
        
        $rules["id"] = 'required|hashid_is_valid:product_variants';
        

        return $rules;
    }

    public function messages()
    {
        return [
            'id.hashid_is_valid' => trans('getcandy::validation.hashid_is_valid'),
            'quantity.min_quantity' => trans('getcandy::validation.min_qty'),
            'quantity.max' => trans('getcandy::validation.max_qty'),
            'quantity.min_batch' => trans('getcandy::validation.min_batch'),
        ];
    }
}
