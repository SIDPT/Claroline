<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.progression_manager")
 */
class ProgressionManager
{
    /** @var FinderProvider */
    private $finder;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * @DI\InjectParams({
     *     "finder"              = @DI\Inject("claroline.api.finder"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager"),
     *     "serializer"          = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param FinderProvider            $finder
     * @param ResourceEvaluationManager $resourceEvalManager
     * @param SerializerProvider        $serializer
     */
    public function __construct(
        FinderProvider $finder,
        ResourceEvaluationManager $resourceEvalManager,
        SerializerProvider $serializer
    ) {
        $this->finder = $finder;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->serializer = $serializer;
    }

    /**
     * Retrieves list of resource nodes accessible by user and formatted for the progression tool.
     *
     * @param Workspace $workspace
     * @param User|null $user
     * @param int       $levelMax
     *
     * @return array
     */
    public function fetchItems(Workspace $workspace, User $user = null, $levelMax = 1)
    {
        $workspaceRoot = $this->finder->get(ResourceNode::class)->find([
            'workspace' => $workspace->getUuid(),
            'parent' => null,
        ])[0];

        $roles = $user ? $user->getRoles() : ['ROLE_ANONYMOUS'];
        $filters = [
            'active' => true,
            'published' => true,
            'hidden' => false,
            'resourceTypeEnabled' => true,
            'workspace' => $workspace->getUuid(),
        ];
        $sortBy = [
            'property' => 'name',
            'direction' => 1,
        ];

        if (!in_array('ROLE_ADMIN', $roles)) {
            $filters['roles'] = $roles;
        }
        // Get all resource nodes available for current user in the workspace
        $visibleNodes = $this->finder->get(ResourceNode::class)->find($filters);
        $filters['parent'] = $workspaceRoot;
        // Get all root resource nodes available for current user in the workspace
        $rootNodes = $this->finder->get(ResourceNode::class)->find($filters, $sortBy);
        $visibleNodesArray = [];

        foreach ($visibleNodes as $node) {
            $visibleNodesArray[$node->getUuid()] = $node;
        }
        $items = [];
        $this->formatNodes($items, $rootNodes, $visibleNodesArray, $user, $levelMax, 0);

        return $items;
    }

    /**
     * Recursive function that filters visible nodes and adds serialized version to list after adding some extra params.
     *
     * @param array     $items
     * @param array     $nodes
     * @param array     $visibleNodes
     * @param User|null $user
     * @param int       $levelMax
     * @param int       $level
     */
    private function formatNodes(array &$items, array $nodes, array $visibleNodes, User $user = null, $levelMax = 1, $level = 0)
    {
        foreach ($nodes as $node) {
            $evaluation = $user ?
                $this->resourceEvalManager->getResourceUserEvaluation($node, $user, false) :
                null;
            $item = $this->serializer->serialize($node, [Options::SERIALIZE_MINIMAL, Options::IS_RECURSIVE]);
            $item['level'] = $level;
            $item['openingUrl'] = ['claro_resource_show_short', ['id' => $item['id']]];
            $item['validated'] = !is_null($evaluation) && 0 < $evaluation->getNbOpenings();
            $items[] = $item;

            if ((is_null($levelMax) || $level < $levelMax) && isset($item['children']) && 0 < count($item['children'])) {
                $children = [];

                usort($item['children'], function ($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });

                foreach ($item['children'] as $child) {
                    // Checks if node is visible
                    if (isset($visibleNodes[$child['id']])) {
                        $children[] = $visibleNodes[$child['id']];
                    }
                }
                if (0 < count($children)) {
                    $this->formatNodes($items, $children, $visibleNodes, $user, $levelMax, $level + 1);
                }
            }
        }
    }
}