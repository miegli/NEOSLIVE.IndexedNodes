<?php
namespace NEOSLIVE\IndexedNodes\Domain\Model;

/*
 * This file is part of the NEOSLIVE.IndexedNodes package.
 */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use NEOSLIVE\IndexedNodes\Domain\Model\IndexData;


/**
 * @Flow\Entity
 */
class Index
{

    /**
     * @ORM\ManyToOne(inversedBy="dimensions",cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\JoinTable(joinColumns={@ORM\JoinColumn(onDelete="cascade")})
     * @var \TYPO3\TYPO3CR\Domain\Model\NodeData
     */
    protected $nodeData;


    /**
     * @ORM\ManyToMany(inversedBy="data",cascade={"all"},orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\JoinTable(joinColumns={@ORM\JoinColumn(onDelete="cascade")})
     * @ORM\OrderBy({"property" = "ASC"})
     * @var \Doctrine\Common\Collections\Collection<\NEOSLIVE\IndexedNodes\Domain\Model\IndexData>
     */
    protected $indexData;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex0;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex1;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex2;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex3;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex4;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex5;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex6;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex7;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex8;


    /**
     * @var IndexData
     * @ORM\ManyToOne(cascade={"all"})
     */
    protected $orderIndex9;


    /**
     * Hash of order Index property names
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $orderIndexHash;


    /**
     * Ordering key for workspaces
     *
     * @var integer
     * @ORM\Column(nullable=true)
     */
    protected $orderWorkspaces;



    /**
     * @param NodeData $nodeData
     */
    public function __construct(NodeData $nodeData)
    {
        $this->nodeData = $nodeData;
        $this->indexData = new \Doctrine\Common\Collections\ArrayCollection();


    }

    /**
     * @return int
     */
    public function getOrderWorkspaces()
    {
        return $this->orderWorkspaces;
    }

    /**
     * @param int $orderWorkspaces
     */
    public function setOrderWorkspaces($orderWorkspaces)
    {
        $this->orderWorkspaces = $orderWorkspaces;
    }



    /**
     * @param int $index
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex($index)
    {
        $orderIndex = 'orderIndex'.$index;
        return $this->$orderIndex;
    }


    /**
     * @param $index
     * @param $indexData
     * @param $orderingHash
     * @return bool
     */
    public function setOrderIndex($index, $indexData, $orderingHash)
    {

        $orderIndex = 'orderIndex'.$index;


        $this->$orderIndex = $indexData;


        if ($this->getOrderIndexHash() == $orderingHash) {
            // hash is still valide
            return true;
        } else {
            // hash was expired
            $this->setOrderIndexHash($orderingHash);
            return false;
        }

    }


