<?php

namespace ImageProcess;

abstract class Filter
{
    public $name;
    /**
     * @var ImageObject
     */
    public $incomingObject;
    /**
     * @var ImageObject
     */
    public $incomingObject2;
    /**
     * @var ImageObject
     */
    public $outgoingObject;

    public function __construct($name, $parametersString = null)
    {
        if (!is_null($parametersString)) {
            $this->importParametersArray($parametersString);
        }
        $this->name = $name;
    }

    public function startProcess($imageProcess)
    {
        $this->incomingObject->prepareGDResource();
        $this->incomingObject2->prepareGDResource();
        $resultObject = $this->processObject($imageProcess, $this->incomingObject, $this->incomingObject2);
        $this->outgoingObject->GDResource = $resultObject->GDResource;
        $this->outgoingObject->updateStatus();
    }

    abstract protected function processObject(
        ImageProcess $imageProcess,
        ImageObject $object1,
        ImageObject $object2 = null
    );

    public function importParametersArray($parametersString)
    {
        $parameters = explode(',', $parametersString);
        foreach ($parameters as $parameter) {
            $arguments = explode('=', $parameter);
            $variable = trim($arguments[0]);
            $value = trim($arguments[1]);

            $this->$variable = $value;
        }
    }
}