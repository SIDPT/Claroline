import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {MenuButton} from '#/main/app/buttons'


import {trans} from '#/main/app/intl/translation'


/**
 * Tree item (based on https://www.w3.org/TR/wai-aria-practices/examples/treeview/treeview-2/treeview-2b.html)
 *
 * props :
 * tree,
 * path,
 * isExpandable,
 * inGroup,
 * node,
 * onNodeSelected,
 * level,
 * setsize,
 * posinset
 * actions
 *  
 */
class CodeTreeItem extends Component {
  constructor(props){
    super(props)

    this.nodeRef = React.createRef()

    this.keyCode = Object.freeze({
      RETURN: 13,
      SPACE: 32,
      PAGEUP: 33,
      PAGEDOWN: 34,
      END: 35,
      HOME: 36,
      LEFT: 37,
      UP: 38,
      RIGHT: 39,
      DOWN: 40
    })

    this.state = {
      isExpanded:true,
      isVisible:true,
      isFocused:false,
      isHovered:false
    }

    this.handleKeydown = this.handleKeydown.bind(this)
    this.handleClick = this.handleClick.bind(this)
    this.handleFocus = this.handleFocus.bind(this)
    this.handleBlur = this.handleBlur.bind(this)
    this.handleMouseOver = this.handleMouseOver.bind(this)
    this.handleMouseOut = this.handleMouseOut.bind(this)

    this.focusNode = this.focusNode.bind(this)
  }

  focusNode(){
    this.nodeRef.current.focus()
  }

  /**
   * KeyDown event handler from ARIA TreeItem
   * @param  {[type]} event [description]
   * @return {[type]}       [description]
   */
  handleKeydown(event) {
    var tgt = event.currentTarget, flag = false, char = event.key, clickEvent

    function isPrintableCharacter(str) {
      return str.length === 1 && str.match(/\S/)
    }

    function printableCharacter(item) {
      if (char == '*') {
        item.tree.expandAllSiblingItems(item)
        flag = true
      } else {
        if (isPrintableCharacter(char)) {
          item.tree.setFocusByFirstCharacter(item, char)
          flag = true
        }
      }
    }

    this.stopDefaultClick = false

    if (event.altKey || event.ctrlKey || event.metaKey) {
      return
    }

    if (event.shift) {
      if (event.keyCode == this.keyCode.SPACE || event.keyCode == this.keyCode.RETURN) {
        event.stopPropagation()
        this.stopDefaultClick = true
      } else {
        if (isPrintableCharacter(char)) {
          printableCharacter(this)
        }
      }
    } else {
      switch (event.keyCode) {
        case this.keyCode.SPACE:
        case this.keyCode.RETURN:
          // Create simulated mouse event to mimic the behavior of ATs
          // and let the event handler handleClick do the housekeeping.
          try {
            clickEvent = new MouseEvent('click', {
              'view': window,
              'bubbles': true,
              'cancelable': true
            })
          }
          catch (err) {
            if (document.createEvent) {
              // DOM Level 3 for IE 9+
              clickEvent = document.createEvent('MouseEvents')
              clickEvent.initEvent('click', true, true)
            }
          }
          tgt.dispatchEvent(clickEvent)
          flag = true
          break

        case this.keyCode.UP:
          this.props.tree.setFocusToPreviousItem(this)
          flag = true
          break

        case this.keyCode.DOWN:
          this.props.tree.setFocusToNextItem(this)
          flag = true
          break

        case this.keyCode.RIGHT:
          if (this.props.isExpandable) {
            if (this.state.isExpanded) {
              this.props.tree.setFocusToNextItem(this)
            }
            else {
              this.props.tree.expandTreeitem(this)
            }
          }
          flag = true
          break

        case this.keyCode.LEFT:
          if (this.props.isExpandable && this.state.isExpanded) {
            this.props.tree.collapseTreeitem(this)
            flag = true
          }
          else {
            if (this.props.inGroup) {
              this.props.tree.setFocusToParentItem(this)
              flag = true
            }
          }
          break

        case this.keyCode.HOME:
          this.props.tree.setFocusToFirstItem()
          flag = true
          break

        case this.keyCode.END:
          this.props.tree.setFocusToLastItem()
          flag = true
          break

        default:
          if (isPrintableCharacter(char)) {
            printableCharacter(this)
          }
          break
      }
    }

    if (flag) {
      event.stopPropagation()
      event.preventDefault()
    }
  }

  handleClick(event) {
    if (this.props.isExpandable) {
      if (this.state.isExpanded) {
        this.tree.collapseTreeitem(this)
      }
      else {
        this.tree.expandTreeitem(this)
      }
      event.stopPropagation()
    }
    else {
      this.tree.setFocusToItem(this)
    }
    if(this.props.onNodeSelected) {
      this.props.onNodeSelected(this.props.node)
    }
  }

  handleFocus(event) {
    this.setState({isFocused:true})
  }

  handleBlur(event) {
    this.setState({isFocused:false})
  }

  handleMouseOver(event) {
    this.setState({isHovered:true})
  }

  handleMouseOut(event) {
    this.setState({isHovered:false})
  }

  render(){
    
    return (
      <li id={`node-${this.props.path}`}
        key={`node-${this.props.path}`}
        ref={this.nodeRef}
        role="treeitem"
        className={classes(
          {focus:this.state.isFocused},
          {hover:this.state.isHovered})}
        tabIndex={this.state.isFocused ? 0 : -1}
        aria-level={this.props.level}
        aria-setsize={this.props.setsize}
        aria-posinset={this.props.posinset}
        onClick={this.handleClick}
        onFocus={this.handleFocus}
        onKeyDown={this.handleKeydown}
        onBlur={this.handleBlur}
        onMouseOver={this.handleMouseOver}
        onMouseOut={this.handleMouseOut}
        aria-expanded={this.state.isExpanded}>
        <span id={`node-name-${this.props.path}`}>
          {this.props.name}
        </span>
        {this.props.actions && this.props.actions.length > 0 && 
          <MenuButton
            style={{marginLeft:'5px'}}
            className={classes( 'btn' , 'btn-secondary' ,'btn-sm' , 'dropdown-toggle',  {disabled: this.props.readOnly})}
            id={`node-dropdown-${this.props.node.id ? this.props.node.id : 'new_node'}`}
            menu={{
              items: this.props.actions
            }}
          >
            <span className='sr-only sr-only-focusable'>{trans('actions')}</span>
          </MenuButton>
        }
        {this.props.children}
      </li>
    )
  }
}

export {
  CodeTreeItem
}
