<?php

namespace App\Services\Admin;

use App\Models\Admin;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminProfileService
{
    public function updateProfile(Admin $admin, array $data): bool
    {
        return $admin->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    public function uploadAvatar(Admin $admin, UploadedFile $file): string
    {
        // Delete old avatar if exists
        if ($admin->avatar) {
            Storage::disk('public')->delete($admin->avatar);
        }

        $path = $file->store('avatars/admins', 'public');
        $admin->update(['avatar' => $path]);

        return $path;
    }
}
