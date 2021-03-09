import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'

const CodeFeedback = props =>
  <div className="open-feedback">
    {props.answer && 0 !== props.answer.length ?
      <ContentHtml>{props.answer}</ContentHtml>
      :
      <div className="no-answer">{trans('no_answer', {}, 'quiz')}</div>
    }
  </div>

CodeFeedback.propTypes = {
  answer: T.string
}

CodeFeedback.defaultProps = {
  answer: ''
}

export {
  CodeFeedback
}
