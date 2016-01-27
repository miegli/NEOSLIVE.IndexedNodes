<?php
namespace NEOSLIVE\IndexedNodes\Domain\Repository;

/*
 * This file is part of the NEOSLIVE.IndexedNodes package.
 */

use NEOSLIVE\IndexedNodes\Domain\Service\IndexService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use NEOSLIVE\IndexedNodes\Domain\Model\Index;

/**
 * @Flow\Scope("singleton")
 */
class IndexRepository extends Repository
{

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
                $query->equals('nodeData',$nodeData)
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
                $query->equals('nodeData',$nodeData)
            )

        )->execute();


        if ($result->count()) return $result->getFirst();


        return null;


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
    public function getFilteredNodes($nodetypes,$filters,$orderBy,$limit)
    {



        $query = $this->createQuery();


        $nodetypesMatcherConditions = array();

            foreach ($nodetypes as $k => $v) {
                $nodetypesMatcherConditions[] = $query->equals('nodeData.nodeType',$v);
            }


        $filterMatcherConditions = array();

            foreach ($filters as $itemKey => $v) {

                foreach ($v as $k => $itemValue) {
                    $filterMatcherConditions[] = $query->logicalAnd(
                        $query->equals('indexData.property',$itemKey),
                        $query->like('indexData.valueRaw',$itemValue)
                    );
                }


            }



         $query->matching(
            $query->logicalAnd(
                $query->logicalOr($nodetypesMatcherConditions),
                $query->logicalAnd($filterMatcherConditions)
            )
        );

        if ($limit) $query->setLimit($limit);

        return $query->execute();




    }





}
