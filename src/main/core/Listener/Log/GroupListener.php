<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Log;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;

class GroupListener
{
    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $dispatcher;

    /**
     * GroupListener constructor.
     *
     * @param ObjectManager    $om
     * @param StrictDispatcher $dispatcher
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
    }

    public function onGroupCreate(CreateEvent $event)
    {
        $group = $event->getObject();

        $this->dispatcher->dispatch('log', 'Log\LogGroupCreate', [$group]);
    }

    public function onGroupDelete(DeleteEvent $event)
    {
        $group = $event->getObject();

        $this->dispatcher->dispatch('log', 'Log\LogGroupDelete', [$group]);
    }

    public function onGroupUpdate(UpdateEvent $event)
    {
        $group = $event->getObject();

        $uow = $this->om->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($group);

        if (count($changeSet) > 0) {
            $this->dispatcher->dispatch('log', 'Log\LogGroupUpdate', [$group, $changeSet]);
        }
    }

    public function onGroupPatch(PatchEvent $event)
    {
        $group = $event->getObject();
        $value = $event->getValue();
        $action = $event->getAction();

        if ($value instanceof User) {
            if ('add' === $action) {
                $this->dispatcher->dispatch('log', 'Log\LogGroupAddUser', [$group, $value]);
            } elseif ('remove' === $action) {
                $this->dispatcher->dispatch('log', 'Log\LogGroupRemoveUser', [$group, $value]);
            }
        } elseif ($value instanceof Role) {
            if ('add' === $action) {
                $this->dispatcher->dispatch('log', 'Log\LogRoleSubscribe', [$value, $group]);
            } elseif ('remove' === $action) {
                $this->dispatcher->dispatch('log', 'Log\LogRoleUnsubscribe', [$value, $group]);
            }
        }
    }
}
