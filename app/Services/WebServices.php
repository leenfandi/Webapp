<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class WebServices
{
    public function validateData(array $data)
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Data is valid.',
        ];
    }

    public function validateDatalog(array $data)
    {
        $rules = [

            'email' => 'required|string|email',
            'password' => 'required|min:8',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Data is valid.',
        ];
    }
    protected function validateDataadmin(array $data)
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8',
            'user_type' => 'required|in:user,admin',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Data is valid.',
        ];
    }
}
