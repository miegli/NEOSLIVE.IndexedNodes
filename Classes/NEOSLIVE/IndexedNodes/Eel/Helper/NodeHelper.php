<?php

namespace NEOSLIVE\IndexedNodes\Eel\Helper;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Eel\ProtectedContextAwareInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Neos\Domain\Exception;

class NodeHelper implements ProtectedContextAwareInterface {

    /**
     * Wrap the incoming string in curly brackets
     *
     * @param $text string
     * @return string
     */
    public function get($text) {
        return '{' . $text . '}';
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