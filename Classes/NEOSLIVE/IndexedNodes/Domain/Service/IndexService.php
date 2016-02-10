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
use TYPO3\TYPO3CR\Domain\Model\Workspace;
use NEOSLIVE\IndexedNodes\Domain\Model\IndexData;
use NEOSLIVE\IndexedNodes\Domain\Repository\IndexRepository;
use NEOSLIVE\IndexedNodes\Domain\Repository\IndexDataRepository;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use TYPO3\Flow\Http\Request;


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
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;


    /**
     * @var Request
     */
    protected $httpRequest;


    /**
     * @var Workspace
     */
    private $workspace;


    /**
     * IndexService constructor.
     * @param Request $httpRequest
     */
    public function __construct()
    {
        $this->httpRequest = new Request($_GET, $_POST, $_FILES, $_SERVER);
        //$this->workspace = ;
    }


    /**
     * Sets node index property values on the given nodedata.
     *
     * @param NodeData $nodeData
     * @param string $propertyname
     * @param string $propertyvalue
     * @return void
     */
    public function setIndexValue(NodeData $nodeData, $propertyname)
    {


        $orderingIndex = $this->getOrderingIndex($nodeData, $propertyname);
        $index = $this->indexRepository->getByNodeDataOrCreate($nodeData);
        $indexData = $index->setIndexData($propertyname, $nodeData->getProperty($propertyname));
        if ($orderingIndex >= 0) {
            if ($index->setOrderIndex($orderingIndex, $indexData, $this->getOrderingHash($nodeData))) {
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
    public function reIndexAll(NodeData $nodeData)
    {


        $nodes = $this->indexRepository->getByNodeDataType($nodeData->getNodeType()->getName());


        foreach ($nodes as $nodeIndex) {


            for ($i = 0; $i < 10; $i++) {
                $nodeIndex->clearOrderIndex($i);
            }

            $indexes = $this->getOrderingIndexAll($nodeData);
            foreach ($indexes as $key => $propertyName) {
                $nodeIndex->setOrderIndex($key, $nodeIndex->getIndexDataOrCreate($propertyName), $this->getOrderingHash($nodeData));
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
    public function removeIndex(NodeData $nodeData)
    {


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
    public function getOrderingIndex($nodeData, $propertyname)
    {

        if ($nodeData instanceof nodeData) $nodeType = $nodeData->getNodeType();
        if ($nodeData instanceof nodeType) $nodeType = $nodeData;


        if ($nodeType->getConfiguration('indexedNodes') == NULL) return -2;

        $i = 0;
        foreach ($nodeType->getConfiguration('indexedNodes')['properties'] as $key => $val) {
            if ($key == $propertyname) {
                if ($i < 10) return $i;
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
    public function getOrderingIndexAll($nodeData)
    {


        if ($nodeData instanceof nodeData) $nodeType = $nodeData->getNodeType();
        if ($nodeData instanceof nodeType) $nodeType = $nodeData;


        $data = array();
        $i = 0;
        foreach ($nodeType->getConfiguration('indexedNodes')['properties'] as $key => $val) {
            if ($i < 10) $data[] = $key;
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
    public function getOrderingHash(NodeData $nodeData)
    {

        $data = '';
        foreach ($nodeData->getNodeType()->getConfiguration('indexedNodes')['properties'] as $key => $val) {
            $data .= $key . ":";
        }

        return md5($data);

    }


    /**
     * Get array of node selection properties
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * @param Node $node
     * @return array
     */
    public function prepareNodeSelectionFromNode(Node $node)
    {

        $this->workspace = $node->getWorkspace();


        $limit = false;
        $offset = false;
        $filter = array();
        $sort = array();
        $nodetype = false;
        $entryNodes = array();


        if ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')) {


            // calculate nodetype name
            if ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['nodeType'])) {
                foreach ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['nodeType'] as $key => $value) {
                    switch ($key) {
                        case 'property':
                            if ($node->getProperty($value)) $nodetype = $node->getProperty($value);
                            break;
                        case 'value':
                            $nodetype = $value;
                            break;
                        case 'param':
                            if ($this->httpRequest->hasArgument($value) || $nodetype == false) $nodetype = addslashes($this->httpRequest->getArgument($value));
                            break;
                        default:
                            break;
                    }
                }
            }


            // calculate limit
            if ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['limit'])) {
                foreach ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['limit'] as $key => $value) {
                    switch ($key) {
                        case 'property':
                            if ($node->getProperty($value)) $limit = $node->getProperty($value);
                            break;
                        case 'value':
                            $limit = $value;
                            break;
                        case 'param':
                            if ($this->httpRequest->hasArgument($value) || $limit == false) $limit = addslashes($this->httpRequest->getArgument($value));
                            break;
                        default:
                            break;
                    }
                }
            }

            // calculate limit offset, if limit isset
            if ($limit && $node->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['offset'])) {
                foreach ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['offset'] as $key => $value) {
                    switch ($key) {
                        case 'property':
                            if ($node->getProperty($value)) $offset = "," . $node->getProperty($value);
                            break;
                        case 'value':
                            $offset = $value;
                            break;
                        case 'param':
                            if ($this->httpRequest->hasArgument($value) || $offset == false) $offset = addslashes($this->httpRequest->getArgument($value));
                            break;
                        default:
                            break;
                    }
                }


            }


            // calculate filters
            if ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['filter'])) {

                foreach ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['filter'] as $property => $arguments) {


                    foreach ($arguments as $arg => $filterValues) {



                        switch ($arg) {

                            case 'type':
                                $filter[$property]['type'] = $filterValues;
                                break;

                            case 'operand':

                                foreach ($filterValues as $key => $value) {
                                    switch ($key) {
                                        case 'property':
                                            if ($node->getProperty($value)) $filter[$property]['operand'] = $node->getProperty($value);
                                            break;
                                        case 'value':
                                            $filter[$property]['operand'] = $value;
                                            break;
                                        case 'param':
                                            if ($this->httpRequest->hasArgument($value) || isset($filter[$property]['operand']) == false) $filter[$property]['operand'] = addslashes($this->httpRequest->getArgument($value));
                                            break;

                                        default:
                                            break;
                                    }

                                }
                                break;


                             case 'operator':

                                foreach ($filterValues as $key => $value) {
                                    switch ($key) {
                                        case 'property':
                                            if ($node->getProperty($value)) $filter[$property]['operator'] = $node->getProperty($value);
                                            break;
                                        case 'value':
                                            $filter[$property]['operator'] = $value;
                                            break;
                                        case 'param':
                                            if ($this->httpRequest->hasArgument($value) || isset($filter[$property]['operator']) == false) $filter[$property]['operator'] = addslashes($this->httpRequest->getArgument($value));
                                            break;

                                        default:
                                            break;
                                    }

                                }
                                break;



                        }


                    }




                    if (isset($filter[$property]['type']) == false) {

                        $targetNodeType = $this->nodeTypeManager->getNodeType($nodetype);

                        // get sorting type by property definition
                        if (isset($targetNodeType->getConfiguration('properties')['text'])) {
                            $filter[$property]['type'] = $targetNodeType->getConfiguration('properties')['text']['type'];
                        } else {
                            $filter[$property]['type'] = 'string';
                        }
                    }


                }


            }

            // calculate entry nodes
            if ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['entryNodes'])) {

                foreach ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['entryNodes'] as $property => $filterValues) {


                    foreach ($filterValues as $key => $value) {
                        switch ($key) {
                            case 'property':
                                if ($node->getProperty($value)) $entryNodes[$property]['value'] = $node->getProperty($value);
                                break;
                            case 'value':
                                $entryNodes[$property]['value'] = $value;
                                break;
                            case 'param':
                                if ($this->httpRequest->hasArgument($value) || isset($entryNodes[$property]['value']) == false) $entryNodes[$property]['value'] = addslashes($this->httpRequest->getArgument($value));
                                break;
                            case 'recursive':
                                $entryNodes[$property]['recursive'] = $value;
                                break;
                        }

                        if (isset($entryNodes[$property]['recursive']) == false) $entryNodes[$property]['recursive'] = TRUE;

                        if (isset($entryNodes[$property]['value'])) {
                            $targetNode = $this->nodeDataRepository->findOneByIdentifier($entryNodes[$property]['value'], $this->workspace);
                            if ($targetNode) {
                                $entryNodes[$property]['path'] = $targetNode->getParentPath();
                            }
                        }


                    }


                }

            }


            // calculate sorting
            if ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes') && ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['sort'])) {

                foreach ($node->getNodeData()->getNodeType()->getConfiguration('indexedNodes')['sort'] as $nullkey => $sortValues) {

                    foreach ($sortValues as $key => $value) {


                        switch ($key) {
                            case 'property':
                                if ($node->getProperty($value)) $sort[$property]['value'] = $node->getProperty($value);
                                break;
                            case 'value':
                                $sort[$property]['value'] = $value;
                                break;
                            case 'param':
                                if ($this->httpRequest->hasArgument($value) || isset($sort[$property]) == false) $sort[$property]['value'] = addslashes($this->httpRequest->getArgument($value));
                                break;
                            case 'type':
                                $sort[$property]['type'] = $value;
                                break;

                            case 'direction':

                                foreach ($value as $k => $v) {

                                    switch ($k) {

                                        case 'property':
                                            if ($node->getProperty($v)) $sort[$property]['direction'] = $node->getProperty($v);
                                            break;
                                        case 'value':
                                            $sort[$property]['direction'] = $v;
                                            break;
                                        case 'param':
                                            if ($this->httpRequest->hasArgument($v) || isset($sort[$property]['direction']) == false) $sort[$property]['direction'] = addslashes($this->httpRequest->getArgument($v));
                                            break;
                                    }

                                }

                                break;

                            default:
                                break;
                        }




                        if (isset($sort[$property]['type']) == false) {

                            $targetNodeType = $this->nodeTypeManager->getNodeType($nodetype);

                            // get sorting type by property definition
                            if (isset($targetNodeType->getConfiguration('properties')['text'])) {
                                $sort[$property]['type'] = $targetNodeType->getConfiguration('properties')['text']['type'];
                            } else {
                                $sort[$property]['type'] = 'string';
                            }
                        }


                    }

                }

            }


        }


        return array(
            'limit' => $limit,
            'offset' => $offset,
            'filter' => $filter,
            'sort' => $sort,
            'nodetype' => $nodetype,
            'entryNodes' => $entryNodes,
            'workspace' => $this->workspace
        );


    }


    /**
     * Get indexed nodes by given node and its filters
     *
     * @param Node $basenode
     * @return array
     */
    public function getNodes(Node $basenode)
    {

        return $this->indexRepository->getFilteredNodes(
            $this->prepareNodeSelectionFromNode($basenode)
        );


    }


}
