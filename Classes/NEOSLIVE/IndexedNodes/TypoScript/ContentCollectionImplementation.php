<?php
namespace NEOSLIVE\IndexedNodes\TypoScript;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\TypoScript\ContentElementWrappingImplementation;


/**
 * Class ContentCollectionImplementation
 * @package NEOSLIVE\IndexedNodes\TypoScript
 */
class ContentCollectionImplementation extends ContentElementWrappingImplementation  {


    /**
     * Evaluate this TypoScript object and return the result
     *
     * @return mixed
     * @throws \TYPO3\Neos\Domain\Exception
     */
    public function evaluate()
    {


        $content = $this->getValue();



        /** @var $node NodeInterface */
        $node = $this->tsValue('node');
        if (!$node instanceof NodeInterface) {
            return $content;
        }

        /** @var $contentContext ContentContext */
        $contentContext = $node->getContext();
        if ($contentContext->getWorkspaceName() === 'live') {
            return $content;
        }

        if (!$this->privilegeManager->isPrivilegeTargetGranted('TYPO3.Neos:Backend.GeneralAccess')) {
            return $content;
        }

        if ($node->isRemoved()) {
            $content = '';
        }
        return $this->contentElementWrappingService->wrapContentObject($node, $this->getContentElementTypoScriptPath(), $content, $this->tsValue('renderCurrentDocumentMetadata'));
    }

    /**
     * Returns the TypoScript path to the wrapped Content Element
     *
     * @return string
     */
    protected function getContentElementTypoScriptPath()
    {
        $typoScriptPathSegments = explode('/', $this->path);
        $numberOfTypoScriptPathSegments = count($typoScriptPathSegments);
        if (isset($typoScriptPathSegments[$numberOfTypoScriptPathSegments - 3])
            && $typoScriptPathSegments[$numberOfTypoScriptPathSegments - 3] === '__meta'
            && isset($typoScriptPathSegments[$numberOfTypoScriptPathSegments - 2])
            && $typoScriptPathSegments[$numberOfTypoScriptPathSegments - 2] === 'process') {

            // cut of the processing segments "__meta/process/contentElementWrapping<TYPO3.Neos:ContentElementWrapping>"
            return implode('/', array_slice($typoScriptPathSegments, 0, -3));
        }
        return $this->path;
    }


}