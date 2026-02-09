
// droparea_controller.js

import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect() {
        this.element.addEventListener('dropzone:connect', this._onConnect);
        this.element.addEventListener('dropzone:change', this._onChange);
        this.element.addEventListener('dropzone:clear', this._onClear);
        this.element.addEventListener('dragleave', this.onDragLeave.bind(this));
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side-effects
        this.element.removeEventListener('dropzone:connect', this._onConnect);
        this.element.removeEventListener('dropzone:change', this._onChange);
        this.element.removeEventListener('dropzone:clear', this._onClear);
        this.element.removeEventListener('dragleave', this.onDragLeave.bind(this));
    }

    _onConnect(e) {
        // The dropzone was just created

    }

    _onChange(e) {
        // The dropzone just changed
    }

    _onClear(e) {
        // The dropzone has just been cleared
    }

    onDragLeave(e) {
        // The dropzone has just been left.
        if (e.dataTransfer.dropEffect === 'none')
        {
          let dropArea = e.target.closest('.dropzone-container'),
          preview = dropArea.querySelector('.dropzone-preview'),
          clearBtn = dropArea.querySelector('.dropzone-preview-button'),
          placeholder = dropArea.querySelector('.dropzone-placeholder');
          placeholder.style.display = 'block';
          preview.style.display = 'none';
        }
    }

}
