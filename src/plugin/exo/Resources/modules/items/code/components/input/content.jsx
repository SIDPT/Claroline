import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

import AceEditor from 'react-ace'

import 'ace-builds/src-noconflict/ext-language_tools'



// from https://www.w3docs.com/snippets/javascript/how-to-encode-and-decode-strings-with-base64-in-javascript.html
function base64EncodeUnicode(str) {
  // Firstly, escape the string using encodeURIComponent to get the UTF-8 encoding of the characters, 
  // Secondly, we convert the percent encodings into raw bytes, and add it to btoa() function.
  utf8Bytes = encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
    return String.fromCharCode('0x' + p1);
  });

  return btoa(utf8Bytes);
}

const extensionsTypesMap = {
  // Ace code
  'js':'application/javascript',
  'java':'application/java',
  'py':'application/python',
  'xml':'application/xml',
  'rb':'application/ruby',
  'sass':'application/sass',
  'md':'application/markdown',
  'sql':'application/mysql',
  'json':'application/json',
  'html':'application/html',
  'go':'application/golang',
  'cs':'application/csharp',
  'ts':'application/typescript',
  'css':'application/css',
  'scss':'application/scss',
  'sh':'application/sh',
  'less':'application/less',

  // Common types

  // Images
  'gif':'image/gif',
  'png':'image/png',
  'jpg':'image/jpeg',
  'jpeg':'image/jpeg',
  'svg':'image/svg+xml',

  // Audio
  'mp3':'audio/mpeg',
  
  // Video
  'mp4':'video/mp4'
}

// ACE Languages
const languages = [
  'css',
  'golang',
  'html',
  'java',
  'javascript',
  'json',
  'jsx',
  'latex',
  'less',
  'markdown',
  'mysql',
  'perl',
  'perl6',
  'php',
  'plain_text',
  'python',
  'ruby',
  'rust',
  'sass',
  'scss',
  'sh',
  'svg',
  'text',
  'tsx',
  'typescript',
  'xml',
  'yaml'
]

const themes = [
  'monokai',
  'github',
  'tomorrow',
  'kuroir',
  'twilight',
  'xcode',
  'textmate',
  'solarized_dark',
  'solarized_light',
  'terminal'
]

languages.forEach(lang => {
  require(`ace-builds/src-noconflict/mode-${lang}`)
  require(`ace-builds/src-noconflict/snippets/${lang}`)
})

themes.forEach(theme => require(`ace-builds/src-noconflict/theme-${theme}`))

class CodeContentEditor extends Component {
  constructor(props){
    super(props)
    this.state = {
      value:atob(this.props.base64value),
      theme:'monokai',
      fontSize:'12',
    }

    this.onChange = this.onChange.bind(this);

  }

  onThemeChange(theme){
    this.setState({theme:theme})
  }

  onFontSizeChange(fontSize){
    this.setState({fontSize:fontSize})
  }

  onChange(newValue){
    this.setState({
      value:newValue
    })
    if(this.props.onChange){
      this.props.onChange(newValue)
    }
  }

  render(){
    let view = ''
    let contentType = ''

    const value = atob(this.props.base64value)

    if(this.props.type && this.props.type !== 'text/plain'){
      contentType = this.props.type
    } 
    if(this.props.name ) {
      const extension = this.props.name.substr(this.props.name.lastIndexOf('.')+1)
      
      if(extensionsTypesMap.hasOwnProperty(extension)){
        contentType =  extensionsTypesMap[extension]
      }
      
    } else contentType = 'text/plain_text'

    if(!this.props.displayRaw){
      if(contentType.startsWith('image/')){
        view = (
          <img 
            src={`data:${contentType};base64,${this.props.base64value}`} 
            alt={this.props.name} />
        )
      } else if (contentType.startsWith('audio/')) {
        view = (
          <audio
            controls
            src={`data:${contentType};base64,${this.props.base64value}`} >
                  The <code>audio</code> element is not supported by your browser.
          </audio>
        )
      } else if (contentType.startsWith('video/')){
        view = (
          <video controls src={`data:${contentType};base64,${this.props.base64value}`}>
            The <code>video</code> element is not supported by your browser.
          </video>
        )
      } else {
        // Check if mimetype end contains a loaded ace mode
        let editorLanguage = contentType.split('/')[1];
        if(editorLanguage.lastIndexOf('+') >= 0){
          editorLanguage = editorLanguage.substr(editorLanguage.lastIndexOf('+') + 1);
        }
        if(!languages.includes(editorLanguage)){
          editorLanguage = 'plain_text'
        }

        view = (
          <div classNames="code-input">
            <button>{trans('view_editor_settings')}</button>
            <div classNames="code-input-settings">
              <label htmlFor='theme-selector'>{trans('change_theme'),{},'quiz'}</label>
              <select name='theme-selector' id='theme-selector' onChange={(e)=>this.onThemeChange(e.target.value)}>
                {themes.map(
                  (theme) => <option key={theme} value={theme}>{theme}</option>
                )}
              </select>

              <label htmlFor='font-size'>Font size</label>
              <input type='number' id='font-size' onChange={(e)=>this.onFontSizeChange(e.target.value)}/>
            </div>

            <AceEditor
              mode={editorLanguage}
              theme={this.props.theme}
              onChange={this.props.onChange()}
              value={this.state.value}
              name={`item_${this.props.id ? this.props.id : 'unregistered'}`}
              fontSize={this.props.fontSize}
              editorProps={{ $blockScrolling: true }}
              setOptions={{
                enableBasicAutocompletion: true,
                enableLiveAutocompletion: true,
                enableSnippets: true
              }}
            />
          </div>
        );
      }
      // TODO Add a html previewer that rebuild the html in an iframe by parsing and 
      // rebuilding a selfcontained html file
      
    } else { // Display raw data
      // Check if mimetype end contains a loaded ace mode
      let editorLanguage = contentType.split('/')[1];
      if(editorLanguage.lastIndexOf('+') >= 0){
        editorLanguage = editorLanguage.substr(editorLanguage.lastIndexOf('+') + 1);
      }
      if(!languages.includes(editorLanguage)){
        editorLanguage = 'plain_text';
      }

      view = (
        <AceEditor
          mode={editorLanguage}
          theme={this.state.theme}
          onChange={(value)=> {this.props.onChange(base64EncodeUnicode(value))}}
          value={value}
          name={`item_${this.props.id ? this.props.id : 'unregistered'}`}
          fontSize={this.state.fontSize}
          editorProps={{ $blockScrolling: true }}
          setOptions={{
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
          }}
        />);
    }  

    return view;
  }
}

CodeContentEditor.propTypes = {
  id:T.string,
  name:T.string,
  type:T.string, // forced displayed type (else use name)
  displayRaw: T.boolean, // force display data as text
  onChange:T.func,
  base64value:T.string
}

CodeContentEditor.defaultProps = {
  type:'text/plain',
  displayRaw: true,
  base64value:'',
  theme:'monokai',
  fontSize: 12
}


export {
  CodeContentEditor
}