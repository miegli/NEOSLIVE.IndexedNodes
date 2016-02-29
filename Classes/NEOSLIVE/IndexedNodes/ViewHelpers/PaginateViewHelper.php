<?php
namespace NEOSLIVE\IndexedNodes\ViewHelpers;

/*
 * This file is part of the TYPO3.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\Helpers\TypoScriptAwareViewInterface;
use NEOSLIVE\IndexedNodes\Domain\Service\IndexService;
use TYPO3\Neos\Service\LinkingService;

/**
 * A view helper for pagination of indexed nodes list
 *
 * = Examples =
 *
 */
class PaginateViewHelper extends AbstractViewHelper
{


    /**
     * @Flow\Inject
     * @var IndexService
     */
    protected $indexService;


    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;


    /**
     * @var boolean
     */
    protected $escapeOutput = false;


    /**
     * @var integer
     */
    protected $currentPage = 1;

    /**
     * @var integer
     */
    protected $numberOfPages = 1;

    /**
     * @var integer
     */
    protected $displayRangeStart;

    /**
     * @var integer
     */
    protected $displayRangeEnd;

    /**
     * @var integer
     */
    protected $itemsPerPage;


    /**
     * @var integer
     */
    protected $total;


    /**
     * @var integer
     */
    protected $limit;


    /**
     * @var string
     */
    protected $limitParamName;


    /**
     * @var string
     */
    protected $offsetParamName;





    /**
     * @param integer $itemsPerPage items per site
     * @param NodeInterface $node The node of the list element. Optional, will be resolved from the TypoScript context by default.
     * @param integer $nodescount the count of indexed node selection. Optional, will be resolved from the TypoScript context by default.
     * @return string The rendered property with a wrapping tag. In the user workspace this adds some required attributes for the RTE to work
     * @throws ViewHelperException
     */
    public function render($itemsPerPage, NodeInterface $node = null, $nodescount = null)
    {

        $view = $this->viewHelperVariableContainer->getView();
        if (!$view instanceof TypoScriptAwareViewInterface) {
            throw new ViewHelperException('This ViewHelper can only be used in a TypoScript content element. You have to specify the "node" argument if it cannot be resolved from the TypoScript context.', 1385737102);
        }
        $typoScriptObject = $view->getTypoScriptObject();
        $currentContext = $typoScriptObject->getTsRuntime()->getCurrentContext();

        if ($node === null) {
            $node = $currentContext['node'];
        }

        if ($nodescount === null) {
            $nodescount = $currentContext['nodescount'];
        }



        $configuration = $this->indexService->prepareNodeSelectionFromNode($node);
        $this->itemsPerPage = $itemsPerPage;
        $this->total = $nodescount;
        $this->limit = $configuration['limit'];
        $this->limitParamName = $configuration['limit_param_name'];
        $this->offsetParamName = $configuration['offset_param_name'];
        $this->numberOfPages = ceil($this->total / (integer)$this->itemsPerPage);
        if ($configuration['offset'] > 0) {
            $this->currentPage = ($configuration['offset'] / $this->itemsPerPage)+1;
        }

        $this->templateVariableContainer->add('pagination', $this->buildPagination());


        return $this->renderChildren();
    }


    /**
     * @param $pagenum
     */
    protected function buildUrl($pagenum)
    {


       $params = $this->controllerContext->getRequest()->getArguments();
       $params[$this->offsetParamName] = $this->itemsPerPage*($pagenum-1);

      return $this->linkingService->createNodeUri(
          $this->controllerContext,
          $params['node'],
          null,
          null,
          false,
          $params
      );
    }


    /**
     * If a certain number of links should be displayed, adjust before and after
     * amounts accordingly.
     *
     * @return void
     */
    protected function calculateDisplayRange()
    {

        $delta = floor($this->numberOfPages / 2);
        $this->displayRangeStart = $this->currentPage - $delta;
        $this->displayRangeEnd = $this->currentPage + $delta + ($this->numberOfPages % 2 === 0 ? 1 : 0);
        if ($this->displayRangeStart < 1) {
            $this->displayRangeEnd -= $this->displayRangeStart - 1;
        }
        if ($this->displayRangeEnd > $this->numberOfPages) {
            $this->displayRangeStart -= ($this->displayRangeEnd - $this->numberOfPages);
        }
        $this->displayRangeStart = (integer)max($this->displayRangeStart, 1);
        $this->displayRangeEnd = (integer)min($this->displayRangeEnd, $this->numberOfPages);
    }


    /**
     * Returns an array with the keys "pages", "current", "numberOfPages", "nextPage" & "previousPage"
     *
     * @return array
     */
    protected function buildPagination()
    {
        $this->calculateDisplayRange();
        $pages = array();
        for ($i = $this->displayRangeStart; $i <= $this->displayRangeEnd; $i++) {
            $pages[] = array(
                'number' => $i,
                'isCurrent' => ($i === $this->currentPage),
                'url' => $this->buildUrl($i)

            );
        }

        $pagination = array(
            'pages' => $pages,
            'current' => $this->currentPage,
            'numberOfPages' => $this->numberOfPages,
            'displayRangeStart' => $this->displayRangeStart,
            'displayRangeEnd' => $this->displayRangeEnd,
            'hasLessPages' => $this->displayRangeStart > 2,
            'hasMorePages' => $this->displayRangeEnd + 1 < $this->numberOfPages,
            'numberOfItems' => $this->total
        );

        if (count($pages)) {
                $pagination['firstPage'] = $pages[0];
                $pagination['lastPage'] = $pages[count($pages)-1];
                $pagination['nextPage'] = $pages[ $this->currentPage<count($pages) ? $this->currentPage : $this->currentPage-1];
                $pagination['previousPage'] = $pages[  $this->currentPage > 1 ? $this->currentPage-2 : 0  ];
        } else {
                $pagination['firstPage'] = array();
                $pagination['lastPage'] = array();
                $pagination['nextPage'] = array();
                $pagination['previousPage'] = array();
        }

        return $pagination;
    }


}
