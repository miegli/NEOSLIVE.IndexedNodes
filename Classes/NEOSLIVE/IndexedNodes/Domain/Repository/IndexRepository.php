<?php
namespace NEOSLIVE\IndexedNodes\Domain\Repository;

/*
 * This file is part of the NEOSLIVE.IndexedNodes package.
 */

use NEOSLIVE\IndexedNodes\Domain\Service\IndexService;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use NEOSLIVE\IndexedNodes\Domain\Model\Index;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;

/**
 * @Flow\Scope("singleton")
 */
class IndexRepository extends Repository
{


    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var IndexService
     */
    protected $indexService;


    /**
     * Finds nodes index by its nodedata
     *
     *
     * @param string $parentPath Absolute path of the parent node
     * @param NodeData $nodeData
     * @return Index
     */
    public function getByNodeDataOrCreate(NodeData $nodeData)
    {


        $query = $this->createQuery();

        $result = $query->matching(
            $query->logicalAnd(
                $query->equals('nodeData', $nodeData)
            )

        )->execute();


        if ($result->count()) return $result->getFirst();


        $index = new Index($nodeData);
        $this->add($index);
        $this->persistenceManager->persistAll();

        return $index;


    }


    /**
     *
     *
     * Finds nodes index by its nodedata
     *
     *
     * @param string $parentPath Absolute path of the parent node
     * @param NodeData $nodeData
     * @return mixed
     */
    public function getByNodeData(NodeData $nodeData)
    {


        $query = $this->createQuery();

        $result = $query->matching(
            $query->logicalAnd(
                $query->equals('nodeData', $nodeData)
            )

        )->execute();


        if ($result->count()) return $result->getFirst();


        return null;


    }


    /**
     *
     * Finds nodes index by its nodedata type
     *
     *
     * @param string $nodetype
     * @return mixed
     */
    public function getByNodeDataType($nodetype)
    {


        $query = $this->createQuery();

        $result = $query->matching(
            $query->logicalAnd(
                $query->equals('nodeData.nodeType', $nodetype)
            )

        )->execute();


        return $result;


    }


    /**
     * Finds nodes index by its filter
     *
     *
     * @param array $nodetypes
     * @param array $filters
     * @param array $orderBy
     * @param int $limit
     * @return \TYPO3\Flow\Persistence\QueryResultInterface The query result
     * @see \TYPO3\Flow\Persistence\QueryInterface::execute()
     */
    public function getFilteredNodes($nodetypes, $filters, $orderBy, $limit, $workspace)
    {


        $query = $this->createQuery();


        $nodetypesMatcherConditions = array();

        foreach ($nodetypes as $k => $v) {
            $nodetypesMatcherConditions[] = $query->equals('nodeData.nodeType', $v);
        }


        $filterMatcherConditions = array();

        foreach ($filters as $itemKey => $v) {

            foreach ($v as $k => $itemValue) {
                $filterMatcherConditions[] = $query->logicalAnd(
                    $query->equals('indexData.property', $itemKey),
                    $query->like('indexData.valueRaw', $itemValue)
                );
            }


        }


        $query->matching(
            $query->logicalAnd(

                $query->equals('nodeData.workspace', $workspace->getName()),
                $query->logicalOr($nodetypesMatcherConditions),
                $query->logicalAnd($filterMatcherConditions)
            )
        );


        $orderingArray = array();
        if (count($nodetypes) === 1) {


            $nodeTypeName = $nodetypes[0];


            foreach ($orderBy as $propertyName => $ordering) {


                $oIndex = $this->indexService->getOrderingIndex($this->nodeTypeManager->getNodeType($nodeTypeName), $propertyName);
                if (isset($ordering['direction']) && $ordering['direction'] == QueryInterface::ORDER_DESCENDING) {
                    $oDirection = QueryInterface::ORDER_DESCENDING;
                } else {
                    $oDirection = QueryInterface::ORDER_ASCENDING;
                }


                if ($oIndex >= 0) {


                    if (isset($ordering['type']) == false) $ordering['type'] = 'string';

                    switch ($ordering['type']) {

                        case 'datetime':
                            $oProperty = 'valueDateTime';
                            break;

                        case 'integer':
                            $oProperty = 'valueInteger';
                            break;

                        default:
                            $oProperty = 'valueRaw';
                            break;


                    }


                    $orderingArray['orderIndex' . $oIndex . '.' . $oProperty] = $oDirection;

                } else {


                    // try to sort by default nodedata properties
                    switch (strtolower($propertyName)) {

                        case 'parentpath':
                            $orderingArray['nodeData.parentPath'] = $oDirection;
                            break;

                        case 'index':
                            $orderingArray['nodeData.index'] = $oDirection;
                            break;

                        case 'sortingindex':
                            $orderingArray['nodeData.index'] = $oDirection;
                            break;

                        case 'lastmodificationdatetime':
                            $orderingArray['nodeData.lastModificationDateTime'] = $oDirection;
                            break;

                        case 'lastpublicationdatetime':
                            $orderingArray['nodeData.lastPublicationDateTime'] = $oDirection;
                            break;

                        case 'creationdatetime':
                            $orderingArray['nodeData.creationDateTime'] = $oDirection;
                            break;

                        default:

                            break;

                    }


                }
            }


        }


        if (count($orderingArray)) {
            $query->setOrderings($orderingArray);
        }


        if ($limit) $query->setLimit($limit);


        return $query->execute();


    }


}
