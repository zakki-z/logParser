<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }
    public function getUser(): UserRepository
    {
        return $this->userRepository;
    }
    public function getUserById(?Uuid $id): UserRepository
    {
        return $this->userRepository->find($id);
    }
    public function updateUser(?Uuid $id, array $data)
    {
        $user = $this->userRepository->find($id);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        return $user;
    }
}
