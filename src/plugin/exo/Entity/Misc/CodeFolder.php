<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use UJM\ExoBundle\Entity\ItemType\CodeQuestion;
use UJM\ExoBundle\Entity\Misc\CodeFile;

/**
 * Coding exercise virtual directory that can hold other folders or code files
 *
 * TODO : add a constraint on unique question/parentId/name to avoid
 * duplicated subfolders names (question is included for unnamed root nodes)
 * 
 * @ORM\Entity()
 * @ORM\Table(name="ujm_code_folder")
 *
 */
class CodeFolder
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var [type]
     */
    protected $name;

    /**
     * Node/Item is editable by users in a paper
     *
     * @ORM\Column(type="boolean")
     *
     * @var [type]
     */
    protected $readOnly;


    /**
     * Related question to which the node is attached
     *
     * @ORM\ManyToOne(
     *     targetEntity="UJM\ExoBundle\Entity\ItemType\CodeQuestion")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     *
     * @var [type]
     */
    protected $question;


    /**
     * Parent in the tree hierarchy
     *
     * @ORM\ManyToOne(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\CodeFolder",
     *     inversedBy="subfolders")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     *
     * @var [type]
     */
    protected $parent;


    /**
     * Subfolders (virtual directory)
     *
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\CodeFolder",
     *     mappedBy="parent",
     *     cascade={"persist", "remove"})
     *
     * @var [type]
     */
    protected $subfolders;

    /**
     * CodeFile subfolders
     *
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\CodeFile",
     *     mappedBy="parent",
     *     cascade={"persist", "remove"})
     *
     * @var [type]
     */
    protected $codefiles;



    public function __construct()
    {
        $this->subfolders = new ArrayCollection();
        $this->codefiles = new ArrayCollection();
    }

    // ---- GETTERS

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getSubfolders()
    {
        return $this->subfolders;
    }

    public function getCodefiles()
    {
        return $this->codefiles;
    }

    // ---- SETTERS

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setQuestion(CodeQuestion $question, $updateSubfolders = false)
    {
        $this->question = $question;
        if ($updateSubfolders) {
            foreach ($this->subfolders as $node) {
                $node->setQuestion($question, $updateSubfolders);
            }
            foreach ($this->codefiles as $item) {
                $item->setQuestion($question);
            }
        }
    }

    public function setParent(CodeFolder $parent)
    {
        $this->parent = $parent;
    }

    public function setSubfolders(ArrayCollection $subfolders)
    {
        $this->subfolders = $subfolders;
    }

    public function setCodefiles(ArrayCollection $codefiles)
    {
        $this->codefiles = $codefiles;
    }

    public function addSubfolder(CodeFolder $subfolder)
    {
        if (!$this->subfolders->contains($subfolder)) {
            $subfolder->setQuestion($this->question);
            $this->subfolders->add($subfolder);
        }
    }

    public function addCodefile(CodeFile $codefile)
    {
        if (!$this->codefiles->contains($codefile)) {
            $codefiles->setQuestion($this->question);
            $this->codefiles->add($codefile);
        }
    }

    public function removeSubfolder(CodeFolder $subfolder)
    {
        if ($this->subfolders->contains($subfolder)) {
            $this->subfolders->remove($subfolder);
        }
    }

    public function removeCodefile(CodeFile $codefile)
    {
        if ($this->codefiles->contains($codefile)) {
            $this->codefiles->remove($codefile);
        }
    }

    /**
     * @param string $nodeId
     *
     * @return CodeFolder|null
     */
    public function getSubfolder($folderId)
    {
        $found = null;

        foreach ($this->subfolders as $folder) {
            if ($folder->getId() === $folderId) {
                $found = $folder;
                break;
            }
        }

        return $found;
    }

    /**
     * @param string $fileId
     *
     * @return CodeFile|null
     */
    public function getCodefile($fileId)
    {
        $found = null;

        foreach ($this->codefiles as $file) {
            if ($file->getId() === $fileId) {
                $found = $file;
                break;
            }
        }

        return $found;
    }

    public function isReadOnly($newStatus = null)
    {
        if (isset($newStatus)) {
            $this->readOnly = $newStatus;
        }
        return $this->readOnly;
    }
}
