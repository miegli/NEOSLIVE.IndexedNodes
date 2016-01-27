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


        $index = $this->indexRepository->getByNodeDataOrCreate($nodeData);
        $index->setIndexData($propertyname,$nodeData->getProperty($propertyname));

        $this->indexRepository->update($index);



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
     * Get indexed nodes by given node and its filters
     *
     * @param Node $basenode
     * @return array
     */
    public function getNodes(Node $basenode) {


        $limit = false;
        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && isset($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['limit'])) {

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

        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && isset($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['filteredProperties'])) {

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

        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && isset($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['orderedByProperties'])) {

            foreach ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['orderedByProperties'] as $orderedByProperty => $orderedByValues) {



                foreach ($orderedByValues as $orderedByValueType => $orderedByValue) {
                    switch ($orderedByValueType) {

                        case 'property':
                            if ($basenode->getProperty($orderedByValue)) $orderBy[$orderedByProperty]['property'] = $basenode->getProperty($orderedByValue);
                            break;

                        case 'value':
                            $orderBy[$orderedByProperty]['property'] = $orderedByValue;
                            break;

                        default:
                            break;
                    }

                }


            }

        }


        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && isset($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['orderedByDirections'])) {

            foreach ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['orderedByDirections'] as $orderedByProperty => $orderedByValues) {

                foreach ($orderedByValues as $orderedByValueType => $orderedByValue) {
                    switch ($orderedByValueType) {

                        case 'property':
                            if ($basenode->getProperty($orderedByValue)) $orderBy[$orderedByProperty]['direction'] = $basenode->getProperty($orderedByValue);
                            break;

                        case 'value':
                            $orderBy[$orderedByProperty]['direction'] = $orderedByValue;
                            break;

                        default:
                            break;
                    }

                }


            }

        }


        $nodeTypes = array();

        if ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && isset($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['nodeTypes'])) {

            foreach ($basenode->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['nodeTypes'] as $nodeType => $nodeTypeValue) {
                $nodeTypes[] = $nodeType;
            }

        }





       return $this->indexRepository->getFilteredNodes(
           $nodeTypes,
           $filters,
           $orderBy,
           $limit
       );


    }



}
