<?php
/**
 * 
 */

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;

use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

use UJM\ExoBundle\Entity\Misc\CodeFolder;

/**
 * Part of the coding exercise tree
 *
 * TODO : add a constraint on unique parentId/name to avoid
 * duplicated code items names in a node
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_code_file")
 *
 */
class CodeFile implements AnswerPartInterface
{

    use ScoreTrait;
    use FeedbackTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Related question
     *
     * @ORM\ManyToOne(
     *     targetEntity="UJM\ExoBundle\Entity\ItemType\CodeQuestion")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     *
     * @var [type]
     */
    protected $question;

    /**
     *
     * @ORM\ManyToOne(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\CodeFolder",
     *     inversedBy="codefiles")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     *
     * @var [type]
     */
    protected $parent;

    /**
     * Virtual file name (prevent unnamed files)
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     *
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
    protected $readOnly = false;

    /**
     * File type, in mimetype format
     * (like text/css or image/png)
     *
     * For special code type, use "text/language"
     * or "application/language", like "application/javascript"
     * or "text/java"
     *
     * @ORM\Column(type="string", length=255)
     *
     * @var [type]
     */
    protected $type;

    /**
     * File content, to be encoded/decoded in base64
     *
     * @ORM\Column(type="text", length=16777215)
     *
     * @var [type]
     */
    protected $content;

    

    // ---- GETTERS
    
    public function getId()
    {
        return $this->id;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getContent()
    {
        return $this->content;
    }

    // ----- SETTERS
    
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setParent(CodeNode $parent)
    {
        $this->parent = $parent;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function isReadOnly($newStatus = null)
    {
        if (isset($newStatus)) {
            $this->readOnly = $newStatus;
        }
        return $this->readOnly;
    }
}
