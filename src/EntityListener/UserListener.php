<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Quand on a set le plainPassword, par exemple dans fixture ou autres
// On a ce entity listener sur user qui va intervenir avant de persister

class UserListener {

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher) {
        $this->hasher = $hasher;
    }

    /**
     * To encode password before persist
     * @param User $user
     * @return void
     */
    public function prePersist(User $user) {

        $this->encodePassword($user);

    }

    /**
     * Encode password based on plain password
     * @param User $user
     * @return void
     */
    public function encodePassword(User $user) {
        if ($user->getPlainPassword() === null) {
            return;
        }

        $user->setPassword(
            $this->hasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        );

        // security
        $user->setPlainPassword(null);
    }

}