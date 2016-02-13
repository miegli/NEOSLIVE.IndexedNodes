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
     * @param array $selection
     * @return \TYPO3\Flow\Persistence\QueryResultInterface The query result
     * @see \TYPO3\Flow\Persistence\QueryInterface::execute()
     */
    public function getFilteredNodes($selection)
    {



        $query = $this->createQuery();
        $nodeMatcherConditions = array();

        // set workspace query
        if (isset($selection['workspace'])) $nodeMatcherConditions[] = $query->equals('nodeData.workspace', $selection['workspace']->getName());

        // set nodetype query
        if (isset($selection['nodetype'])) $nodeMatcherConditions[] = $query->equals('nodeData.nodeType', $selection['nodetype']);


        // set entrypoint query
        if (isset($selection['entryNodes']) && is_array($selection['entryNodes'])) {

            $entrypointConditions = array();

            foreach ($selection['entryNodes'] as $key => $val) {

                if (isset($val['path'])) {

                    if (isset($val['recursive']) == FALSE) $val['recursive'] = FALSE;

                    if (isset($val['recursive']) && $val['recursive'] == TRUE) {
                        $entrypointConditions[] = $query->like('nodeData.path',$val['path'].'%');
                    } else {
                        foreach ($val['childNodes'] as $path => $data) {

                            $entrypointConditions[] = $query->like('nodeData.parentPath',$val['path'].'/'.$path);
                        }


                    }

                } else {
                        $entrypointConditions[] = $query->like('nodeData.parentpath','');
                }


            }


            if (count($entrypointConditions) > 0) {
                $nodeMatcherConditions[] = $query->logicalOr($entrypointConditions);
            }
        }


        // set filter query
        if (isset($selection['nodetype']) && isset($selection['filter']) && is_array($selection['filter'])) {


            foreach ($selection['filter'] as $property => $filter) {

                if (isset($filter['operand'])) {

                    $filterCondition = array();

                    $oProperty = 'valueRaw';
                    $oOperator = 'like';
                    if (isset($filter['type']) == false) $filter['type'] = 'string';
                    if (isset($filter['operator']) == false) $filter['operator'] = 'like';

                    $oIndex = $this->indexService->getOrderingIndex($this->nodeTypeManager->getNodeType($selection['nodetype']), $property);

                    if ($oIndex >= 0) {

                        // filter by indexed nodes
                        $filterCondition[] = $query->equals('indexData.property', $property);

                        switch ($filter['type']) {

                            case 'datetime':
                                $oProperty = 'indexData.valueDateTime';
                                $filter['operand'] = new \DateTime(strtotime($filter['operand']));
                                \typo3\flow\var_dump( $filter['operand']);
                                break;

                            case 'integer':
                                $oProperty = 'indexData.valueInteger';
                                break;

                            default:
                                $oProperty = 'indexData.valueRaw';
                                break;
                        }

                    } else {

                        //try to sort by default nodedata properties
                        switch ($filter['type']) {
                            case 'datetime':
                                $filter['operand'] = $this->calculateDateFromString($filter['operand']);
                                break;
                        }

                        switch (strtolower($property)) {

                            case 'parentpath':
                                $oProperty = 'nodeData.parentPath';
                                break;

                            case 'index':
                                $oProperty = 'nodeData.index';
                                break;

                            case 'sortingindex':
                                $oProperty = 'nodeData.index';
                                break;

                            case 'lastmodificationdatetime':
                                $oProperty = 'nodeData.lastModificationDateTime';
                                break;

                            case 'lastpublicationdatetime':
                                $oProperty = 'nodeData.lastPublicationDateTime';
                                break;

                            case 'creationdatetime':
                                $oProperty = 'nodeData.creationDateTime';
                                break;

                        }


                    }


                    // apply filter condition
                    switch ($filter['operator']) {

                        case 'like':
                            $filterCondition[] = $query->like($oProperty, $filter['operand']."%");
                            break;

                        case 'equals':
                            $filterCondition[] = $query->equals($oProperty, $filter['operand']);
                            break;

                        case 'greaterThanOrEqual':
                            $filterCondition[] = $query->greaterThanOrEqual($oProperty, $filter['operand']);
                            break;

                        case 'greaterThan':
                            $filterCondition[] = $query->greaterThan($oProperty, $filter['operand']);
                            break;

                        case 'lessThan':
                            $filterCondition[] = $query->lessThan($oProperty, $filter['operand']);
                            break;

                        case 'lessThanOrEqual':
                            $filterCondition[] = $query->lessThanOrEqual($oProperty, $filter['operand']);
                            break;

                        case 'in':
                            $filterCondition[] = $query->in($oProperty, $filter['operand']);
                            break;

                        case 'notequal':
                            $filterCondition[] = $query->logicalNot($query->matching($oProperty, $filter['operand']));
                            break;

                    }

                    if (count($filterCondition) > 0) $nodeMatcherConditions[] = $query->logicalAnd($filterCondition);

                }

            }




            // set orderings
            $orderingArray = array();
            if (isset($selection['nodetype']) && isset($selection['sort']) ) {



                foreach ($selection['sort'] as $propertyName => $ordering) {


                    $oIndex = $this->indexService->getOrderingIndex($this->nodeTypeManager->getNodeType($selection['nodetype']), $ordering['value']);
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
                        switch (strtolower($ordering['value'])) {

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



        }


        // set node matcher
        if (count($nodeMatcherConditions) > 0) $query->matching($query->logicalAnd($nodeMatcherConditions));


        // set limit
        if (isset($selection['limit'])) $query->setLimit($selection['limit']);


        // set offset
        if (isset($selection['offset'])) $query->setOffset($selection['offset']);



        return $query->execute();



}




    /**
     * @param string $shortValue
     * @return \DateTime
     */
    public function calculateDateFromString($shortValue)
    {

        preg_match("/([0-9]{2})\.([0-9]{2})\.([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})|([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})|([0-9]{2})\.([0-9]{2})\.([0-9]{4}) ([0-9]{2}):([0-9]{2})|([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2})|([0-9]{2})\.([0-9]{2})\.([0-9]{4})|([0-9]{4})-([0-9]{2})-([0-9]{2})/", $shortValue, $matches);

        $date = new \DateTime();
        $match = false;

        // dd.mm.YYY hh:ii:ss
        if ((isset($matches[6]) && $matches[1] && $matches[2] && $matches[3] && $matches[4] && $matches[5] && $matches[6])) {
            $date->setDate(intval($matches[3]), intval($matches[2]), intval($matches[1]));
            $date->setTime(intval($matches[4]), intval($matches[5]), intval($matches[6]));
            $match = true;
        }
        // dd.mm.YYY hh:ii
        if ($match === false && (isset($matches[17]) && $matches[13] && $matches[14] && $matches[15] && $matches[16] && $matches[17])) {
            $date->setDate(intval($matches[15]), intval($matches[14]), intval($matches[13]));
            $date->setTime(intval($matches[16]), intval($matches[17]));
            $match = true;
        }
        // dd.mm.YYYY
        if ($match === false && (isset($matches[23]) && $matches[23] && $matches[24] && $matches[25])) {
            $date->setDate(intval($matches[25]), intval($matches[24]), intval($matches[23]));
            $match = true;
        }
        // YYYY-mm-dd
        if ($match === false && (isset($matches[26]) && $matches[26] && $matches[27] && $matches[28])) {
            $date->setDate(intval($matches[26]), intval($matches[27]), intval($matches[28]));
            $match = true;
        }

        // yyyy-mm-dd hh:ii:ss || yyyy-mm-dd hh:ii
        if ($match === false && (isset($matches[22]) && $matches[18] && $matches[19] && $matches[20] && $matches[21] && $matches[22]) | (isset($matches[12]) && $matches[7] && $matches[8] && $matches[9] && $matches[10] && $matches[11] && $matches[12])) {
            $date->setTimestamp(strtotime($matches[0]));
            $match = true;
        }

        if ($match === false) {
            $ts = strtotime($shortValue);
            $date->setTimestamp($ts);

        }

        return $date;
    }



}
