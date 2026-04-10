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

        showPreview: function () {
            this.activeTab = 'preview';
            var self = this;
            this.$nextTick(function () { self.renderPreview(); });
        },

        renderPreview: function () {
            var raw = document.getElementById('content').value || '';
            var html = this.contentFormat === 'markdown' ? marked.parse(raw) : raw;
            var featureImage = document.getElementById('feature_image').value || '';
            var featureAlt = document.getElementById('feature_image_alt').value || '';
            var featureCaption = document.getElementById('feature_image_caption').value || '';

            var figureHtml = '';
            if (featureImage) {
                figureHtml = '<figure class="mt-6"><img class="rounded" src="' + this.escapeHtml(featureImage) + '" alt="' + this.escapeHtml(featureAlt) + '">';
                if (featureCaption) {
                    figureHtml += '<figcaption class="text-left">' + featureCaption + '</figcaption>';
                }
                figureHtml += '</figure>';
            }

            var titleHtml = this.escapeHtml(this.title || 'Untitled');

            var parts = [];
            parts.push('<!DOCTYPE html><html><head>');
            parts.push('<meta charset="UTF-8">');
            parts.push('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
            parts.push('<link rel="preconnect" href="https://fonts.googleapis.com">');
            parts.push('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
            parts.push('<link rel="stylesheet" href="https://use.typekit.net/ikg3vvf.css">');
            parts.push('<script src="https://cdn.tailwindcss.com?plugins=typography"><' + '/script>');
            parts.push('<style>');
            parts.push('body{font-family:Lato,sans-serif}');
            parts.push('figure{display:inline-block}figure img{vertical-align:top}');
            parts.push('figure figcaption{text-align:center;font-family:"Courier New",Courier,monospace;font-size:9px}');
            parts.push('code{background-color:#e5e7eb;display:inline-flex;padding:0.125rem 0.75rem;border-radius:0.25rem}');
            parts.push('blockquote{font-size:21px;line-height:110%}');
            parts.push('.kg-width-wide{max-width:1040px;margin:0 auto}.kg-width-full{max-width:none}');
            parts.push('.kg-image-card{margin:1.5em 0}.kg-image-card img{margin:0 auto}');
            parts.push('.kg-embed-card{display:flex;justify-content:center;margin:1.5em 0}.kg-embed-card iframe{width:100%}');
            parts.push('.kg-gallery-container{display:flex;flex-direction:column;gap:0.75em;margin:1.5em 0}');
            parts.push('.kg-gallery-row{display:flex;gap:0.75em}.kg-gallery-row img{flex:1;height:auto;object-fit:cover}');
            parts.push('.kg-gallery-image img{width:100%;height:auto}');
            parts.push('.kg-bookmark-card{border:1px solid #e5e7eb;border-radius:0.375rem;overflow:hidden;margin:1.5em 0}');
            parts.push('.kg-bookmark-card a{display:flex;text-decoration:none;color:inherit}');
            parts.push('.kg-bookmark-content{padding:1rem;flex:1}.kg-bookmark-title{font-weight:600}');
            parts.push('.kg-bookmark-description{margin-top:0.5rem;font-size:0.875rem;color:#6b7280}');
            parts.push('.kg-bookmark-metadata{margin-top:0.5rem;font-size:0.75rem;color:#9ca3af}');
            parts.push('.kg-bookmark-thumbnail{width:200px}.kg-bookmark-thumbnail img{width:100%;height:100%;object-fit:cover}');
            parts.push('</style></head><body>');
            parts.push('<div class="relative py-12 bg-white overflow-hidden">');
            parts.push('<div class="relative px-4 sm:px-6 lg:px-8">');
            parts.push('<div class="text-lg max-w-4xl mx-auto">');
            parts.push('<h1><span class="mt-2 block text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">');
            parts.push(titleHtml);
            parts.push('</span></h1>');
            parts.push(figureHtml);
            parts.push('</div>');
            parts.push('<div class="mt-6 max-w-4xl prose prose-emerald prose-lg text-gray-600 mx-auto">');
            parts.push(html);
            parts.push('</div></div></div></body></html>');

            this.$refs.previewFrame.srcdoc = parts.join('');
        },

        escapeHtml: function (text) {
            var d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }
    };
}
