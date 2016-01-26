<?php
namespace NEOSLIVE\IndexedNodes\Domain\Model;

/*
 * This file is part of the NEOSLIVE.IndexedNodes package.
 */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use NEOSLIVE\IndexedNodes\Domain\Model\IndexData;


/**
 * @Flow\Entity
 */
class Index
{

    /**
     * @ORM\ManyToOne(inversedBy="dimensions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var \TYPO3\TYPO3CR\Domain\Model\NodeData
     */
    protected $nodeData;


    /**
     * @ORM\ManyToMany(inversedBy="data",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var \Doctrine\Common\Collections\Collection<\NEOSLIVE\IndexedNodes\Domain\Model\IndexData>
     */
    protected $indexData;



    /**
     * @param NodeData $nodeData
     */
    public function __construct(NodeData $nodeData)
    {
        $this->nodeData = $nodeData;
        $this->indexData = new \Doctrine\Common\Collections\ArrayCollection();

    }

    /**
     * @param \TYPO3\TYPO3CR\Domain\Model\NodeData $nodeData
     */
    public function setNodeData($nodeData)
    {
        $this->nodeData = $nodeData;
    }


    /**
     * Adds a index data
     *
     * @param IndexData $indexData
     * @return void
     */
    public function addIndexData(IndexData $indexData) {
        $this->indexData->add($indexData);
    }


    /**
     * @return \TYPO3\TYPO3CR\Domain\Model\NodeData
     */
    public function getNodeData()
    {
        return $this->nodeData;
    }


    /**
     * @param $property
     * @param $value
     * @return void
     */
    public function setIndexData($property,$value)
    {


        $indexData = $this->getIndexDataOrCreate($property);
        $indexData->setValue($value);


    }


    /**
     * @param $property
     * @return IndexData
     */
    public function getIndexDataOrCreate($property)
    {


        foreach ($this->indexData as $indexData) {

            if ($indexData->getProperty() == $property) {
                return $indexData;
            }

        }

        $indexData = new IndexData();
        $indexData->setProperty($property);
        $this->addIndexData($indexData);
        return $indexData;



    }





}
