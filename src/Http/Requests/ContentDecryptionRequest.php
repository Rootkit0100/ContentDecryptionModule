<?php

namespace ContentDecryptionPlugin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentDecryptionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'server_id' => 'required|integer',
            'url' => 'required|string|max:2990',
            'decryption_key' => 'string|required|max:250',
            'rtmp_server_id' => 'required|integer',
        ];
    }
}
