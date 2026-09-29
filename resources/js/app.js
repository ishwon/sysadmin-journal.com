import Alpine from 'alpinejs';
import { marked } from 'marked';
import { postForm } from './post-form';

// The dashboard editor's live preview renders markdown in the browser.
window.marked = marked;

window.Alpine = Alpine;
Alpine.data('postForm', postForm);
Alpine.start();
