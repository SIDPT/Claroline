<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ResourceNodeRepositoryTest extends RepositoryTestCase
{
    private static $repo;
    private static $time;
    private static $roleManagerName;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Resource\ResourceNode');

        self::createWorkspace('ws_1');
        self::createWorkspace('ws_2');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_2'));
        self::$roleManagerName = 'ROLE_WS_MANAGER_'.self::get('ws_1')->getGuid();
        self::createRole(self::$roleManagerName);
        self::createUser('john', [self::get('ROLE_1'), self::get('ROLE_2')]);
        self::createUser('jane', [self::get('ROLE_2')]);
        self::createUser('manager_ws_1', [self::get(self::$roleManagerName)]);
        self::createResourceType('t_dir', 'Directory');
        self::createResourceType('t_file', 'File');
        self::createResourceType('t_link', 'Link', false);

        /*
         * Structure :
         *
         * ws_1
         *     dir_1
         *         dir_2
         *             l_dir_3
         *         dir_3
         *             dir_4
         *                 l_dir_4
         *                 file_1
         *
         * ws_2
         *     dir_5
         *         l_dir_2
         */
        self::createDirectory('dir_1', self::get('t_dir'), self::get('john'), self::get('ws_1'));
        self::createDirectory('dir_2', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_1'));
        self::sleep(1); // dates involved
        self::$time = self::getTime();
        self::sleep(1);
        self::createDirectory('dir_3', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_1'));
        self::createDirectory('dir_4', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_3'));
        self::createDirectory('dir_5', self::get('t_dir'), self::get('jane'), self::get('ws_2'));
        self::createFile('file_1', self::get('t_file'), self::get('john'), self::get('dir_4'));
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_1'), 1);
        self::createResourceRights(self::get('ROLE_2'), self::get('dir_2'), 3);
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_2'), 6);
        self::createResourceRights(self::get('ROLE_2'), self::get('dir_5'), 1);

        //add the role manager to~
    }

    public function testFindWorkspaceRoot()
    {
        $root = self::$repo->findWorkspaceRoot(self::get('ws_1'));
        $this->assertEquals(self::get('dir_1')->getResourceNode(), $root);
    }

    public function testFindDescendants()
    {
        $this->assertEquals(0, count(self::$repo->findDescendants(self::get('dir_2')->getResourceNode())));
        $this->assertEquals(4, count(self::$repo->findDescendants(self::get('dir_1')->getResourceNode())));
        $this->assertEquals(5, count(self::$repo->findDescendants(self::get('dir_1')->getResourceNode(), true)));
        $this->assertEquals(
            2,
            count(self::$repo->findDescendants(self::get('dir_3')->getResourceNode(), true, 't_dir'))
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFindChildrenThrowsAnExceptionIfNoRolesAreGiven()
    {
        self::$repo->findChildren(
            self::get('dir_1')->getResourceNode(),
            [],
            self::get('jane')
        );
    }

    public function testFindChildrenReturnsEverythingIfTheUserIsAdmin()
    {
        $children = self::$repo->findChildren(
            self::get('dir_1')->getResourceNode(),
            ['ROLE_ADMIN'],
            self::get('jane')
        );

        $this->assertEquals(2, count($children));
        $this->assertEquals('dir_2', $children[0]['name']);
        $this->assertEquals('dir_3', $children[1]['name']);
    }

    public function testFindChildrenReturnsEverythingIfTheUserIsManager()
    {
        $children = self::$repo->findChildren(
            self::get('dir_1')->getResourceNode(),
            [self::$roleManagerName],
            self::get('jane')
        );
        $this->assertEquals(2, count($children));
        $this->assertEquals('dir_2', $children[0]['name']);
        $this->assertEquals('dir_3', $children[1]['name']);
    }

    public function testFindChildrenReturnsOpenableResources()
    {
        $children = self::$repo->findChildren(
            self::get('dir_1')->getResourceNode(),
            ['ROLE_1', 'ROLE_2'],
            self::get('jane')
        );
        $this->assertEquals(1, count($children));
        $this->assertEquals('dir_2', $children[0]['name']);
        $this->assertEquals(3, $children[0]['mask']);
    }

    public function testFindMimeTypesWithMostResources()
    {
        $mimeTypes = self::$repo->findMimeTypesWithMostResources(10);
        $this->assertEquals(3, count($mimeTypes));
        $this->assertEquals('directory/mime', $mimeTypes[0]['type']);
        $this->assertEquals('file/mime', $mimeTypes[2]['type']);
        $this->assertEquals(5, $mimeTypes[0]['total']);
        $this->assertEquals(1, $mimeTypes[2]['total']);
    }
}