    /**
     * @param $index
     * @return bool
     */
    public function clearOrderIndex($index)
    {

        $orderIndex = 'orderIndex'.$index;
        $this->$orderIndex = NULL;



    }


    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex0()
    {
        return $this->orderIndex0;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex0
     */
    public function setOrderIndex0($orderIndex0)
    {
        $this->orderIndex0 = $orderIndex0;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex1()
    {
        return $this->orderIndex1;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex1
     */
    public function setOrderIndex1($orderIndex1)
    {
        $this->orderIndex1 = $orderIndex1;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex2()
    {
        return $this->orderIndex2;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex2
     */
    public function setOrderIndex2($orderIndex2)
    {
        $this->orderIndex2 = $orderIndex2;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex3()
    {
        return $this->orderIndex3;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex3
     */
    public function setOrderIndex3($orderIndex3)
    {
        $this->orderIndex3 = $orderIndex3;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex4()
    {
        return $this->orderIndex4;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex4
     */
    public function setOrderIndex4($orderIndex4)
    {
        $this->orderIndex4 = $orderIndex4;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex5()
    {
        return $this->orderIndex5;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex5
     */
    public function setOrderIndex5($orderIndex5)
    {
        $this->orderIndex5 = $orderIndex5;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex6()
    {
        return $this->orderIndex6;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex6
     */
    public function setOrderIndex6($orderIndex6)
    {
        $this->orderIndex6 = $orderIndex6;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex7()
    {
        return $this->orderIndex7;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex7
     */
    public function setOrderIndex7($orderIndex7)
    {
        $this->orderIndex7 = $orderIndex7;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex8()
    {
        return $this->orderIndex8;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex8
     */
    public function setOrderIndex8($orderIndex8)
    {
        $this->orderIndex8 = $orderIndex8;
    }

    /**
     * @return \NEOSLIVE\IndexedNodes\Domain\Model\IndexData
     */
    public function getOrderIndex9()
    {
        return $this->orderIndex9;
    }

    /**
     * @param \NEOSLIVE\IndexedNodes\Domain\Model\IndexData $orderIndex9
     */
    public function setOrderIndex9($orderIndex9)
    {
        $this->orderIndex9 = $orderIndex9;
    }

    /**
     * @return string
     */
    public function getOrderIndexHash()
    {
        return $this->orderIndexHash;
    }

    /**
     * @param string $orderIndexHash
     */
    public function setOrderIndexHash($orderIndexHash)
    {
        $this->orderIndexHash = $orderIndexHash;
    }

    /**
     * @param \TYPO3\TYPO3CR\Domain\Model\NodeData $nodeData
     */
    public function setNodeData($nodeData)
    {
        $this->nodeData = $nodeData;
    }


    /**
     * Adds a index data
     *
     * @param IndexData $indexData
     * @return void
     */
    public function addIndexData(IndexData $indexData)
    {
        $this->indexData->add($indexData);
    }


    /**
     * @return \TYPO3\TYPO3CR\Domain\Model\NodeData
     */
    public function getNodeData()
    {
        return $this->nodeData;
    }


    /**
     * @param $property
     * @param $value
     * @return void
     */
    public function setIndexData($property, $value)
    {

        if (is_string($value)) {
            $indexData = $this->getIndexDataOrCreate($property);
            $indexData->setValue($value);
            $indexData->setValueRaw(strip_tags($value));
            $shortValue = strtolower(preg_replace('/[\xZZ]/', "", trim($indexData->getValueRaw())));
            $indexData->setValueInteger(intval($shortValue));
            $indexData->setValueDateTime($this->calculateDateFromString($indexData->getValueRaw()));
        }

        return $indexData;


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

    /**
     * @param $property
     * @return IndexData
     */
    public function getIndexDataOrCreate($property)
    {


        foreach ($this->indexData as $indexData) {

            if ($indexData->getProperty() == $property) {
                return $indexData;
            }

        }

        $indexData = new IndexData();
        $indexData->setProperty($property);
        $this->addIndexData($indexData);
        return $indexData;


    }


    /**
     * @param $str
     * @param $pad_len
     * @param string $pad_str
     * @param int $dir
     * @return null|string
     */
    function str_pad_unicode($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT)
    {
        $str_len = mb_strlen($str);
        $pad_str_len = mb_strlen($pad_str);
        if (!$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
            $str_len = 1; // @debug
        }
        if (!$pad_len || !$pad_str_len || $pad_len <= $str_len) {
            return $str;
        }

        $result = null;
        $repeat = ceil($str_len - $pad_str_len + $pad_len);
        if ($dir == STR_PAD_RIGHT) {
            $result = $str . str_repeat($pad_str, $repeat);
            $result = mb_substr($result, 0, $pad_len);
        } else if ($dir == STR_PAD_LEFT) {
            $result = str_repeat($pad_str, $repeat) . $str;
            $result = mb_substr($result, -$pad_len);
        } else if ($dir == STR_PAD_BOTH) {
            $length = ($pad_len - $str_len) / 2;
            $repeat = ceil($length / $pad_str_len);
            $result = mb_substr(str_repeat($pad_str, $repeat), 0, floor($length))
                . $str
                . mb_substr(str_repeat($pad_str, $repeat), 0, ceil($length));
        }

        return $result;
    }


}
