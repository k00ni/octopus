<?php

namespace Saft\Data;

/**
 * SerializationUtils class provides helper methods, for instance to convert between MIME-type and serialization.
 *
 * @api
 * @package Saft\Data
 * @since 0.1
 */
class SerializationUtils
{
    /**
     * @var
     */
    protected $serializationMimeTypeMap = array();

    /**
     * Constructor.
     *
     * @api
     * @since 0.1
     */
    public function __construct()
    {
        $this->serializationMimeTypeMap = array(
            'json-ld' => 'application/json',
            'n-quads' => 'application/n-quads',
            'n-triples' => 'application/n-triples',
            'rdf-json' => 'application/json',
            'rdf-xml' => 'application/rdf+xml',
            'rdfa' => 'text/html',
            'trig' => 'application/trig',
            'turtle' => 'text/turtle'
        );
    }

    /**
     * Returns serialization pendant for a given MIME type, if available.
     *
     * @param string $mime MIME-type to transform to the serialization.
     * @return string MIME-Type, if available, null otherwise.
     * @api
     * @since 0.1
     */
    public function mimeToSerialization($mime)
    {
        foreach ($this->serializationMimeTypeMap as $serialization => $mimePendant) {
            if ($mime == $mimePendant) {
                return $serialization;
            }
        }

        return null;
    }

    /**
     * Returns MIME type pendant for a given serialization, if available.
     *
     * @param string $serialization MIME-type to transform to the Serialization.
     * @return string|null MIME-Type, if available, null otherwise.
     * @api
     * @since 0.1
     */
    public function serializationToMime($serialization)
    {
        if (isset($this->serializationMimeTypeMap[$serialization])) {
            return $this->serializationMimeTypeMap[$serialization];
        }

        return null;
    }
}
