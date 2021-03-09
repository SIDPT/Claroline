import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {notBlank, number, gteZero, chainSync} from '#/main/app/data/types/validators'

import {CorrectedAnswer} from '#/plugin/exo/items/utils'
import {OpenItem} from '#/plugin/exo/items/open/prop-types'
import {CodeItem} from '#/plugin/exo/items/code/prop-types'

// components
import {OpenPaper} from '#/plugin/exo/items/open/components/paper'
import {OpenPlayer} from '#/plugin/exo/items/open/components/player'
import {OpenFeedback} from '#/plugin/exo/items/open/components/feedback'
import {CodeEditor} from '#/plugin/exo/items/code/components/editor'

// scores
import ScoreManual from '#/plugin/exo/scores/manual'

export default {
  name: 'code',
  type: 'application/x.code+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: OpenPaper,
  player: OpenPlayer,
  feedback: OpenFeedback,

  components: {
    editor: CodeEditor
  },

  /**
   * List all available score modes for a code item.
   *
   * @return {Array}
   */
  supportScores: () => [
    ScoreManual
  ],

  /**
   * Create a new code item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => {
    const test = merge({}, baseItem, CodeItem.defaultProps)
    console.log(test)
    return test
  },

  /**
   * Validate a open item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    const errors = {}

    return errors
  },

  /**
   * Correct an answer submitted to a open item.
   *
   * @return {CorrectedAnswer}
   */
  correctAnswer: () => new CorrectedAnswer(),

  expectAnswer: () => [],
  allAnswers: () => [],

  refreshIdentifiers: (item) => {
    return item
  }
}
