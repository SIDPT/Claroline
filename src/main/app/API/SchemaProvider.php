<?php

namespace Claroline\AppBundle\API;

use JVal\Context;
use JVal\Registry;
use JVal\Resolver;
use JVal\Uri;
use JVal\Utils;
use JVal\Walker;

class SchemaProvider
{
    /**
     * The list of registered serializers in the platform.
     *
     * @var array
     */
    private $serializers = [];
    /** @var string */
    private $projectDir;
    /** @var string */
    private $baseUri;

    /**
     * @param string $projectDir
     */
    public function __construct($projectDir, SerializerProvider $serializer)
    {
        $this->projectDir = $projectDir;
        $this->baseUri = 'https://github.com/claroline/Distribution/tree/master';
        $this->serializer = $serializer;
    }

    /**
     * Returns the class handled by the schema provider.
     *
     * @param mixed $serializer
     *
     * @return string
     */
    public function getSchemaHandledClass($serializer)
    {
        if (method_exists($serializer, 'getClass')) {
            // 1. the serializer implements the getClass method, so we just call it
            //    this is the recommended way because it's more efficient than using reflection
            return $serializer->getClass();
        } else {
            // 2. else, we try to find the correct serializer by using the type hint of the `serialize` method
            //    this is not always possible, because some serializers can not use type hint (mostly because of an Interface),
            //    so for this case the `getClass` method is required
            $p = new \ReflectionParameter([get_class($serializer), 'serialize'], 0);

            return $p->getClass()->getName();
        }
    }

    /**
     * Gets a registered serializer instance.
     *
     * @param string $class
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($class)
    {
        foreach ($this->serializer->all() as $serializer) {
            if ($class === $this->getSchemaHandledClass($serializer)) {
                return $serializer;
            }
        }

        //no exception to not break everything atm
        return null;
    }

    /**
     * Check if serializer instance exists.
     *
     * @param string $class
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function has($class)
    {
        // search for the correct serializer
        foreach ($this->serializers as $serializer) {
            if ($class === $this->getSchemaHandledClass($serializer)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the identifier list from the json schema.
     *
     * @param string $class
     *
     * @return array
     */
    public function getIdentifiers($class)
    {
        $schema = $this->getSchema($class);

        if (isset($schema->claroline)) {
            return $schema->claroline->ids;
        }

        return [];
    }

    /**
     * Gets the json schema of a class.
     *
     * @param string $class
     * @param array  $options
     *
     * @return \stdClass
     */
    public function getSchema($class, array $options = [])
    {
        $serializer = $this->get($class);
        $schema = null;

        if (method_exists($serializer, 'getSchema')) {
            $url = $serializer->getSchema();
            $path = explode('/', $url);

            // following webpack uri mappings  : 
            // # for distribution modules (see webpack/plugins/distribution-shortcut.js) 
            // ~ for costom vendor modules (see webpack/plugins/vendor-shortcut.js)
            $prefix = array_shift($path); 
            
            if("#" === $prefix){ // for schemas in the distribution folder (now src in claroline repo)
                $bundleType = array_shift($path); // main, integration or plugin
                $bundleRootFolder = array_shift($path); // bundle root folder name

                $schema = $this->loadSchema(
                        $this->projectDir.'/src/'
                            .$bundleType.'/'.$bundleRootFolder.'/Resources/schemas/'.implode('/', $path)
                    );
                
            } else if ("~" === $prefix){
                $vendorName = array_shift($path); // name of the vendor as declared in composer
                $bundleName = array_shift($path); // name of the bundle as declared in composer

                $bundleType = array_shift($path); // main, integration or plugin
                $bundleRootFolder = array_shift($path); // bundle root folder name

                $schema = $this->loadSchema(
                        $this->projectDir.'/vendor/'
                            .$vendorName.'/'.$bundleName.'/'.$bundleType.'/'.$bundleRootFolder.'/Resources/schemas/'.implode('/', $path)
                    );
            }

            if($schema && 
                    in_array(Options::IGNORE_COLLECTIONS, $options) && 
                    isset($schema->properties)) {
                foreach ($schema->properties as $key => $property) {
                    if ('array' === $property->type) {
                        unset($schema->properties->{$key});
                    }
                }
            }
            
        }

        return $schema;
    }

    /**
     * Gets the json schema examples.
     *
     * @param string $class
     * @param array  $options
     *
     * @return array
     */
    public function getSamples($class, array $options = [])
    {
        $serializer = $this->get($class);
        $samples = [];

        if (method_exists($serializer, 'getSamples')) {
            $iterator = new \DirectoryIterator($this->getSampleDirectory($class).'/json/valid/create');

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $originalData = \file_get_contents($file->getPathName());
                    $samples[basename($file)] = json_decode($originalData, true);
                }
            }
        }

        return $samples;
    }

    /**
     * Loads a json schema.
     *
     * @param string $path
     *
     * @return \stdClass
     */
    public function loadSchema($path)
    {
        $schema = Utils::LoadJsonFromFile($path);

        $hook = function ($uri) {
            return $this->resolveRef($uri);
        };

        //this is the resolution of the $ref thingy with Jval classes
        //resolver can take a Closure parameter to change the $ref value
        $resolver = new Resolver();
        $resolver->setPreFetchHook($hook);
        $walker = new Walker(new Registry(), $resolver);
        $schema = $walker->resolveReferences($schema, new Uri(''));

        return $walker->parseSchema($schema, new Context());
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public function getSampleDirectory($class)
    {
        $serializer = $this->get($class);

        if (method_exists($serializer, 'getSamples')) {
            $url = $serializer->getSamples();
            $path = explode('/', $url);

            return $this->projectDir.'/src/'
              .$path[1].'/'.$path[2].'/Resources/samples/'.$path[3];
        }

        return null;
    }

    /**
     * Converts distant schema URI to a local one to load schemas from source code.
     *
     * @param string $uri
     *
     * @return string mixed
     */
    private function resolveRef($uri)
    {
        $uri = str_replace($this->baseUri, '', $uri);
        $schemaDir = realpath("{$this->projectDir}/src");

        return $schemaDir.'/'.$uri;
    }
}
