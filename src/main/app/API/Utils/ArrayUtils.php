<?php

namespace Claroline\AppBundle\API\Utils;

class ArrayUtils
{
    /**
     * This is more or less the equivalent of lodash set for array.
     *
     * @param string $keys - the property path
     * @param $value
     *
     * @throws \Exception
     */
    public static function set(array &$object, $keys, $value)
    {
        $keys = explode('.', $keys);
        $depth = count($keys);
        $key = array_shift($keys);

        if (1 === $depth) {
            $object[$key] = $value;
        } else {
            if (!isset($object[$key])) {
                $object[$key] = [];
            } elseif (!is_array($object[$key])) {
                throw new \Exception('Cannot set property because it already exists as a non \stdClass');
            }

            static::set($object[$key], implode('.', $keys), $value);
        }
    }

    public static function remove(array &$object, $keys)
    {
        // because sometimes there are keys with dot in it (eg. Scorm props cmi.*)
        // we check the whole key exist before starting the recursive search
        if (isset($object[$keys])) {
            unset($object[$keys]);
        }

        $keys = explode('.', $keys);
        $depth = count($keys);
        $key = array_shift($keys);

        if (1 === $depth) {
            unset($object[$key]);
        } else {
            if (isset($object[$key])) {
                static::remove($object[$key], implode('.', $keys));
            }
        }
    }

    /**
     * This is more or less the equivalent of lodash get for array.
     *
     * @param array  $object  - the array
     * @param string $keys    - the property path
     * @param mixed  $default
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function get(array $object, $keys, $default = null)
    {
        // because sometimes there are keys with dot in it (eg. Scorm props cmi.*)
        // we check the whole key exist before starting the recursive search
        if (array_key_exists($keys, $object)) {
            if (isset($object[$keys])) {
                return $object[$keys];
            }

            return $default;
        }

        $parts = explode('.', $keys);
        $key = array_shift($parts);

        if (isset($object[$key])) {
            if (!empty($parts) && is_array($object[$key])) {
                return static::get($object[$key], implode('.', $parts), $default);
            }

            return $object[$key];
        }

        if (array_key_exists($key, $object)) {
            return $default;
        }

        throw new \Exception("Key `{$keys}` doesn't exist for array keys [".implode(',', array_keys($object)).']');
    }

    /**
     * @param array  $object - the array
     * @param string $keys   - the property path
     *
     * @return mixed
     */
    public static function has(array $object, $keys)
    {
        try {
            static::get($object, $keys);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getPropertiesName(array $object, $titles = [], $currentPos = null)
    {
        $keys = array_keys($object);

        foreach ($keys as $key) {
            if (is_array($object[$key]) && is_string($key)) {
                if (is_int($key)) {
                    $newPos = null;
                } else {
                    if (null === $currentPos) {
                        $newPos = $key;
                    } else {
                        $newPos = $currentPos.'.'.$key;
                    }
                }
                $titles = static::getPropertiesName($object[$key], $titles, is_int($key) ? null : $newPos);
            } else {
                if (is_string($key)) {
                    $displayName = $currentPos ? $currentPos.'.'.$key : $key;
                    $titles[] = $displayName;
                }
            }
        }

        return $titles;
    }
}
