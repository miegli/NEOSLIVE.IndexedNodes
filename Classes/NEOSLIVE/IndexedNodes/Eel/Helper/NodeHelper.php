<?php

namespace NEOSLIVE\IndexedNodes\Eel\Helper;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Eel\ProtectedContextAwareInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Neos\Domain\Exception;
use TYPO3\TYPO3CR\Domain\Model\Node;
use NEOSLIVE\IndexedNodes\Domain\Service\IndexService;
use TYPO3\TYPO3CR\Domain\Factory\NodeFactory;

class NodeHelper implements ProtectedContextAwareInterface {



    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;


    /**
     * Get indexed nodes
     *
     * @param Node $node
     * @return string
     */
    public function get(Node $node) {

        $nodes = array();

        $indexService = new IndexService();

        $nodesResult = $indexService->getNodes($node);

        foreach ($nodesResult as $identifier => $item) {
            $n = new \TYPO3\TYPO3CR\Domain\Model\Node($item,$node->getContext());
            $nodes[] = $n->getNode('.');
        }

        return $nodes;


    }

    /**
     * Count indexed nodes
     *
     * @param Node $node
     * @return integer
     */
    public function count(Node $node) {


        $indexService = new IndexService();

        return $indexService->countNodes($node);


    }

    /**
     * All methods are considered safe, i.e. can be executed from within Eel
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName) {
        return TRUE;
    }


}