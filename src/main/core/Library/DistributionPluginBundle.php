<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library;

/**
 * Base class of all the plugin bundles on the claroline platform.
 */
abstract class DistributionPluginBundle extends PluginBundle
{

    public function getVersion(): string
    {
    	// go up from src/plugin/pname
    	$versionFromPlugin = realpath($this->getPath().'/../../../VERSION.txt');
    	// assuming an external plugin bundle sources are under /plugin/name/ folder, 
    	// following current webpack in-vendor search for modules
    	// go up from vendor/vname/package/plugin/pname
    	$versionFromVendor = realpath($this->getPath().'/../../../../../VERSION.txt');
    	$data = "unknown";
    	if($versionFromPlugin){
    		$data = file_get_contents($versionFromPlugin);
    	} else if($versionFromVendor) {
    		$data = file_get_contents($versionFromVendor);
    	}
    	$dataParts = explode("\n", $data); 
        return trim($dataParts[0]);
        
    }
}
