<?php
declare(strict_types=1);

namespace ImageProcess;

abstract class Filter
{
    public string $name;
    public ImageObject $incomingObject;
    public ImageObject $incomingObject2;
    public ImageObject $outgoingObject;

    public function __construct(string $name, ?array $parameters = null)
    {
        if ($parameters !== null) {
            $this->importParametersArray($parameters);
        }
        $this->name = $name;
    }

    protected function importParametersArray(array $parameters): void
    {
        foreach ($parameters as $variable => $value) {
            $this->$variable = $value;
        }
    }

    public function startProcess($imageProcess): void
    {
        if ($this->incomingObject->getGDResource()) {
            $resultObject = $this->processObject($imageProcess, $this->incomingObject, $this->incomingObject2);
            if ($resultObject !== null){
                $this->outgoingObject->setGDResource($resultObject->getGDResource());
            }
        }
    }

    abstract protected function processObject(
        ImageProcess $imageProcess,
        ImageObject  $object1,
        ?ImageObject $object2 = null,
    ): ?imageObject;
}