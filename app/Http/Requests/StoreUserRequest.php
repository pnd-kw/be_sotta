<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user && $user->role->name === 'superadmin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'role_id' => [
                'required',
                Rule::exists('roles', 'id'),
                function ($attribute, $value, $fail) {
                    $superadminRole = Role::where('name', 'superadmin')->first();
                    if ($superadminRole && $value == $superadminRole->id) {
                        $exists = User::where('role_id', $superadminRole->id)->exists();
                        if ($exists) {
                            $fail('Sudah ada superadmin. Hanya boleh satu.');
                        }
                    }
                },
            ],
        ];
    }
}
