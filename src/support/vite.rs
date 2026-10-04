//! Resolve the Vite manifest (or dev server) into `<link>`/`<script>` tags —
//! what Blade's `@vite([...])` directive did.

use std::collections::HashMap;
use std::net::{TcpStream, ToSocketAddrs};
use std::path::{Path, PathBuf};
use std::sync::Mutex;
use std::time::{Duration, Instant};

use serde::Deserialize;

#[derive(Clone, Debug, Deserialize)]
struct Chunk {
    file: String,
    #[serde(default)]
    css: Vec<String>,
}

#[derive(Debug, Default)]
struct HotState {
    checked_at: Option<Instant>,
    url: Option<String>,
}

#[derive(Debug, Default)]
pub struct Vite {
    manifest: HashMap<String, Chunk>,
    public_dir: PathBuf,
    hot: Mutex<HotState>,
}

impl Vite {
    pub fn load(public_dir: &Path) -> Self {
        let candidates = [
            public_dir.join("build/.vite/manifest.json"),
            public_dir.join("build/manifest.json"),
        ];
        let mut manifest = HashMap::new();
        for path in &candidates {
            let Ok(json) = std::fs::read_to_string(path) else {
                continue;
            };
            match serde_json::from_str::<HashMap<String, Chunk>>(&json) {
                Ok(parsed) => {
                    manifest = parsed;
                    tracing::info!("Vite manifest: {}", path.display());
                    break;
                }
                Err(e) => {
                    tracing::warn!("ignoring unreadable Vite manifest {}: {e}", path.display())
                }
            }
        }
        if manifest.is_empty() {
            tracing::warn!(
                "Vite manifest not found under {}/build; run `npm run build`",
                public_dir.display()
            );
        }
        for entry in ["resources/css/app.css", "resources/js/app.js"] {
            match manifest.get(entry) {
                Some(chunk) if public_dir.join("build").join(&chunk.file).is_file() => {
                    tracing::info!("asset {entry} -> /build/{}", chunk.file);
                }
                Some(chunk) => tracing::warn!(
                    "asset {entry} -> /build/{} is listed in the manifest but missing on disk; rebuild with `npm run build`",
                    chunk.file
                ),
                None if !manifest.is_empty() => {
                    tracing::warn!(
                        "asset {entry} is not in the Vite manifest; is public/build from an older build?"
                    )
                }
                None => {}
            }
        }
        Self {
            manifest,
            public_dir: public_dir.to_path_buf(),
            hot: Mutex::new(HotState::default()),
        }
    }

    /// The dev server URL from `public/hot`, if the file exists and the server
    /// answers. Re-checked every few seconds so a stale file left behind by a
    /// killed `npm run dev` falls back to the built assets instead of a blank page.
    fn hot_url(&self) -> Option<String> {
        let Ok(raw) = std::fs::read_to_string(self.public_dir.join("hot")) else {
            return None;
        };
        let url = raw.trim().trim_end_matches('/').to_string();
        let mut state = self.hot.lock().unwrap_or_else(|e| e.into_inner());
        let fresh = state
            .checked_at
            .map(|t| t.elapsed() < Duration::from_secs(5))
            .unwrap_or(false);
        if fresh {
            return state.url.clone();
        }
        let reachable = dev_server_reachable(&url);
        if !reachable {
            tracing::warn!(
                "public/hot points at {url} but nothing is listening there; serving built assets instead. Delete public/hot if `npm run dev` is not running."
            );
        }
        state.checked_at = Some(Instant::now());
        state.url = reachable.then_some(url);
        state.url.clone()
    }

    /// Render tags for the given entry points.
    pub fn tags(&self, entries: &[&str]) -> String {
        if let Some(base) = self.hot_url() {
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

fn dev_server_reachable(url: &str) -> bool {
    let without_scheme = url.split("://").nth(1).unwrap_or(url);
    let host_port = without_scheme.split('/').next().unwrap_or("");
    let (host, port) = match host_port.rsplit_once(':') {
        Some((h, p)) => (h, p.parse::<u16>().unwrap_or(80)),
        None => (host_port, if url.starts_with("https") { 443 } else { 80 }),
    };
    let host = host.trim_matches(['[', ']']);
    let Ok(addrs) = (host, port).to_socket_addrs() else {
        return false;
    };
    addrs
        .into_iter()
        .any(|addr| TcpStream::connect_timeout(&addr, Duration::from_millis(300)).is_ok())
}
