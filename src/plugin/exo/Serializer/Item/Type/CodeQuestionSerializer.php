<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;


use UJM\ExoBundle\Entity\ItemType\CodeQuestion;
use UJM\ExoBundle\Entity\Misc\CodeFolder;
use UJM\ExoBundle\Entity\Misc\CodeFile;

use UJM\ExoBundle\Library\Options\Transfer;

class CodeQuestionSerializer
{
    use SerializerTrait;

    public function getName()
    {
        return 'exo_question_code';
    }

    /**
     * Converts a Code question into a JSON-encodable structure.
     *
     * @param CodeQuestion $codeQuestion
     * @param array         $options
     *
     * @return array
     */
    public function serialize(
        CodeQuestion $codeQuestion,
        array $options = []
    ) {
        $placeholder = $codeQuestion->getPlaceholderTree();
        if (empty($placeholder)) {
            $placeholder = new CodeFolder();
            $codeQuestion->setPlaceholderTree($placeholder);
        }
        $serialized = [
            'placeholderTree' => $this->serializeFolder(
                $placeholder,
                $options
            ),
            'treeIsEditable' => $codeQuestion->canEditTree()
        ];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $solution = $codeQuestion->getSolutionTree();
            if (empty($solution)) {
                $solution = new CodeFolder();
                $codeQuestion->setSolutionTree($solution);
            }
            $serialized['solutionTree'] = $this->serializeFolder(
                $solution,
                $options
            );
        }

        return $serialized;
    }

    public function serializeFolder(CodeFolder $folder, array $options = [])
    {
        $serialized = [
            'id' => $folder->getId(),
            'name' => $folder->getName(),
            'readOnly' => $item->isReadOnly(),
            'subfolders' => array_map(
                function (CodeFolder $subfolder) {
                    $this->serializeFolder($subfolder, $options);
                },
                $folder->getSubfolders()->toArray()
            ),
            'codefiles' => array_map(
                function (CodeFile $item) {
                    $this->serializeFile($item, $options);
                },
                $folder->getCodefiles()->toArray()
            )
        ];
        return $serialized;
    }

    public function serializeFile(CodeFile $item, array $options = [])
    {
        $type = "";
        if ($item->getType()) {
            // Force type load
            $type = $item->getType();
        } else {
            // search if the file extension maps a mode
        }
        $serialized = [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'type' => $item->getType(),
            'content' => $item->getContent(),
            'readOnly' => $item->isReadOnly()
        ];
        return $serialized;
    }

    /**
     * Converts raw data into a Code question entity.
     *
     * @param array        $data         data to deserialize
     * @param CodeQuestion $codeQuestion target question
     * @param array        $options      desiralizer options
     *
     * @return CodeQuestion
     */
    public function deserialize(
        $data,
        CodeQuestion $codeQuestion = null,
        array $options = []
    ) {
        if (empty($codeQuestion)) {
             $codeQuestion = new CodeQuestion();
        }

        $this->sipe('treeIsEditable', 'canEditTree', $data, $codeQuestion);

        if (empty($codeQuestion->getPlaceholderTree())) {
            $codeQuestion->setPlaceholderTree(new CodeFolder());
        }

        $placeholderTree = $this->deserializeFolder(
            $data['placeholderTree'],
            $codeQuestion->getPlaceholderTree(),
            $options
        );
        $placeholderTree->setQuestion($codeQuestion, true);

        if (empty($codeQuestion->getSolutionTree())) {
            $codeQuestion->setSolutionTree(new CodeFolder());
        }
        $solutionTree = $this->deserializeFolder(
            $data['solutionTree'],
            $codeQuestion->getSolutionTree(),
            $options
        );
        $solutionTree->setQuestion($codeQuestion, true);

        return $codeQuestion;
    }

    public function deserializeFolder(
        $folderData,
        CodeFolder $codeFolder = null,
        array $options = []
    ) {
        if (empty($codeFolder)) {
             $codeFolder = new CodeFolder();
        }
        $this->sipe('name', 'setName', $folderData, $codeFolder);
        $this->sipe('readOnly', 'isReadOnly', $treeData, $codeFolder);

        // update subfolders
        $currentSubfolders = $codeFolder->getSubfolders()->toArray();
        $subfoldersIds = [];
        foreach ($folderData['subfolders'] as $position => $folder) {
            $subfolder = isset($folder['id']) ?
                $codeFolder->getSubfolder($folder['id']) :
                null;

            if (empty($subfolder)) {
                $subfolder = new CodeFolder();
                $subfolder->setParent($codeFolder);
                $codeFolder->addSubfolder($subfolder);
            }
            $this->deserializeFolder(
                $folder,
                $subfolder,
                $options
            );

            $subfoldersIds[] = $subfolder->getId();
        }

        // removes subfolders which no longer exists
        foreach ($currentSubfolders as $subfolder) {
            if (!in_array($subfolder->getId(), $subfoldersIds)) {
                $codeFolder->removeSubfolder($subfolder);
                $this->om->remove($subfolder);
            }
        }
        

        // update CodeFiles
        $currentCodeFiles = $codeFolder->getCodefiles()->toArray();
        $codefilesIds = [];
        foreach ($folderData['codefiles'] as $position => $item) {
            $codefile = isset($item['id']) ?
                $codeFolder->getCodefile($item['id']) :
                null;

            if (empty($codefile)) {
                $codefile = new CodeFile();
                $codefile->setParent($codeFolder);
                $codeFolder->addCodefile($codefile);
            }
            $this->deserializeFile(
                $item,
                $codefile,
                $options
            );

            $codefilesIds[] = $codefile->getId();
        }

        // removes CodeFiles which no longer exists
        foreach ($currentCodeFiles as $codefile) {
            if (!in_array($codefile->getId(), $codefilesIds)) {
                $codeFolder->removeCodefile($codefile);
                $this->om->remove($codefile);
            }
        }

        return $codeFolder;
    }

    public function deserializeFile(
        $itemData,
        CodeFile $codefile = null,
        array $options = []
    ) {
        if (empty($codefile)) {
             $codefile = new CodeFile();
        }

        $this->sipe('name', 'setName', $treeData, $codefile);
        $this->sipe('type', 'setType', $treeData, $codefile);
        $this->sipe('content', 'setContent', $treeData, $codefile);
        $this->sipe('readOnly', 'isReadOnly', $treeData, $codefile);

        return $codefile;
    }



}