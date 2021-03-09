import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {CodeFolder} from '#/plugin/exo/items/code/prop-types'

// TODO : the player load the the placeholder tree as default answer
// then the user can updated its answer tree

const CodePlayer = (props) =>
  <div>

    <HtmlInput
      id={`open-${props.item.id}-data`}
      value={props.answer}
      disabled={props.disabled}
      onChange={(value) => props.disabled ? false : props.onChange(value)}
    />
    {0 < props.item.maxLength &&
      <div className="pull-right">
        {trans('remaining_characters', {}, 'quiz')} : {props.item.maxLength - props.answer.replace('&nbsp;', ' ').replace(/<[^>]*>/g, '').length}
      </div>
    }
  </div>

CodePlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    contentType: T.string.isRequired,
    maxLength: T.number.isRequired
  }).isRequired,
  answer: T.shape(CodeFolder.propTypes),
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

CodePlayer.defaultProps = {
  answer: {},
  disabled: false
}

export {
  CodePlayer
}
