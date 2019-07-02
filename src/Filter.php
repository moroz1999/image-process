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
     * @param string $parameters
     */
    public function __construct($name, $parameters = null)
    {
        if ($parameters !== null) {
            $this->importParametersArray($parameters);
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
     * @param string $parameters
     */
    protected function importParametersArray($parameters)
    {
        if (is_array($parameters)) {
            foreach ($parameters as $variable => $value) {
                $this->$variable = $value;
            }
        } else {
            $parameters = explode(',', $parameters);
            foreach ($parameters as $parameter) {
                $arguments = explode('=', $parameter);
                $variable = trim($arguments[0]);
                $value = trim($arguments[1]);

                $this->$variable = $value;
            }
        }

    }
}