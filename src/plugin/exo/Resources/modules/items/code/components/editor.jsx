import React, {Component} from 'react'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'


import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
//import {FormGroup} from '#/main/app/content/form/components/group'
//import {HtmlInput} from '#/main/app/data/types/html/components/input'
//import {Button} from '#/main/app/action/components/button'
//import {CALLBACK_BUTTON} from '#/main/app/buttons'

//import {makeId} from '#/main/core/scaffolding/id'


import {CodeField} from '#/plugin/exo/items/code/components/field'

import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {CodeItem} from '#/plugin/exo/items/code/prop-types'


// TODO : code field and code question editor : 
// The code question editor should allow to :
// - Load a tree from a zip file
// - Edit the tree (add/edit/delete nodes)
// - Change content of a selected virtual file


const CodeEditor = (props) => {

  const newItem = cloneDeep(props.item)

  console.log(props);

  const PlaceholderContent = (
    <CodeField
      path='placeholderTree'
      item={newItem.placeholderTree}
      update={props.update}
    />
  )

  // const SolutionContent = (
  //   <CodeField
  //     {...props}
  //     item={newItem.solutionTree}
  //     hasScore={props.hasAnswerScores}
  //   />
  // )

  return (
    <FormData
      className="code-editor"
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'treeIsEditable',
              label: trans('tree_is_editable', {}, 'quiz'),
              type: 'boolean'
            },{
              name: 'placeholderTree',
              label: trans('starting_content', {}, 'quiz'),
              component:PlaceholderContent
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(CodeEditor, ItemEditorTypes, {
  item: T.shape(CodeItem.propTypes).isRequired
})

export {
  CodeEditor
}

/*
,{
              name: 'placeholderTree',
              label: trans('starting_content', {}, 'quiz'),
              component:PlaceholderContent
            },
            {
              name: 'withSolutions',
              label: trans('provide_solutions', {}, 'quiz'),
              type: 'boolean',
              onChange: (checked) => {
                if (checked) {
                  // Prepare solution tree node
                  props.update('solutionTree', {
                    name:'solution',
                    readOnly:'true',
                    subNodes:[],
                    codeItems:[]
                  })
                } else {
                  props.update('solutionTree', null) // delete the tree
                }
              },
              linked: [
                {
                  name: 'solutionTree',
                  label: trans('solution'),
                  required: true,
                  component:SolutionContent
                }
              ]
            }
 */
