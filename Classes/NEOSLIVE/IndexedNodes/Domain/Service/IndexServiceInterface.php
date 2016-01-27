<?php
namespace NEOSLIVE\IndexedNodes\Domain\Service;

/*
 * This file is part of the NEOSLIVE.IndexedNodes package.
 *
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Domain\Model\Node;

/**
 * Provides generic methods to manage and work with Nodes Index
 *
 * @api
 */
interface IndexServiceInterface
{

    /**
     * Sets node index property values on the given node.
     *
     * @param NodeData $nodeData
     * @param string $propertyname
     * @return void
     */
    public function setIndexValue(NodeData $nodeData,$propertyname);



    /**
     * Remove node index on the given nodedata.
     *
     * @param NodeData $nodeData
     * @return void
     */
    public function removeIndex(NodeData $nodeData);


    /**
     * Get indexed nodes by given node and its filters
     *
     * @param Node $node
     * @return array
     */
    public function getNodes(Node $node);



}
