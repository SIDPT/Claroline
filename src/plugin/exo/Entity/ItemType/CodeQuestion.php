<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\ORM\Mapping as ORM;

use UJM\ExoBundle\Entity\Misc\CodeFolder;

/**
 * A code question where the use must fill text area with code
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_code_question")
 */
class CodeQuestion extends AbstractItem
{
    
    /**
     *  Root node of the placeholderTree/default content Tree
     *
     * @ORM\OneToOne(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\CodeFolder",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var [type]
     */
    protected $placeholderTree;

    /**
     * Tree of the solution
     *
     * @ORM\OneToOne(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\CodeFolder",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var [type]
     */
    protected $solutionTree;


    /**
     * Can the attendee update the virtual file tree
     *
     * @ORM\Column(type="boolean")
     *
     * @var [type]
     */
    protected $treeIsEditable = false;


    public function getPlaceholderTree()
    {
        return $this->placeholderTree;
    }

    public function setPlaceholderTree(CodeNode $placeholderTree)
    {
        $this->placeholderTree = $placeholderTree;
    }

    public function getSolutionTree()
    {
        return $this->solutionTree;
    }

    public function setSolutionTree(CodeNode $solutionTree)
    {
        $this->solutionTree = $solutionTree;
    }

    public function canEditTree($newEditableStatus = null)
    {
        if (isset($newEditableStatus)) {
            $this->treeIsEditable = $newEditableStatus;
        }
        return $this->treeIsEditable;
    }

}