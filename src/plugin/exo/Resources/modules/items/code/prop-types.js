import {PropTypes as T} from '#/main/app/prop-types'

import {SCORE_MANUAL} from '#/plugin/exo/quiz/enums'


const CodeFile = {
  propTypes: { 
    id:T.number,
    name:T.string.isRequired,
    type:T.string,
    content:T.string,
    readOnly:T.boolean
  },
  defaultProps: {
    name:'new file.txt',
    type:'text/plain',
    content:'UGxhY2Vob2xkZXI=',
    readOnly:false
  }
}

const CodeFolderShape = {
  id:T.number,
  name:T.string,
  readOnly:T.boolean
}
CodeFolderShape.subfolders = T.arrayOf(T.shape(CodeFolderShape))
CodeFolderShape.codefiles = T.arrayOf(T.shape(CodeFile.propTypes))

const CodeFolder = {
  propTypes:T.shape(CodeFolderShape),
  defaultProps:{
    name:'new folder',
    readOnly:false
  }
}

// CodeQuestion entity renamed CodeItem here,
// following other questions naming conventions 
// where an "item" is a question
const CodeItem = {
  propTypes: {
    id: T.string,
    treeIsEditable:T.boolean,
    placeholderTree:T.shape(CodeFolder.propTypes),
    solutionTree:T.shape(CodeFolder.propTypes)
  },
  defaultProps: {
    score: {
      type: SCORE_MANUAL,
      max: 0
    },
    treeIsEditable:true,
    placeholderTree: {
      name:'',
      readOnly:false,
      subfolders:[],
      codefiles:[]
    },
    solutionTree: {
      name:'solution',
      readOnly:false,
      subfolders:[],
      codefiles:[]
    }
  }
}



export {
  CodeFolder,
  CodeFile,
  CodeItem
}