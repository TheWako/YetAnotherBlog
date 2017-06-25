<?php

namespace BlogBundle\Listener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use BlogBundle\Entity\Comment;

class CommentListener
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage = null) 
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // only act on some "GenericEntity" entity
        if (!$entity instanceof Comment) {
            return;
        }

        if (null !== $currentUser = $this->getUser()) {
            $entity->setUser($currentUser);
        } else {
            $entity->setUser(0);
        }
    }

    public function getUser()
    {
        if (!$this->tokenStorage) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }
}