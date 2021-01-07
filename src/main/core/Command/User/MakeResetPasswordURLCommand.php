<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\User;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Entity\User as UserEntity;


/**
 * create a password reset URL to reset a user password.
 * (in case of issues with smtp or user mail)
 */
class MakeResetPasswordURLCommand extends Command
{
    use BaseCommandTrait;
   
    private $params = [
        'user_username' => 'username'
    ];
    


    //private $om;
    private $usermanager;
    private $router;

    public function __construct(
        //ObjectManager $om,
        UserManager $usermanager, 
        UrlGeneratorInterface $router
    ) {
        //$this->om = $om;
        $this->usermanager = $usermanager;
        $this->router = $router;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate a password reset url for a user.');
        $this->configureParams();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('user_username');
        $user = $this->usermanager->getUserByUsername($username);
        if($user === null) throw new Exception("Username ".$username." not found.", 1);
        
        $this->usermanager->initializePassword($user);
        $hash = $user->getResetPasswordHash();
        $link = $this->router->generate(
            'claro_index',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        )."#/newpassword/{$hash}";

        $output->writeln($link);
        return 0;
    }

    // */
}
