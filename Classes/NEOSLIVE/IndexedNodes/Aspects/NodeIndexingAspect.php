<?php

namespace NEOSLIVE\IndexedNodes\Aspects;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use NEOSLIVE\IndexedNodes\Domain\Service\IndexService;



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


            $nodeData = $joinPoint->getMethodArgument('object');

            if ($nodeData && $nodeData->getNodeType()->getConfiguration('indexedNodes')) {

                foreach ($nodeData->getNodeType()->getConfiguration('indexedNodes') as $propertyKey => $propertyVal) {


                    $this->indexService->setIndexValue($nodeData,$propertyKey);



                }

            }


    }


}

