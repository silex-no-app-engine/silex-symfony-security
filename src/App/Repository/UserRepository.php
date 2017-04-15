<?php

namespace CodeExperts\App\Repository;


use CodeExperts\App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends EntityRepository implements UserProviderInterface
{

    public function loadUserByUsername($username)
    {
        $user = $this->findOneByEmail($username);

        if(!$user)
            throw new UsernameNotFoundException(sprintf("Usuário %s não existe! ", $username));

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if(!$user instanceof User)
            throw new UnsupportedUserException(sprintf("Instância não suportada de %s", $user));

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'CodeExperts\App\Entity\User';
    }
}