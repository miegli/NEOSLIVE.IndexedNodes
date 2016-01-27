<?php

namespace NEOSLIVE\IndexedNodes\Aspects;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use NEOSLIVE\IndexedNodes\Domain\Service\IndexService;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Domain\Model\Node;


/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class NodeIndexingAspect
{


    /**
     * @Flow\Inject
     * @var IndexService
     */
    protected $indexService;


    /**
     * @Flow\After("method(TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository->update())")
     * @return void
     */
    public function indexNodeBeforeUpdatingNodeData(JoinPointInterface $joinPoint)
    {


        $object = $joinPoint->getMethodArgument('object');

        if ($object instanceof NodeData) $nodeData = $object;
        if ($object instanceof Node) $nodeData = $object->getNodeData();


        if ($nodeData instanceof NodeData) {

                if ($nodeData && $nodeData->getNodeType()->getConfiguration('indexedNodes') && isset($nodeData->getNodeType()->getConfiguration('indexedNodes')['properties'])) {

                    // add property to nodedata index
                    foreach ($nodeData->getNodeType()->getConfiguration('indexedNodes')['properties'] as $propertyKey => $propertyVal) {
                        $this->indexService->setIndexValue($nodeData, $propertyKey);
                    }

                }


        }


    }


    /**
     * @Flow\After("method(TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository->remove())")
     * @return void
     */
    public function indexNodeBeforeRemovingNodeData(JoinPointInterface $joinPoint)
    {

        $object = $joinPoint->getMethodArgument('object');

        if ($object instanceof NodeData) $nodeData = $object;
        if ($object instanceof Node) $nodeData = $object->getNodeData();

        if ($nodeData instanceof NodeData) $this->indexService->removeIndex($nodeData);

    }


}

