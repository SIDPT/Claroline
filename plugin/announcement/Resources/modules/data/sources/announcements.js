import {url} from '#/main/app/api'
import {URL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

import {AnnouncementCard} from '#/plugin/announcement/data/components/announcement-card'

export default {
  name: 'announcements',
  parameters: {
    primaryAction: (announcement) => ({
      type: URL_BUTTON,
      target: url([ 'claro_resource_show_short', {
        id: announcement.meta.resource.id
      }]) + `#/${announcement.id}`
    }),
    definition: [
      {
        name: 'title',
        type: 'string',
        label: trans('title'),
        displayed: true,
        primary: true
      }, {
        name: 'content',
        type: 'html',
        label: trans('content'),
        displayed: true
      }, {
        name: 'meta.publishedAt',
        alias: 'publicationDate',
        type: 'date',
        label: trans('date'),
        displayed: true
      }, {
        name: 'meta.author',
        alias: 'announcer',
        type: 'string',
        label: trans('author'),
        displayed: true
      }
    ],
    card: AnnouncementCard
  }
}