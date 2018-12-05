<?php

namespace ImageProcess;

abstract class Filter
{
    /**
     * @var string
     */
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

    /**
     * Filter constructor.
     * @param string $name
     * @param string $parametersString
     */
    public function __construct($name, $parametersString = null)
    {
        if ($parametersString !== null) {
            $this->importParametersArray($parametersString);
        }
        $this->name = $name;
    }

    public function startProcess($imageProcess)
    {
        $resultObject = $this->processObject($imageProcess, $this->incomingObject, $this->incomingObject2);
        $this->outgoingObject->setGDResource($resultObject->getGDResource());
    }

    /**
     * @param ImageProcess $imageProcess
     * @param ImageObject $object1
     * @param ImageObject|null $object2
     * @return imageObject
     */
    abstract protected function processObject(
        ImageProcess $imageProcess,
        ImageObject $object1,
        ImageObject $object2 = null
    );

    /**
     * @param string $parametersString
     */
    protected function importParametersArray($parametersString)
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