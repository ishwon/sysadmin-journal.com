function postForm(config) {
    return {
        title: config.title,
        slug: config.slug,
        slugManual: Boolean(config.slug),
        contentFormat: config.contentFormat,
        activeTab: 'editor',

        generateSlug: async function () {
            if (this.slugManual && this.slug) return;
            var response = await fetch(config.slugCheckUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ title: this.title, exclude_id: config.postId })
            });
            var data = await response.json();
            this.slug = data.slug;
        },

        wordCount: function () {
            var el = document.getElementById('content');
            if (!el) return '';
            var words = (el.value || '').trim().split(/\s+/).filter(Boolean).length;
            return words.toLocaleString() + ' words · ' + Math.max(1, Math.round(words / 220)) + ' min';
        },

        /* — Insert image — */
        imageSnippet: function (url, alt, caption) {
            if (this.contentFormat === 'markdown') {
                // CommonMark: title attribute carries the caption; the server turns it into <figcaption>.
                return '![' + (alt || '') + '](' + url + (caption ? ' "' + caption.replace(/"/g, '\\"') + '"' : '') + ')';
            }
            var html = '<figure><img src="' + this.escapeAttr(url) + '" alt="' + this.escapeAttr(alt || '') + '">';
            if (caption) html += '<figcaption>' + this.escapeHtml(caption) + '</figcaption>';
            return html + '</figure>';
        },

        insertImage: function (url, alt, caption) {
            if (!url) return;
            var ta = document.getElementById('content');
            var snippet = this.imageSnippet(url, alt, caption);
            var start = ta.selectionStart, end = ta.selectionEnd, v = ta.value;
            var before = v.slice(0, start), after = v.slice(end);
            var pad = before.length && !/\n\n$/.test(before) ? (/\n$/.test(before) ? '\n' : '\n\n') : '';
            var tail = after.length && !/^\n/.test(after) ? '\n\n' : '\n';
            ta.value = before + pad + snippet + tail + after;
            var caret = (before + pad + snippet).length;
            ta.focus();
            ta.setSelectionRange(caret, caret);
            ta.style.height = 'auto';
            ta.style.height = ta.scrollHeight + 'px';
        },

        /* — Preview — */
        showPreview: function () {
            this.activeTab = 'preview';
            var self = this;
            this.$nextTick(function () { self.renderPreview(); });
        },

        renderPreview: function () {
            var raw = document.getElementById('content').value || '';
            var html = this.contentFormat === 'markdown' ? this.figuresFromTitles(marked.parse(raw)) : raw;
            var featureImage = document.getElementById('feature_image').value || '';
            var featureAlt = document.getElementById('feature_image_alt').value || '';
            var featureCaption = document.getElementById('feature_image_caption').value || '';

            var figureHtml = '';
            if (featureImage) {
                figureHtml = '<figure class="feature"><img src="' + this.escapeAttr(featureImage) + '" alt="' + this.escapeAttr(featureAlt) + '">';
                if (featureCaption) figureHtml += '<figcaption>' + featureCaption + '</figcaption>';
                figureHtml += '</figure>';
            }

            var css = document.querySelector('link[href*="app"][rel="stylesheet"]');
            var parts = [];
            parts.push('<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">');
            parts.push('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,ital,wght@8..60,0,400;8..60,0,600;8..60,1,400&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&family=JetBrains+Mono:wght@400;500&display=swap">');
            if (css) parts.push('<link rel="stylesheet" href="' + css.href + '">');
            parts.push('<style>body{margin:0;background:#fff}.wrap{max-width:68ch;margin:0 auto;padding:48px 24px}h1{font-family:Newsreader,Georgia,serif;font-weight:500;font-size:44px;line-height:1.1;letter-spacing:-.02em;color:#161b30;margin:0}.feature{margin:32px 0 0}.feature img{width:100%;border-bottom:1px solid #e6e8f0}.feature figcaption{margin-top:8px;font-family:"JetBrains Mono",monospace;font-size:12px;color:#6b7596}</style>');
            parts.push('</head><body><div class="wrap"><h1>' + this.escapeHtml(this.title || 'Untitled') + '</h1>' + figureHtml);
            parts.push('<div class="prose-brand" style="margin-top:40px">' + html + '</div></div></body></html>');

            this.$refs.previewFrame.srcdoc = parts.join('');
        },

        // Mirror of App\Support\Markdown::wrapFigures for the live preview.
        figuresFromTitles: function (html) {
            return html.replace(/<p>\s*(<img\b[^>]*\btitle="([^"]*)"[^>]*>)\s*<\/p>/g, function (_, img, title) {
                return '<figure>' + img.replace(/\s*title="[^"]*"/, '') + '<figcaption>' + title + '</figcaption></figure>';
            });
        },

        escapeHtml: function (text) {
            var d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        },

        escapeAttr: function (text) {
            return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
        }
    };
}
