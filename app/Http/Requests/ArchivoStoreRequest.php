<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArchivoStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'archivo' => 'required|file|max:10240',
        ];
    }
}