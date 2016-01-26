<?php
namespace NEOSLIVE\IndexedNodes\Domain\Service;


/*
 * This file is part of the TYPO3.TYPO3CR package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeType;
use TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository;
use TYPO3\TYPO3CR\Domain\Utility\NodePaths;
use TYPO3\TYPO3CR\Exception\NodeExistsException;
use NEOSLIVE\IndexedNodes\Domain\Model\IndexData;
use NEOSLIVE\IndexedNodes\Domain\Repository\IndexRepository;
use NEOSLIVE\IndexedNodes\Domain\Repository\IndexDataRepository;

/**
 * Provide method to manage node
 *
 * @Flow\Scope("singleton")
 * @api
 */
class IndexService implements IndexServiceInterface
{



    /**
     * @Flow\Inject
     * @var IndexRepository
     */
    protected $indexRepository;


    /**
     * @Flow\Inject
     * @var IndexDataRepository
     */
    protected $indexDataRepository;



    /**
     * Sets node index property values on the given nodedata.
     *
     * @param NodeData $nodeData
     * @param string $propertyname
     * @param string $propertyvalue
     * @return void
     */
    public function setIndexValue(NodeData $nodeData,$propertyname) {


        $index = $this->getIndexByNodeData($nodeData);


        $index->setIndexData($propertyname,time());

        $this->indexRepository->update($index);



    }



    /**
     * gets node index by property and dimensionhash
     *
     * @param NodeData $nodeData
     * @return void
     */
    public function getIndexByNodeData(NodeData $nodeData) {


        return $this->indexRepository->getByNodeDataOrCreate($nodeData);


    }



}
