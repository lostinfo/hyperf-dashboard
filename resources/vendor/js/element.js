import Vue from 'vue'
import {
  Message,
} from 'element-ui'

Vue.prototype.$errors = (message, errors) => {
  let html = '<div class="message-errors">' +
    '<p>' + message + '</p>' +
    '<ul>'
  for (let column_key in errors) {
    for (let error_msg of errors[column_key]) {
      html += '<li>' + error_msg +'</li>'
    }
  }
  html += '</ul></div>'
  Message({
    type: 'error',
    dangerouslyUseHTMLString: true,
    message: html,
    duration: 10000,
    showClose: true
  })
}
