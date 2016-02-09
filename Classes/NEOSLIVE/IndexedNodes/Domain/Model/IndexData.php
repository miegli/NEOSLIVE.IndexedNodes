<?php
namespace NEOSLIVE\IndexedNodes\Domain\Model;

/*
 * This file is part of the NEOSLIVE.IndexedNodes package.
 */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * @Flow\Entity
 */
class IndexData
{


    /**
     * Index property name
     *
     * @var string
     * @ORM\Column(nullable=false)
     */
    protected $property;

    /**
     * Index property value
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $value;

    /**
     * Index property value
     *
     * @var string
     * @ORM\Column(nullable=true,type="text")
     */
    protected $valueRaw;

    /**
     * Index property value cast as date
     *
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $valueDateTime;


    /**
     * Index property value cast as integer
     *
     * @var Integer
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $valueInteger;




    /**
     * @return \DateTime
     */
    public function getValueDateTime()
    {
        return $this->valueDateTime;
    }

    /**
     * @param \DateTime $valueDateTime
     */
    public function setValueDateTime($valueDateTime)
    {
        $this->valueDateTime = $valueDateTime;
    }

    /**
     * @return int
     */
    public function getValueInteger()
    {
        return $this->valueInteger;
    }

    /**
     * @param int $valueInteger
     */
    public function setValueInteger($valueInteger)
    {
        $this->valueInteger = $valueInteger;
    }





    /**
     * @return string
     */
    public function getValueRaw()
    {
        return $this->valueRaw;
    }

    /**
     * @param string $valueRaw
     */
    public function setValueRaw($valueRaw)
    {
        $this->valueRaw = $valueRaw;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }




}
