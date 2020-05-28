<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ToolSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /**
     * ToolSource constructor.
     *
     * @param FinderProvider     $finder
     * @param SerializerProvider $serializer
     * @param TokenStorage       $tokenStorage
     * @param WorkspaceManager   $workspaceManager
     */
    public function __construct(
        FinderProvider $finder,
        SerializerProvider $serializer,
        TokenStorage $tokenStorage,
        WorkspaceManager $workspaceManager
    ) {
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @param GetDataEvent $event
     */
    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();
        $user = $event->getUser();

        $roles = ['ROLE_ANONYMOUS'];
        if ($user) {
            $roles = $user->getRoles();
        }

        switch ($event->getContext()) {
            case DataSource::CONTEXT_DESKTOP:
                $options['hiddenFilters']['isDisplayableInDesktop'] = true;
                $options['hiddenFilters']['user'] = $user->getUuid();

                if (!in_array('ROLE_ADMIN', $roles)) {
                    $options['hiddenFilters']['roles'] = $roles;
                }
                break;

            case DataSource::CONTEXT_WORKSPACE:
                $workspace = $event->getWorkspace();
                $isManager = $this->workspaceManager->isManager($workspace, $this->tokenStorage->getToken());
                $options['hiddenFilters']['isDisplayableInWorkspace'] = true;
                $options['hiddenFilters']['workspace'] = $workspace->getUuid();

                if (!$isManager) {
                    $options['hiddenFilters']['roles'] = $roles;
                }
                break;
        }
        $context = [
            'type' => $event->getContext(),
            'data' => DataSource::CONTEXT_WORKSPACE === $event->getContext() ?
                $this->serializer->serialize($event->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                null,
        ];
        $tools = $this->finder->search(Tool::class, $options);

        for ($i = 0; $i < count($tools['data']); ++$i) {
            $tools['data'][$i]['context'] = $context;
        }
        $event->setData($tools);
        $event->stopPropagation();
    }
}
