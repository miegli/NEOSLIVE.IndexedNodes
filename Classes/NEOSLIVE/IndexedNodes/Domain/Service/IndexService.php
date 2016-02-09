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
use TYPO3\TYPO3CR\Domain\Model\Node;
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


        $orderingIndex = $this->getOrderingIndex($nodeData,$propertyname);
        $index = $this->indexRepository->getByNodeDataOrCreate($nodeData);
        $indexData = $index->setIndexData($propertyname,$nodeData->getProperty($propertyname));
        if ($orderingIndex >= 0) {
            if ($index->setOrderIndex($orderingIndex,$indexData,$this->getOrderingHash($nodeData))) {
                // ordering index hash is still valide
            } else {
                // ordering index hash is not valide anymore, please update all index by given node
                $this->reIndexAll($nodeData);

            }
        }

        $this->indexRepository->update($index);



    }



    /**
     * re-index all node data
     *
     * @param NodeData $nodeData
     * @return void
     */
    public function reIndexAll(NodeData $nodeData) {


        $nodes = $this->indexRepository->getByNodeDataType($nodeData->getNodeType()->getName());



        foreach ($nodes as $nodeIndex) {


            for ($i=0;$i<10;$i++) {
                $nodeIndex->clearOrderIndex($i);
            }

            $indexes = $this->getOrderingIndexAll($nodeData);
            foreach ($indexes as $key => $propertyName) {
               $nodeIndex->setOrderIndex($key,$nodeIndex->getIndexDataOrCreate($propertyName),$this->getOrderingHash($nodeData));
            }

            $this->indexRepository->update($nodeIndex);

        }


    }


    /**
     * Remove node index on the given nodedata.
     *
     * @param NodeData $nodeData
     * @return void
     */
    public function removeIndex(NodeData $nodeData) {


        $index = $this->indexRepository->getByNodeData($nodeData);

        if ($index) $this->indexRepository->remove($index);



    }



    /**
     * Get properties ordering index
     *
     * @param mixed $nodeData
     * @param string $propertyname
     * @return integer
     */
    public function getOrderingIndex($nodeData,$propertyname) {

        if ($nodeData instanceof nodeData) $nodeType = $nodeData->getNodeType();
        if ($nodeData instanceof nodeType) $nodeType = $nodeData;

        $i = 0;
        foreach ($nodeType->getConfiguration('indexedNodes')['properties'] as $key => $val) {
            if ($key == $propertyname) {
                if ($i<10) return $i;
            }
            $i++;
        }

        return -1;

    }


    /**
     * Get properties ordering index
     *
     * @param mixed $nodeData
     * @return integer
     */
    public function getOrderingIndexAll($nodeData) {


        if ($nodeData instanceof nodeData) $nodeType = $nodeData->getNodeType();
        if ($nodeData instanceof nodeType) $nodeType = $nodeData;


        $data = array();
        $i=0;
        foreach ($nodeType->getConfiguration('indexedNodes')['properties'] as $key => $val) {
                if ($i<10) $data[] = $key;
            $i++;
        }


        return $data;

    }




    /**
     * Get properties ordering hash
     *
     * @param NodeData $nodeData
     * @return string
     */
    public function getOrderingHash(NodeData $nodeData) {

        $data = '';
        foreach ($nodeData->getNodeType()->getConfiguration('indexedNodes')['properties'] as $key => $val) {
            $data .= $key.":";
        }

        return md5($data);

    }


    /**
     * Get indexed nodes by given node and its filters
     *
     * @param Node $basenode
     * @return array
     */
    public function getNodes(Node $basenode) {


        $limit = false;
        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['limit'])) {

            foreach ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['limit'] as $limitProperty => $limitValues) {

                foreach ($limitValues as $limitValueType => $limitValue) {
                    switch ($limitValueType) {

                        case 'property':
                            if ($basenode->getProperty($limitValue)) $limit = $basenode->getProperty($limitValue);
                            break;

                        case 'value':
                            $limit = $limitValue;
                            break;

                        default:
                            break;
                    }

                }

            }

        }
        
        
        $filters = array();

        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['filteredProperties'])) {

            foreach ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['filteredProperties'] as $filteredProperty => $filterValues) {

                foreach ($filterValues as $filterValueType => $filterValue) {
                    switch ($filterValueType) {

                        case 'property':
                           if ($basenode->getProperty($filterValue)) $filters[$filteredProperty][] = $basenode->getProperty($filterValue);
                        break;

                        case 'value':
                           $filters[$filteredProperty][] = $filterValue;
                        break;

                        default:
                        break;
                    }

                }


            }

        }



        $orderBy = array();

        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['orderedByProperties'])) {

            foreach ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['orderedByProperties'] as $orderedByProperty => $orderedByValues) {


                foreach ($orderedByValues as $orderedByValueType => $orderedByValue) {
                    switch ($orderedByValueType) {

                        case 'property':
                            if ($basenode->getProperty($orderedByValue)) $orderBy[$orderedByProperty]['value'] = $basenode->getProperty($orderedByValue);
                            break;

                        case 'value':
                            $orderBy[$orderedByProperty]['value'] = $orderedByValue;
                            break;

                        case 'type':
                            $orderBy[$orderedByProperty]['type'] = $orderedByValue;
                            break;

                        case 'direction':
                            $orderBy[$orderedByProperty]['direction'] = $orderedByValue;
                            break;

                        default:
                            break;
                    }


                }




            }

        }




        $nodeTypes = array();

        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['nodeTypes'])) {

            foreach ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['nodeTypes'] as $nodeType => $nodeTypeValue) {
                $nodeTypes[] = $nodeType;
            }

        }




       return $this->indexRepository->getFilteredNodes(
           $nodeTypes,
           $filters,
           $orderBy,
           $limit,
           $basenode->getWorkspace()
       );


    }



}
