<?php
/**
 * Created by PhpStorm.
 * User: Piet  Marcus
 * Date: 11-9-2016
 * Time: 19:38
 */

namespace PietMarcus\ModelDocGenerator;


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