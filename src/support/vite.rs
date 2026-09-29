//! Resolve the Vite manifest (or dev server) into `<link>`/`<script>` tags —
//! what Blade's `@vite([...])` directive did.

use std::collections::HashMap;
use std::path::Path;

use serde::Deserialize;

#[derive(Clone, Debug, Deserialize)]
struct Chunk {
    file: String,
    #[serde(default)]
    css: Vec<String>,
}

#[derive(Clone, Debug, Default)]
pub struct Vite {
    manifest: HashMap<String, Chunk>,
    public_dir: std::path::PathBuf,
}

impl Vite {
    pub fn load(public_dir: &Path) -> Self {
        let candidates = [
            public_dir.join("build/.vite/manifest.json"),
            public_dir.join("build/manifest.json"),
        ];
        let manifest = candidates
            .iter()
            .find_map(|p| std::fs::read_to_string(p).ok())
            .and_then(|json| serde_json::from_str::<HashMap<String, Chunk>>(&json).ok())
            .unwrap_or_default();
        if manifest.is_empty() {
            tracing::warn!(
                "Vite manifest not found under {}; run `npm run build`",
                public_dir.display()
            );
        }
        Self {
            manifest,
            public_dir: public_dir.to_path_buf(),
        }
    }

    /// Render tags for the given entry points.
    pub fn tags(&self, entries: &[&str]) -> String {
        if let Ok(hot) = std::fs::read_to_string(self.public_dir.join("hot")) {
            let base = hot.trim().trim_end_matches('/').to_string();
            let mut out =
                format!("<script type=\"module\" src=\"{base}/@vite/client\"></script>\n");
            for entry in entries {
                out.push_str(&self.tag(&format!("{base}/{entry}")));
                out.push('\n');
            }
            return out;
        }

        let mut out = String::new();
        let mut seen_css: Vec<String> = Vec::new();
        for entry in entries {
            let Some(chunk) = self.manifest.get(*entry) else {
                continue;
            };
            for css in &chunk.css {
                if !seen_css.contains(css) {
                    seen_css.push(css.clone());
                    out.push_str(&self.tag(&format!("/build/{css}")));
                    out.push('\n');
                }
            }
            out.push_str(&self.tag(&format!("/build/{}", chunk.file)));
            out.push('\n');
        }
        out
    }

    fn tag(&self, url: &str) -> String {
        if url.ends_with(".css") {
            format!("<link rel=\"stylesheet\" href=\"{url}\">")
        } else {
            format!("<script type=\"module\" src=\"{url}\"></script>")
        }
    }
}
