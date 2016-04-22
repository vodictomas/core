<?php

/**
 * @table  animal
 * @entity AnimalEntity
 */
class AnimalRepository extends \Kravcik\Core\Repository
{
    /**
     * @param  NdbContext $context
     */
    public function __construct(\Nette\Database\Context $context)
    {
        parent::__construct($context);
    }
}
