<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        $targetUserId = $this->route('id_user');

        return $user && (
            $user->role->name === 'superadmin' ||
            $user->id_user === $targetUserId
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('id_user'), 'id_user'),
            ],
            'password' => 'sometimes|string|confirmed|min:6',
            'role_id' => [
                'sometimes',
                Rule::exists('roles', 'id'),
                function ($attribute, $value, $fail) {
                    $superadminRole = Role::where('name', 'superadmin')->first();
                    if (!$superadminRole || $value != $superadminRole->id) return;

                    $existingSuperadmin = User::where('role_id', $superadminRole->id)
                        ->where('id_user', '!=', $this->route('id_user'))
                        ->first();

                    if ($existingSuperadmin) {
                        $fail('Hanya boleh satu superadmin.');
                    }
                },
            ],
            'gender' => 'sometimes|in:male,female',
            'avatar' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'phone_number' => 'sometimes|string|max:20',
        ];
    }
}
