<?php

namespace PietMarcus\ModelDocGenerator;

/**
 * Class DocModel
 * @package PietMarcus\ModelDocGenerator
 *
 * Helper object for ModelDocGenerator to store information about models.
 */
class DocModel
{
    /**
     * @var string
     */
    var $classname;

    /**
     * @var string
     */
    var $filename;

    /**
     * @var DocProperty[]
     */
    var $properties;

    /**
     * @var string
     */
    var $phpDoc;

    /**
     * @var string
     */
    var $namespace;

    /**
     * @var string
     */
    var $fullname;
}