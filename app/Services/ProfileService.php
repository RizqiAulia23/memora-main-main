<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    private ImageStore $avatarStore;

    public function __construct()
    {
        $this->avatarStore = new ImageStore('avatars');
    }

    public function updateProfile(User $user, array $data, ?UploadedFile $avatar): User
    {
        $oldAvatar = $user->avatar;
        $newAvatar = $this->avatarStore->update($oldAvatar, $avatar);

        try {
            $user->update([
                'name' => $data['name'],
                'bio' => $data['bio'] ?? null,
                'partner_name' => $data['partner_name'] ?? null,
                'relationship_date' => $data['relationship_date'] ?? null,
                'location' => $data['location'] ?? null,
                'avatar' => $newAvatar,
            ]);
        } catch (\Throwable $exception) {
            if ($newAvatar !== $oldAvatar) {
                $this->avatarStore->delete($newAvatar, 'profile-update-cleanup');
            }

            throw $exception;
        }

        if ($newAvatar !== $oldAvatar) {
            $this->avatarStore->delete($oldAvatar, 'profile-avatar-replacement');
        }

        return $user;
    }

    public function removeAvatar(User $user): void
    {
        $oldAvatar = $user->avatar;

        $user->update(['avatar' => null]);

        $this->avatarStore->delete($oldAvatar, 'profile-avatar-removal');
    }
}
