<?php

namespace App\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class AuditSubscriber implements EventSubscriber
{
    public function __construct(private readonly Security $security) {}

    public function getSubscribedEvents(): array
    {
        return [Events::prePersist, Events::preUpdate];
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        // Retrieves the object (entity) that is being persisted
        $entity = $args->getObject();
        //Retrieves the “author”: email address of the logged-in user if available, otherwise null
        $author = $this->getAuthorEmail();

        if ($author === null) {
            return;
        }

        // Sets the createdBy and updatedBy fields if the entity has the corresponding methods
        if (method_exists($entity, 'getCreatedBy') && method_exists($entity, 'setCreatedBy')) {
            if ($entity->getCreatedBy() === null) {
                $entity->setCreatedBy($author);
            }
        }

        if (method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($author);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        // Retrieves the object (entity) that is being updated
        $entity = $args->getObject();
        // Retrieves the “author”: email address of the logged-in user if available, otherwise null
        $author = $this->getAuthorEmail();

        if ($author === null) {
            return;
        }

        if (method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($author);
        }

        // Recompute the change set to ensure Doctrine is aware of the changes
        $em = $args->getObjectManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
    }

    // Method to get the email of the currently logged-in user
    private function getAuthorEmail(): ?string
    {
        $user = $this->security->getUser();

        if ($user === null) {
            return null;
        }

        return method_exists($user, 'getUserIdentifier')
            ? $user->getUserIdentifier()
            : null;
    }
}
