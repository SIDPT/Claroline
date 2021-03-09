
import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'

import {Button} from '#/main/app/action/components/button'
import {MenuButton, CALLBACK_BUTTON} from '#/main/app/buttons'


import {trans} from '#/main/app/intl/translation'

import {CodeFolder, CodeFile} from '#/plugin/exo/items/code/prop-types'

import {CodeTreeItem} from '#/plugin/exo/items/code/components/input/treeitem'


/**
 * Editable tree of code nodes and code items
 */
class CodeTree extends Component {

  constructor(props){
    super(props)
    this.state = {
      displayPopupMenu:false,
      selectedNode:null,
      selectedDOMNode:null,
      selectedType:null,
      selectedAction:null
    }
    this.renderFolder = this.renderFolder.bind(this)
    this.renderFile = this.renderFolder.bind(this)

    
  }

 
  // Simpler version of the tree
  renderFolder(codeFolder, path='' , level=0, setsize=1, posinset=0){
    const children = []
    const subsetsize = codeFolder.subfolders.length + codeFolder.codefiles.length
    children.concat(codeFolder.subfolders.map(
      (subfolder,index) => this.renderFolder(
        subfolder, 
        `${path !== '' ? path+'.': path}subfolders[${index}]`,
        level + 1,
        subsetsize, 
        index)
    )).concat(codeFolder.codefiles.map(
      (codefile,index) => this.renderFile(
        codefile,
        `${path !== '' ? path+'.': path}codefiles[${index}]`, 
        level + 1,
        subsetsize, 
        codeFolder.subfolders.length + index)
    ))

    const treeitem = <CodeTreeItem 
      tree={this}
      isExpandable={true}
      level={level+1}
      setsize={setsize}
      posinset={posinset+1}
      path={`${path}`}
      inGroup={level > 0}
      node={codeFolder}
      onNodeSelected={this.props.onFolderSelected}
      actions={this.props.folderMenu(codeFolder,path)}
    >
      {children.length > 0 && <ul role="group">
        {children}
      </ul>}
    </CodeTreeItem>

    return treeitem
  }

  renderFile(codeFile, path='' , level=0, setsize=1, posinset=0){
    const treeitem = <CodeTreeItem 
      tree={this}
      isExpandable={false}
      level={level+1}
      setsize={setsize}
      posinset={posinset+1}
      path={`${path}`}
      inGroup={level > 0}
      node={codeFile}
      onNodeSelected={()=>{}}
      actions={this.props.fileMenu(codeFile,path)}
    />
    return treeitem
  }

  render(){

    return (
      <nav>
        <ul>
          {this.renderFolder(this.props.root)}
        </ul>
      </nav>
    )
    
  }
}

CodeTree.propTypes = {
  root:T.shape(CodeFolder.propTypes),
  readOnly:T.boolean,
  // Selection
  onFolderSelected:T.func,
  onFileSelected:T.func,
  // Menu builder functions ((node,path) => [...actions])
  folderMenu:T.func,
  fileMenu:T.func
}

export {
  CodeTree
}

/*




 */