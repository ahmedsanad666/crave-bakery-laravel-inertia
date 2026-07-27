<?php

namespace App\Http\Requests;

use App\Models\AdminUser;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var AdminUser $adminUser */
        $adminUser = $this->route('adminUser');

        return $this->user()?->can('updatePermissions', $adminUser) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array'],
        ];
    }
}
