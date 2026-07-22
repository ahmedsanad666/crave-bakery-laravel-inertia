<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminProfileService
{
    /**
     * @param  array{name: string, email: string, avatar?: UploadedFile|null, remove_avatar?: bool}  $data
     */
    public function update(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['remove_avatar'])) {
            $this->deleteAvatarFile($user->avatar);
            $user->avatar = null;
        } elseif (($data['avatar'] ?? null) instanceof UploadedFile) {
            $this->deleteAvatarFile($user->avatar);
            $user->avatar = $data['avatar']->store('avatars', 'public');
        }

        $user->save();

        return $user->fresh();
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->password = $password;
        $user->save();

        return $user->fresh();
    }

    public static function avatarUrl(?string $avatar): ?string
    {
        return Product::toPublicUrl($avatar);
    }

    private function deleteAvatarFile(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '/')
        ) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
