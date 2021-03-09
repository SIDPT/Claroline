import React, {Component} from 'react'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'


import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {FormGroup} from '#/main/app/content/form/components/group'
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {makeId} from '#/main/core/scaffolding/id'

import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {CodeFolder, CodeFile} from '#/plugin/exo/items/code/prop-types'

import {CodeTree, CodeContentEditor} from '#/plugin/exo/items/code/components/input/'

class CodeField extends Component {
  constructor(props) {
    super(props)

    this.state = {
      selectedElement:null,
      displayDefinition:false
    }
  }

  getNode(path=''){
    if(path === '') return this.props.item
    else {
      get(this.props.item, path)
    }
  }

  render() {


    return (
      <fieldset className="code-field">
        <nav>
          <CodeTree 
            root={this.props.item}
            readOnly={false}
            folderMenu={(folder,path) => [
              {
                type: CALLBACK_BUTTON,
                label: trans('add_folder'),
                callback: () => {
                  let newRoot = cloneDeep(this.props.item)
                  let newFolder = get(newRoot,path)
                  newFolder.subfolders.push(Object.assign({},CodeFolder.defaultProps))
                  this.props.update(this.props.path,newRoot)
                  this.setState({
                    selectedElement:folder.subfolders[folder.subfolders.length-1],
                    displayDefinition:true
                  })
                }
              },
              {
                type: CALLBACK_BUTTON,
                label: trans('add_file'),
                callback: () => {
                  folder.codefiles.push(Object.assign({},CodeFile.defaultProps))
                  // select the folder
                  this.setState({
                    selectedElement:folder.codefiles[folder.codefiles.length-1],
                    displayDefinition:true
                  })
                }
              }, {
                type: CALLBACK_BUTTON,
                label: trans('edit'),
                callback: () => {
                  this.setState({
                    selectedElement:folder,
                    displayDefinition:true
                  })
                }
              }, {
                type: CALLBACK_BUTTON,
                label: trans('delete'),
                disabled: path === '',
                callback: () => {
                  if(folder ===  this.state.selectedElement){
                    this.setState({
                      selectedElement:null
                    })
                  }
                  let pathes = path.split('.')
                  const nodeData = pathes[pathes.length-1].split('[')
                  const nodeType = nodeData[0]
                  const nodeIndex = parseInt(nodeData[1].split(']')[0])
                  let parent = get(
                    this.props.item, 
                    pathes.slice(0,pathes.length).join('.'))
                  parent[nodeType] = parent[nodeType].splice(nodeIndex,1)
                }
              }
            ]}
            fileMenu={(file,path)=>[
              {
                type: CALLBACK_BUTTON,
                label: trans('edit'),
                callback: () => {
                  this.setState({
                    selectedElement:file,
                    displayDefinition:true
                  })
                }                  
              }, {
                type: CALLBACK_BUTTON,
                label: trans('delete'),
                callback: () => {
                  if(file ===  this.state.selectedElement){
                    this.setState({
                      selectedElement:null
                    })
                  }
                  let pathes = path.split('.')
                  const nodeData = pathes[pathes.length-1].split('[')
                  const nodeType = nodeData[0]
                  const nodeIndex = parseInt(nodeData[1].split(']')[0])
                  let parent = get(
                    this.props.item, 
                    pathes.slice(0,pathes.length).join('.'))
                  parent[nodeType] = parent[nodeType].splice(nodeIndex,1)
                }
              }
            ]}
            onFileSelected={(file)=> {
              
              this.setState({
                selectedElement:file,
                displayDefinition:false
              })
            }}
          />
        </nav>
        <div>
          { !this.state.displayDefinition && this.state.selectedElement && 
              this.state.selectedElement.content &&
            <CodeContentEditor 
              id='code-input'
              name={this.state.selectedElement.name}
              displayRaw={false}
              onChange={this.updateCurrentNode}
              base64value={this.state.selectedElement.content}
            />
          }
          { this.state.displayDefinition && this.state.selectedElement &&
            <div>
              <label>{trans('name')}</label>
              <label>{trans('name')}</label>
            </div>
          }

        </div>
        
        
      </fieldset>
    )
  }
}

CodeField.propTypes = {
  path:T.string,
  item:T.shape(CodeFolder.propTypes),
  update:T.func
}

export {
  CodeField
}
