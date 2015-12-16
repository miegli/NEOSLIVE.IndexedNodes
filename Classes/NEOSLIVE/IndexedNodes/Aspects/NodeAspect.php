<?php

namespace NEOSLIVE\IndexedNodes\Aspects;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;


/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class NodeAspect
{

//    /**
//     * @Flow\Around("method(TYPO3\Neos\Service\NodeOperations->create())")
//     * @return void
//     */
//    public function indexNodeAfterCreating(JoinPointInterface $joinPoint)
//    {
//
//        $referenceNode = $joinPoint->getMethodArgument('referenceNode');
//        $newNode = $joinPoint->getAdviceChain()->proceed($joinPoint);
//
//       // \typo3\flow\var_dump($newNode->getIdentifier());
//
//        return $newNode;
//
//
//    }


    /**
     * @Flow\After("method(TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository->update())")
     * @return void
     */
    public function indexNodeBeforeUpdatingNodeData(JoinPointInterface $joinPoint)
    {

        $nodeData = $joinPoint->getMethodArgument('object');

        if ($nodeData->getNodeType()->hasConfiguration('neoslive')) {
            // \typo3\flow\var_dump($nodeData->getNodeType()->getConfiguration('neoslive'));

        }

    }



}

