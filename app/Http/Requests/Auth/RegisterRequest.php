<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function createUser(): User
    {
        return User::create([
            'first_name' => $this->string('first_name')->toString(),
            'last_name' => $this->string('last_name')->toString(),
            'name' => trim($this->string('first_name')->toString().' '.$this->string('last_name')->toString()),
            'email' => $this->string('email')->toString(),
            'phone' => $this->string('phone')->toString(),
            'password' => $this->string('password')->toString(),
            'role' => UserRole::Customer,
            'is_active' => true,
        ]);
    }
}
