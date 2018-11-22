<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Library;

use Claroline\AuthenticationBundle\Model\Oauth\OauthConfiguration;
use Claroline\CoreBundle\Library\Configuration\LegacyParametersMappingInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.configuration.mapping.legacy")
 */
class LegacyParametersMapping implements LegacyParametersMappingInterface
{
    public function getMapping()
    {
        $parameters = [];

        foreach (OauthConfiguration::resourceOwners() as $resourceOwner) {
            $resourceOwnerStr = str_replace(' ', '_', strtolower($resourceOwner));
            $parameters[$resourceOwnerStr.'_client_id'] = 'external_authentication.'.$resourceOwnerStr.'.client_id';
            $parameters[$resourceOwnerStr.'_client_secret'] = 'external_authentication.'.$resourceOwnerStr.'.client_secret';
            $parameters[$resourceOwnerStr.'_client_active'] = 'external_authentication.'.$resourceOwnerStr.'.client_active';
            $parameters[$resourceOwnerStr.'_client_force_reauthenticate'] = 'external_authentication.'.$resourceOwnerStr.'.client_force_reauthenticate';
        }

        $parameters['generic_authorization_url'] = 'external_authentication.generic.authorization_url';
        $parameters['generic_access_token_url'] = 'external_authentication.generic.access_token_url';
        $parameters['generic_infos_url'] = 'external_authentication.generic.infos_url';
        $parameters['generic_scope'] = 'external_authentication.generic.scope';
        $parameters['generic_paths_login'] = 'external_authentication.generic.paths_login';
        $parameters['generic_paths_email'] = 'external_authentication.generic.paths_email';
        $parameters['generic_display_name'] = 'external_authentication.generic.display_name';

        return $parameters;
    }
}