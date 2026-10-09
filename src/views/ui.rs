//! The UI component library (the former `components/ui/*.blade.php`), as Rust
//! builders that return HTML. Container components expose `open()`/`close()`
//! halves so page templates can put their own markup in between.

use super::esc;
use crate::support::pagination::Page;

fn attr(name: &str, value: &str) -> String {
    format!(" {name}=\"{}\"", esc(value))
}

fn opt_attr(name: &str, value: &Option<String>) -> String {
    value.as_ref().map(|v| attr(name, v)).unwrap_or_default()
}

// ---- button ------------------------------------------------------------------

#[derive(Clone, Debug)]
pub struct Button {
    pub variant: &'static str,
    pub size: &'static str,
    pub href: Option<String>,
    pub kind: &'static str,
    pub loading: bool,
    pub class: String,
    pub attrs: String,
}

impl Default for Button {
    fn default() -> Self {
        Self {
            variant: "primary",
            size: "md",
            href: None,
            kind: "button",
            loading: false,
            class: String::new(),
            attrs: String::new(),
        }
    }
}

impl Button {
    pub fn new(variant: &'static str) -> Self {
        Self {
            variant,
            ..Default::default()
        }
    }
    pub fn size(mut self, size: &'static str) -> Self {
        self.size = size;
        self
    }
    pub fn href(mut self, href: impl Into<String>) -> Self {
        self.href = Some(href.into());
        self
    }
    pub fn submit(mut self) -> Self {
        self.kind = "submit";
        self
    }
    pub fn class(mut self, class: &str) -> Self {
        self.class = class.into();
        self
    }
    pub fn attrs(mut self, attrs: &str) -> Self {
        self.attrs = attrs.into();
        self
    }
    pub fn loading(mut self) -> Self {
        self.loading = true;
        self
    }

    pub fn html(&self, body: &str) -> String {
        let variant = match self.variant {
            "secondary" => {
                "bg-white text-ink-900 border border-ink-200 hover:bg-surface-50 hover:border-ink-300 disabled:text-ink-400 disabled:bg-white focus-visible:shadow-[var(--shadow-focus)]"
            }
            "ghost" => {
                "text-ink-700 hover:bg-surface-50 hover:text-ink-900 disabled:text-ink-400 focus-visible:shadow-[var(--shadow-focus)]"
            }
            "danger" => {
                "bg-danger-500 text-white hover:bg-danger-600 disabled:bg-danger-300 focus-visible:shadow-[0_0_0_3px_rgb(177_74_42_/_0.32)]"
            }
            "warning" => {
                "bg-warning-500 text-white hover:bg-warning-600 focus-visible:shadow-[var(--shadow-focus)]"
            }
            "ink" => {
                "bg-ink-800 text-white hover:bg-ink-900 disabled:bg-ink-400 focus-visible:shadow-[var(--shadow-focus)]"
            }
            _ => {
                "bg-accent-600 text-white hover:bg-accent-700 disabled:bg-accent-300 focus-visible:shadow-[var(--shadow-focus)]"
            }
        };
        let size = match self.size {
            "sm" => "h-8 px-3 text-[13px]",
            "lg" => "h-12 px-6 text-[15px]",
            _ => "h-10 px-4 text-sm",
        };
        let classes = format!(
            "inline-flex items-center justify-center gap-2 font-semibold rounded-sm transition-colors duration-[var(--duration-quick)] ease-[var(--ease-brand)] focus-visible:outline-none disabled:cursor-not-allowed {variant} {size} {}",
            self.class
        );
        match &self.href {
            Some(href) => format!(
                "<a href=\"{}\" class=\"{classes}\" {}>{body}</a>",
                esc(href),
                self.attrs
            ),
            None => {
                let spinner = if self.loading {
                    "<svg class=\"animate-spin h-4 w-4\" viewBox=\"0 0 24 24\" fill=\"none\" aria-hidden=\"true\"><circle class=\"opacity-25\" cx=\"12\" cy=\"12\" r=\"10\" stroke=\"currentColor\" stroke-width=\"4\" /><path class=\"opacity-75\" fill=\"currentColor\" d=\"M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z\" /></svg>"
                } else {
                    ""
                };
                format!(
                    "<button type=\"{}\"{} class=\"{classes}\" {}>{spinner}{body}</button>",
                    self.kind,
                    if self.loading { " disabled" } else { "" },
                    self.attrs
                )
            }
        }
    }
}

pub fn button(variant: &'static str) -> Button {
    Button::new(variant)
}

// ---- badge -------------------------------------------------------------------

pub fn badge(tone: &str, size: &str, body: &str) -> String {
    let tone = match tone {
        "success" => "bg-sage-100 text-sage-700",
        "warning" => "bg-warning-100 text-warning-700",
        "danger" => "bg-danger-100 text-danger-700",
        "info" => "bg-sky-100 text-ink-700 ring-1 ring-inset ring-sky-200",
        "ink" => "bg-ink-800 text-white",
        _ => "bg-surface-100 text-ink-500",
    };
    let size = match size {
        "sm" => "px-1.5 py-px text-[10px]",
        "lg" => "px-2.5 py-0.5 text-xs",
        _ => "px-2 py-px text-[11px]",
    };
    format!(
        "<span class=\"inline-flex items-center gap-1.5 rounded-xs font-mono lowercase tracking-wide {tone} {size}\">{body}</span>"
    )
}

/// The published / draft pill used in every listing.
pub fn status_badge(status: &str) -> String {
    badge(
        if status == "published" {
            "success"
        } else {
            "neutral"
        },
        "md",
        &esc(status),
    )
}

// ---- alert -------------------------------------------------------------------

pub fn alert(tone: &str, title: Option<&str>, dismissible: bool, body: &str) -> String {
    let tone = match tone {
        "success" => "border-l-sage-500 bg-sage-50 text-sage-700",
        "warning" => "border-l-warning-500 bg-warning-50 text-warning-700",
        "danger" => "border-l-danger-500 bg-danger-50 text-danger-700",
        _ => "border-l-ink-400 bg-surface-50 text-ink-800",
    };
    let mut out = format!(
        "<div {}class=\"border-l-4 {tone} rounded-r-sm px-4 py-3 flex items-start gap-3\"><div class=\"flex-1 text-sm\">",
        if dismissible {
            "x-data=\"{ shown: true }\" x-show=\"shown\" "
        } else {
            ""
        }
    );
    if let Some(title) = title {
        out.push_str(&format!("<h4 class=\"font-semibold\">{}</h4>", esc(title)));
    }
    out.push_str(&format!(
        "<div class=\"{}\">{body}</div></div>",
        if title.is_some() { "mt-1" } else { "" }
    ));
    if dismissible {
        out.push_str("<button type=\"button\" @click=\"shown = false\" class=\"shrink-0 opacity-60 hover:opacity-100 transition-opacity\" aria-label=\"Dismiss\"><svg class=\"w-4 h-4\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\"><path fill-rule=\"evenodd\" d=\"M4.28 3.22a.75.75 0 00-1.06 1.06L8.94 10l-5.72 5.72a.75.75 0 101.06 1.06L10 11.06l5.72 5.72a.75.75 0 101.06-1.06L11.06 10l5.72-5.72a.75.75 0 00-1.06-1.06L10 8.94 4.28 3.22z\" clip-rule=\"evenodd\" /></svg></button>");
    }
    out.push_str("</div>");
    out
}

// ---- card --------------------------------------------------------------------

#[derive(Clone, Debug, Default)]
pub struct Card {
    pub title: Option<String>,
    pub subtitle: Option<String>,
    pub padding: &'static str,
    pub hoverable: bool,
    pub class: String,
    pub actions: Option<String>,
    pub footer: Option<String>,
}

impl Card {
    pub fn new() -> Self {
        Self {
            padding: "md",
            ..Default::default()
        }
    }
    pub fn title(mut self, title: &str) -> Self {
        self.title = Some(title.into());
        self
    }
    pub fn subtitle(mut self, subtitle: &str) -> Self {
        self.subtitle = Some(subtitle.into());
        self
    }
    pub fn padding(mut self, padding: &'static str) -> Self {
        self.padding = padding;
        self
    }
    pub fn hoverable(mut self) -> Self {
        self.hoverable = true;
        self
    }
    pub fn class(mut self, class: &str) -> Self {
        self.class = class.into();
        self
    }
    pub fn actions(mut self, html: String) -> Self {
        self.actions = Some(html);
        self
    }
    pub fn footer(mut self, html: String) -> Self {
        self.footer = Some(html);
        self
    }

    pub fn open(&self) -> String {
        let pad = match self.padding {
            "none" => "",
            "sm" => "p-4",
            "lg" => "p-8",
            _ => "p-6",
        };
        let mut out = format!(
            "<div class=\"rounded-md bg-white border border-ink-100 {} {}\">",
            if self.hoverable {
                "transition-shadow duration-[var(--duration-base)] hover:shadow-md"
            } else {
                ""
            },
            self.class
        );
        if self.title.is_some() || self.subtitle.is_some() {
            out.push_str("<div class=\"px-6 py-4 border-b border-ink-100 flex items-center justify-between gap-4\"><div>");
            if let Some(title) = &self.title {
                out.push_str(&format!(
                    "<h3 class=\"eyebrow\">/{}/</h3>",
                    esc(&super::slug(title))
                ));
            }
            if let Some(subtitle) = &self.subtitle {
                out.push_str(&format!(
                    "<p class=\"text-sm text-ink-500 mt-1\">{}</p>",
                    esc(subtitle)
                ));
            }
            out.push_str("</div>");
            if let Some(actions) = &self.actions {
                out.push_str(&format!(
                    "<div class=\"shrink-0 flex items-center gap-2\">{actions}</div>"
                ));
            }
            out.push_str("</div>");
        }
        out.push_str(&format!("<div class=\"{pad}\">"));
        out
    }

    pub fn close(&self) -> String {
        let mut out = String::from("</div>");
        if let Some(footer) = &self.footer {
            out.push_str(&format!("<div class=\"px-6 py-3 border-t border-ink-100 bg-surface-50 rounded-b-md\">{footer}</div>"));
        }
        out.push_str("</div>");
        out
    }

    pub fn html(&self, body: &str) -> String {
        format!("{}{body}{}", self.open(), self.close())
    }
}

pub fn card() -> Card {
    Card::new()
}

// ---- form controls -----------------------------------------------------------

fn control_border(error: bool) -> &'static str {
    if error {
        "border-danger-500 focus:border-danger-500 focus:shadow-[0_0_0_3px_rgb(177_74_42_/_0.32)]"
    } else {
        "border-ink-200 focus:border-accent-500 focus:shadow-[var(--shadow-focus)]"
    }
}

fn label_html(id: &str, label: &Option<String>) -> String {
    label
        .as_ref()
        .map(|l| format!("<label for=\"{}\" class=\"block text-[13px] font-medium text-ink-900 mb-1.5\">{}</label>", esc(id), esc(l)))
        .unwrap_or_default()
}

fn hint_html(error: &Option<String>, hint: &Option<String>) -> String {
    if let Some(error) = error {
        format!(
            "<p class=\"mt-1.5 text-xs text-danger-600\">{}</p>",
            esc(error)
        )
    } else if let Some(hint) = hint {
        format!(
            "<p class=\"mt-1.5 font-mono text-[11px] text-ink-400\">{}</p>",
            esc(hint)
        )
    } else {
        String::new()
    }
}

#[derive(Clone, Debug, Default)]
pub struct Input {
    pub name: String,
    pub label: Option<String>,
    pub hint: Option<String>,
    pub error: Option<String>,
    pub kind: String,
    pub value: Option<String>,
    pub prefix: Option<String>,
    pub suffix: Option<String>,
    pub class: String,
    pub attrs: String,
    pub id: Option<String>,
    pub placeholder: Option<String>,
    pub required: bool,
    pub disabled: bool,
}

impl Input {
    pub fn new(name: &str) -> Self {
        Self {
            name: name.into(),
            kind: "text".into(),
            ..Default::default()
        }
    }
    pub fn label(mut self, v: &str) -> Self {
        self.label = Some(v.into());
        self
    }
    pub fn hint(mut self, v: &str) -> Self {
        self.hint = Some(v.into());
        self
    }
    pub fn hint_opt(mut self, v: Option<&str>) -> Self {
        self.hint = v.map(|s| s.into());
        self
    }
    pub fn error(mut self, v: &str) -> Self {
        self.error = Some(v.into());
        self
    }
    pub fn kind(mut self, v: &str) -> Self {
        self.kind = v.into();
        self
    }
    pub fn value(mut self, v: impl Into<String>) -> Self {
        self.value = Some(v.into());
        self
    }
    pub fn prefix(mut self, v: &str) -> Self {
        self.prefix = Some(v.into());
        self
    }
    pub fn suffix(mut self, v: &str) -> Self {
        self.suffix = Some(v.into());
        self
    }
    pub fn class(mut self, v: &str) -> Self {
        self.class = v.into();
        self
    }
    pub fn attrs(mut self, v: &str) -> Self {
        self.attrs = v.into();
        self
    }
    pub fn id(mut self, v: &str) -> Self {
        self.id = Some(v.into());
        self
    }
    pub fn placeholder(mut self, v: &str) -> Self {
        self.placeholder = Some(v.into());
        self
    }
    pub fn required(mut self, v: bool) -> Self {
        self.required = v;
        self
    }
    pub fn disabled(mut self) -> Self {
        self.disabled = true;
        self
    }

    pub fn html(&self) -> String {
        let id = self.id.clone().unwrap_or_else(|| self.name.clone());
        let classes = format!(
            "w-full rounded-sm border bg-white px-3 py-2 text-sm text-ink-900 placeholder:text-ink-300 transition-colors duration-[var(--duration-quick)] focus:outline-none disabled:bg-surface-50 disabled:text-ink-400 disabled:cursor-not-allowed {}{}{} {}",
            control_border(self.error.is_some()),
            if self.prefix.is_some() {
                " rounded-l-none"
            } else {
                ""
            },
            if self.suffix.is_some() {
                " rounded-r-none"
            } else {
                ""
            },
            self.class
        );
        let input = format!(
            "<input type=\"{}\" name=\"{}\" id=\"{}\"{}{}{}{} class=\"{classes}\" {}>",
            esc(&self.kind),
            esc(&self.name),
            esc(&id),
            opt_attr("value", &self.value),
            opt_attr("placeholder", &self.placeholder),
            if self.required { " required" } else { "" },
            if self.disabled { " disabled" } else { "" },
            self.attrs
        );
        let field = if self.prefix.is_some() || self.suffix.is_some() {
            let prefix = self
                .prefix
                .as_ref()
                .map(|p| format!("<span class=\"inline-flex items-center rounded-l-sm border border-r-0 border-ink-200 bg-surface-50 px-3 font-mono text-xs text-ink-500\">{}</span>", esc(p)))
                .unwrap_or_default();
            let suffix = self
                .suffix
                .as_ref()
                .map(|s| format!("<span class=\"inline-flex items-center rounded-r-sm border border-l-0 border-ink-200 bg-surface-50 px-3 font-mono text-xs text-ink-500\">{}</span>", esc(s)))
                .unwrap_or_default();
            format!("<div class=\"flex\">{prefix}{input}{suffix}</div>")
        } else {
            input
        };
        format!(
            "<div>{}{field}{}</div>",
            label_html(&id, &self.label),
            hint_html(&self.error, &self.hint)
        )
    }
}

pub fn input(name: &str) -> Input {
    Input::new(name)
}

#[derive(Clone, Debug, Default)]
pub struct Textarea {
    pub name: String,
    pub label: Option<String>,
    pub hint: Option<String>,
    pub error: Option<String>,
    pub rows: u32,
    pub mono: bool,
    pub value: String,
    pub class: String,
    pub attrs: String,
    pub placeholder: Option<String>,
    pub id: Option<String>,
}

impl Textarea {
    pub fn new(name: &str) -> Self {
        Self {
            name: name.into(),
            rows: 4,
            ..Default::default()
        }
    }
    pub fn label(mut self, v: &str) -> Self {
        self.label = Some(v.into());
        self
    }
    pub fn hint(mut self, v: &str) -> Self {
        self.hint = Some(v.into());
        self
    }
    pub fn rows(mut self, v: u32) -> Self {
        self.rows = v;
        self
    }
    pub fn mono(mut self) -> Self {
        self.mono = true;
        self
    }
    pub fn value(mut self, v: impl Into<String>) -> Self {
        self.value = v.into();
        self
    }
    pub fn placeholder(mut self, v: &str) -> Self {
        self.placeholder = Some(v.into());
        self
    }
    pub fn class(mut self, v: &str) -> Self {
        self.class = v.into();
        self
    }
    pub fn attrs(mut self, v: &str) -> Self {
        self.attrs = v.into();
        self
    }

    pub fn html(&self) -> String {
        let id = self.id.clone().unwrap_or_else(|| self.name.clone());
        let classes = format!(
            "w-full rounded-sm border bg-white px-3 py-2 text-sm text-ink-900 placeholder:text-ink-300 transition-colors duration-[var(--duration-quick)] focus:outline-none resize-y disabled:bg-surface-50 disabled:text-ink-400 disabled:cursor-not-allowed {}{} {}",
            if self.mono {
                "font-mono leading-relaxed "
            } else {
                ""
            },
            control_border(self.error.is_some()),
            self.class
        );
        format!(
            "<div>{}<textarea name=\"{}\" id=\"{}\" rows=\"{}\"{} class=\"{classes}\" {}>{}</textarea>{}</div>",
            label_html(&id, &self.label),
            esc(&self.name),
            esc(&id),
            self.rows,
            opt_attr("placeholder", &self.placeholder),
            self.attrs,
            esc(&self.value),
            hint_html(&self.error, &self.hint)
        )
    }
}

pub fn textarea(name: &str) -> Textarea {
    Textarea::new(name)
}

#[derive(Clone, Debug, Default)]
pub struct Select {
    pub name: String,
    pub label: Option<String>,
    pub hint: Option<String>,
    pub error: Option<String>,
    pub options: Vec<(String, String)>,
    pub selected: Option<String>,
    pub placeholder: Option<String>,
    pub class: String,
    pub attrs: String,
    pub id: Option<String>,
}

impl Select {
    pub fn new(name: &str) -> Self {
        Self {
            name: name.into(),
            ..Default::default()
        }
    }
    pub fn label(mut self, v: &str) -> Self {
        self.label = Some(v.into());
        self
    }
    pub fn hint(mut self, v: &str) -> Self {
        self.hint = Some(v.into());
        self
    }
    pub fn options(mut self, options: &[(&str, &str)]) -> Self {
        self.options = options
            .iter()
            .map(|(v, l)| (v.to_string(), l.to_string()))
            .collect();
        self
    }
    pub fn options_owned(mut self, options: Vec<(String, String)>) -> Self {
        self.options = options;
        self
    }
    pub fn selected(mut self, v: impl Into<String>) -> Self {
        self.selected = Some(v.into());
        self
    }
    pub fn placeholder(mut self, v: &str) -> Self {
        self.placeholder = Some(v.into());
        self
    }

    pub fn html(&self) -> String {
        let id = self.id.clone().unwrap_or_else(|| self.name.clone());
        let classes = format!(
            "w-full appearance-none rounded-sm border bg-white px-3 py-2 pr-10 text-sm text-ink-900 transition-colors duration-[var(--duration-quick)] focus:outline-none disabled:bg-surface-50 disabled:text-ink-400 disabled:cursor-not-allowed {} {}",
            control_border(self.error.is_some()),
            self.class
        );
        let mut options = String::new();
        if let Some(placeholder) = &self.placeholder {
            options.push_str(&format!("<option value=\"\">{}</option>", esc(placeholder)));
        }
        for (value, label) in &self.options {
            let selected = self.selected.as_deref() == Some(value.as_str());
            options.push_str(&format!(
                "<option value=\"{}\"{}>{}</option>",
                esc(value),
                if selected { " selected" } else { "" },
                esc(label)
            ));
        }
        format!(
            "<div>{}<div class=\"relative\"><select name=\"{}\" id=\"{}\" class=\"{classes}\" {}>{options}</select>\
             <svg class=\"pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-ink-400\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\"><path fill-rule=\"evenodd\" d=\"M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.38a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z\" clip-rule=\"evenodd\" /></svg></div>{}</div>",
            label_html(&id, &self.label),
            esc(&self.name),
            esc(&id),
            self.attrs,
            hint_html(&self.error, &self.hint)
        )
    }
}

pub fn select(name: &str) -> Select {
    Select::new(name)
}

pub fn checkbox(name: &str, label: Option<&str>, checked: bool, hint: Option<&str>) -> String {
    let id = name;
    format!(
        "<label for=\"{id}\" class=\"inline-flex items-start gap-2 cursor-pointer\"><input type=\"checkbox\" name=\"{n}\" id=\"{id}\" value=\"1\"{checked} class=\"mt-0.5 h-4 w-4 rounded-xs border-ink-300 accent-accent-600 focus:shadow-[var(--shadow-focus)] focus:outline-none cursor-pointer\"><span class=\"flex-1\">{label}{hint}</span></label>",
        id = esc(id),
        n = esc(name),
        checked = if checked { " checked" } else { "" },
        label = label
            .map(|l| format!(
                "<span class=\"block text-sm text-ink-900 select-none\">{}</span>",
                esc(l)
            ))
            .unwrap_or_default(),
        hint = hint
            .map(|h| format!(
                "<span class=\"block font-mono text-[11px] text-ink-400 mt-0.5\">{}</span>",
                esc(h)
            ))
            .unwrap_or_default(),
    )
}

pub fn radio(name: &str, value: &str, label: Option<&str>, checked: bool, attrs: &str) -> String {
    let id = format!("{name}_{value}");
    format!(
        "<label for=\"{id}\" class=\"inline-flex items-center gap-2 cursor-pointer\"><input type=\"radio\" name=\"{n}\" id=\"{id}\" value=\"{v}\"{checked} class=\"h-4 w-4 border-ink-300 accent-accent-600 focus:shadow-[var(--shadow-focus)] focus:outline-none cursor-pointer\" {attrs}>{label}</label>",
        id = esc(&id),
        n = esc(name),
        v = esc(value),
        checked = if checked { " checked" } else { "" },
        label = label
            .map(|l| format!(
                "<span class=\"text-sm text-ink-900 select-none\">{}</span>",
                esc(l)
            ))
            .unwrap_or_default(),
    )
}

pub fn toggle(name: &str, label: Option<&str>, hint: Option<&str>, checked: bool) -> String {
    let text = if label.is_some() || hint.is_some() {
        format!(
            "<span class=\"flex-1\">{}{}</span>",
            label.map(|l| format!("<span class=\"block text-sm font-medium text-ink-800 dark:text-ink-100 select-none\">{}</span>", esc(l))).unwrap_or_default(),
            hint.map(|h| format!("<span class=\"block text-xs text-ink-500 dark:text-ink-400 mt-0.5\">{}</span>", esc(h))).unwrap_or_default()
        )
    } else {
        String::new()
    };
    format!(
        "<label for=\"{n}\" class=\"inline-flex items-start gap-3 cursor-pointer group\"><span x-data=\"{{ on: {on} }}\" class=\"relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors duration-[var(--duration-quick)] ease-[var(--ease-brand)] focus-within:shadow-[var(--shadow-focus)]\" :class=\"on ? 'bg-accent-500' : 'bg-ink-300 dark:bg-ink-700'\"><input type=\"checkbox\" name=\"{n}\" id=\"{n}\" value=\"1\" x-model=\"on\" class=\"sr-only peer\"{checked}><span class=\"absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-[var(--duration-quick)] ease-[var(--ease-brand)]\" :class=\"on ? 'translate-x-5' : 'translate-x-0'\"></span></span>{text}</label>",
        n = esc(name),
        on = if checked { "true" } else { "false" },
        checked = if checked { " checked" } else { "" },
    )
}

// ---- navigation --------------------------------------------------------------

pub fn nav_item(href: &str, active: bool, icon: &str, count: Option<i64>, body: &str) -> String {
    format!(
        "<a href=\"{}\" class=\"group flex items-center gap-3 px-3 py-2 rounded-sm text-sm font-medium transition-colors duration-[var(--duration-quick)] {}\"><span class=\"shrink-0 w-[18px] h-[18px] {}\">{icon}</span><span>{body}</span>{}</a>",
        esc(href),
        if active {
            "bg-ink-800 text-white"
        } else {
            "text-ink-200 hover:bg-ink-800 hover:text-white"
        },
        if active {
            "text-accent-300"
        } else {
            "text-ink-400 group-hover:text-ink-200"
        },
        count
            .map(|c| format!(
                "<span class=\"ml-auto font-mono text-[11px] text-ink-400\">{c}</span>"
            ))
            .unwrap_or_default()
    )
}

pub struct Crumb {
    pub label: String,
    pub href: Option<String>,
}

pub fn crumb(label: &str, href: Option<&str>) -> Crumb {
    Crumb {
        label: label.into(),
        href: href.map(|h| h.into()),
    }
}

pub fn breadcrumbs(items: &[Crumb]) -> String {
    let mut out = String::from(
        "<nav class=\"flex\" aria-label=\"Breadcrumb\"><ol class=\"inline-flex items-center gap-1.5 font-mono text-xs tracking-wide\">",
    );
    let last = items.len().saturating_sub(1);
    for (i, item) in items.iter().enumerate() {
        out.push_str("<li class=\"inline-flex items-center gap-1.5\">");
        if i > 0 {
            out.push_str("<span class=\"text-ink-300\" aria-hidden=\"true\">/</span>");
        }
        match &item.href {
            Some(href) if i != last => out.push_str(&format!(
                "<a href=\"{}\" class=\"text-ink-400 hover:text-ink-900 transition-colors\">{}</a>",
                esc(href),
                esc(&item.label)
            )),
            _ => out.push_str(&format!(
                "<span class=\"text-ink-900\">{}</span>",
                esc(&item.label)
            )),
        }
        out.push_str("</li>");
    }
    out.push_str("</ol></nav>");
    out
}

pub fn stat_card(label: &str, value: &str, change: Option<&str>, trend: Option<&str>) -> String {
    let change = change
        .map(|c| {
            let tone = match trend {
                Some("down") => "text-danger-600",
                Some("up") => "text-sage-500",
                _ => "text-ink-400",
            };
            format!(
                "<p class=\"mt-2 font-mono text-[11px] {tone}\">{}</p>",
                esc(c)
            )
        })
        .unwrap_or_default();
    format!(
        "<div class=\"py-6 pr-6 mr-6 border-r border-ink-100 last:border-r-0 last:mr-0 last:pr-0\"><p class=\"font-mono text-[11px] lowercase tracking-wider text-ink-400\">{}</p><p class=\"mt-2 font-display text-4xl leading-none text-ink-900\">{}</p>{change}</div>",
        esc(label),
        esc(value)
    )
}

// ---- table -------------------------------------------------------------------

pub fn table_open(columns: &[&str]) -> String {
    let mut out = String::from(
        "<div class=\"overflow-x-auto\"><table class=\"w-full text-sm border-collapse\"><thead><tr>",
    );
    for column in columns {
        out.push_str(&format!(
            "<th scope=\"col\" class=\"py-3 first:pl-0 px-4 text-left font-mono text-[11px] font-medium lowercase tracking-wide text-ink-400 whitespace-nowrap border-b border-ink-100\">{}</th>",
            esc(column)
        ));
    }
    out.push_str("</tr></thead><tbody class=\"[&_tr]:border-b [&_tr]:border-surface-50 [&_tr:hover]:bg-surface-50 [&_td]:py-3.5 [&_td]:px-4 [&_td:first-child]:pl-0 [&_td:last-child]:pr-0\">");
    out
}

pub fn table_close(footer: Option<String>) -> String {
    let mut out = String::from("</tbody></table>");
    if let Some(footer) = footer {
        out.push_str(&format!("<div class=\"pt-4 meta\">{footer}</div>"));
    }
    out.push_str("</div>");
    out
}

pub fn empty_state(title: &str, description: Option<&str>, action: Option<String>) -> String {
    format!(
        "<div class=\"flex flex-col items-center justify-center text-center px-6 py-16 border border-dashed border-ink-200 rounded-md\"><p class=\"eyebrow\">/empty/</p><h3 class=\"mt-2 font-display text-2xl text-ink-900\">{}</h3>{}{}</div>",
        esc(title),
        description
            .map(|d| format!(
                "<p class=\"mt-2 font-serif text-base text-ink-500 max-w-sm text-pretty\">{}</p>",
                esc(d)
            ))
            .unwrap_or_default(),
        action
            .map(|a| format!("<div class=\"mt-6\">{a}</div>"))
            .unwrap_or_default()
    )
}

// ---- overlays ----------------------------------------------------------------

/// `direction = "up"` opens the menu above its trigger.
pub fn dropdown_open(
    trigger: &str,
    align: &str,
    width: &str,
    direction: &str,
    class: &str,
) -> String {
    let up = direction == "up";
    let align = match (align, up) {
        ("left", false) => "left-0 origin-top-left",
        ("left", true) => "left-0 origin-bottom-left",
        (_, false) => "right-0 origin-top-right",
        (_, true) => "right-0 origin-bottom-right",
    };
    let width = match width {
        "56" => "w-56",
        "64" => "w-64",
        _ => "w-48",
    };
    format!(
        "<div class=\"relative {class}\" x-data=\"{{ open: false }}\" @click.outside=\"open = false\" @keydown.escape.window=\"open = false\"><div @click=\"open = !open\">{trigger}</div>\
         <div x-show=\"open\" x-transition:enter=\"transition ease-[var(--ease-brand)] duration-[var(--duration-quick)]\" x-transition:enter-start=\"opacity-0 {}\" x-transition:enter-end=\"opacity-100 translate-y-0\" x-transition:leave=\"transition ease-in duration-100\" x-transition:leave-start=\"opacity-100\" x-transition:leave-end=\"opacity-0\" x-cloak class=\"absolute z-40 {} {align} {width} rounded-md bg-white shadow-lg border border-ink-100 py-1\">",
        if up {
            "translate-y-1"
        } else {
            "-translate-y-1"
        },
        if up { "bottom-full mb-2" } else { "mt-2" }
    )
}

pub fn dropdown_close() -> &'static str {
    "</div></div>"
}

pub fn dropdown_item(href: Option<&str>, tone: &str, kind: &str, body: &str) -> String {
    let tone = match tone {
        "danger" => "text-danger-600 hover:bg-danger-50",
        _ => "text-ink-700 hover:bg-surface-50 hover:text-ink-900",
    };
    let classes = format!(
        "flex items-center gap-2 px-4 py-2 text-sm transition-colors duration-[var(--duration-quick)] {tone}"
    );
    match href {
        Some(href) => format!("<a href=\"{}\" class=\"{classes}\">{body}</a>", esc(href)),
        None => format!(
            "<button type=\"{}\" class=\"{classes} w-full text-left\">{body}</button>",
            esc(kind)
        ),
    }
}

pub fn modal_open(name: &str, title: Option<&str>, eyebrow: Option<&str>, size: &str) -> String {
    let size = match size {
        "sm" => "max-w-md",
        "lg" => "max-w-2xl",
        "xl" => "max-w-4xl",
        _ => "max-w-lg",
    };
    let header = title
        .map(|t| {
            format!(
                "<div class=\"flex items-start justify-between gap-4 mb-5\"><div>{}<h3 class=\"display text-[26px] mt-1.5\">{}</h3></div>\
                 <button type=\"button\" @click=\"open = false\" class=\"text-ink-400 hover:text-ink-900 transition-colors\" aria-label=\"Close\"><svg class=\"w-5 h-5\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\"><path fill-rule=\"evenodd\" d=\"M4.28 3.22a.75.75 0 00-1.06 1.06L8.94 10l-5.72 5.72a.75.75 0 101.06 1.06L10 11.06l5.72 5.72a.75.75 0 101.06-1.06L11.06 10l5.72-5.72a.75.75 0 00-1.06-1.06L10 8.94 4.28 3.22z\" clip-rule=\"evenodd\"/></svg></button></div>",
                eyebrow.map(|e| format!("<p class=\"eyebrow\">{}</p>", esc(e))).unwrap_or_default(),
                esc(t)
            )
        })
        .unwrap_or_default();
    format!(
        "<div x-data=\"{{ open: false }}\" x-on:open-modal-{n}.window=\"open = true\" x-on:close-modal-{n}.window=\"open = false\" @keydown.escape.window=\"open = false\" x-cloak>\
         <div x-show=\"open\" x-transition.opacity class=\"fixed inset-0 bg-ink-950/60 z-40\" @click=\"open = false\"></div>\
         <div x-show=\"open\" class=\"fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-6 overflow-y-auto pointer-events-none\">\
         <div x-show=\"open\" x-transition:enter=\"transition ease-[var(--ease-brand)] duration-[var(--duration-base)]\" x-transition:enter-start=\"opacity-0 translate-y-2\" x-transition:enter-end=\"opacity-100 translate-y-0\" x-transition:leave=\"transition ease-in duration-[var(--duration-quick)]\" x-transition:leave-start=\"opacity-100\" x-transition:leave-end=\"opacity-0\" class=\"pointer-events-auto mt-24 w-full {size} rounded-lg bg-white shadow-xl border border-ink-100 p-6\">{header}<div class=\"text-sm text-ink-700\">",
        n = esc(name)
    )
}

pub fn modal_close(footer: Option<String>) -> String {
    format!(
        "</div>{}</div></div></div>",
        footer.map(|f| format!("<div class=\"mt-5 pt-4 border-t border-ink-100 flex items-center justify-end gap-2\">{f}</div>")).unwrap_or_default()
    )
}

pub fn tabs_open(tabs: &[(&str, &str)], default: &str) -> String {
    let mut out = format!(
        "<div x-data=\"{{ active: {} }}\"><div class=\"border-b border-surface-200 dark:border-ink-700\"><nav class=\"flex gap-6 -mb-px\" aria-label=\"Tabs\">",
        esc(&super::js(&default))
    );
    for (key, label) in tabs {
        let key = esc(&super::js(key));
        out.push_str(&format!(
            "<button type=\"button\" @click=\"active = {key}\" :class=\"active === {key} ? 'border-accent-500 text-accent-700 dark:text-accent-400' : 'border-transparent text-ink-500 hover:text-ink-800 hover:border-ink-300 dark:text-ink-400 dark:hover:text-ink-100'\" class=\"inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition-colors\">{}</button>",
            esc(label)
        ));
    }
    out.push_str("</nav></div><div class=\"pt-6\">");
    out
}

pub fn tabs_close() -> &'static str {
    "</div></div>"
}

pub fn toast(tone: &str, title: &str, message: &str, on: &str) -> String {
    let (box_tone, icon_tone, icon) = match tone {
        "success" => (
            "bg-white border-accent-500 text-ink-900 dark:bg-ink-900 dark:text-ink-50",
            "text-accent-600 bg-accent-50 dark:bg-accent-900/40 dark:text-accent-300",
            "<path fill-rule=\"evenodd\" d=\"M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z\" clip-rule=\"evenodd\"/>",
        ),
        "warning" => (
            "bg-white border-warning-500 text-ink-900 dark:bg-ink-900 dark:text-ink-50",
            "text-warning-600 bg-warning-50 dark:bg-warning-900/40 dark:text-warning-300",
            "<path fill-rule=\"evenodd\" d=\"M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625l6.28-10.875zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z\" clip-rule=\"evenodd\"/>",
        ),
        "danger" => (
            "bg-white border-danger-500 text-ink-900 dark:bg-ink-900 dark:text-ink-50",
            "text-danger-600 bg-danger-50 dark:bg-danger-900/40 dark:text-danger-300",
            "<path fill-rule=\"evenodd\" d=\"M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z\" clip-rule=\"evenodd\"/>",
        ),
        _ => (
            "bg-white border-ink-400 text-ink-900 dark:bg-ink-900 dark:text-ink-50",
            "text-ink-500 bg-surface-100 dark:bg-ink-800 dark:text-ink-300",
            "<path fill-rule=\"evenodd\" d=\"M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z\" clip-rule=\"evenodd\"/>",
        ),
    };
    format!(
        "<div x-data=\"{{ shown: false }}\" x-on:{on}.window=\"shown = true; setTimeout(() => shown = false, 4500)\" x-show=\"shown\" x-transition:enter=\"transition ease-out duration-200\" x-transition:enter-start=\"opacity-0 translate-y-2\" x-transition:enter-end=\"opacity-100 translate-y-0\" x-transition:leave=\"transition ease-in duration-150\" x-transition:leave-start=\"opacity-100\" x-transition:leave-end=\"opacity-0\" x-cloak class=\"pointer-events-auto rounded-lg border-l-4 shadow-lg p-4 flex items-start gap-3 min-w-80 max-w-md {box_tone}\">\
         <span class=\"shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-full {icon_tone}\"><svg class=\"w-4 h-4\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\">{icon}</svg></span>\
         <div class=\"flex-1 text-sm\"><p class=\"font-semibold\">{}</p><p class=\"mt-0.5 text-ink-600 dark:text-ink-300\">{}</p></div>\
         <button type=\"button\" @click=\"shown = false\" class=\"shrink-0 text-ink-400 hover:text-ink-700 dark:hover:text-ink-100\" aria-label=\"Dismiss\"><svg class=\"w-4 h-4\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\"><path fill-rule=\"evenodd\" d=\"M4.28 3.22a.75.75 0 00-1.06 1.06L8.94 10l-5.72 5.72a.75.75 0 101.06 1.06L10 11.06l5.72 5.72a.75.75 0 101.06-1.06L11.06 10l5.72-5.72a.75.75 0 00-1.06-1.06L10 8.94 4.28 3.22z\" clip-rule=\"evenodd\"/></svg></button></div>",
        esc(title),
        esc(message),
        on = esc(on)
    )
}

pub fn skeleton(shape: &str, width: Option<&str>) -> String {
    let shape = match shape {
        "heading" => "h-5 w-2/3 rounded",
        "avatar" => "h-10 w-10 rounded-full",
        "thumbnail" => "h-24 w-full rounded-md",
        "button" => "h-10 w-24 rounded-md",
        _ => "h-3 w-full rounded",
    };
    format!(
        "<div class=\"animate-pulse bg-surface-200 dark:bg-ink-800 {shape}\"{}></div>",
        width
            .map(|w| format!(" style=\"width: {};\"", esc(w)))
            .unwrap_or_default()
    )
}

pub fn section_header(eyebrow: &str, title: &str, description: &str) -> String {
    format!(
        "<header class=\"max-w-2xl\"><p class=\"text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400\">{}</p><h2 class=\"mt-1 text-2xl font-bold tracking-tight text-ink-900 dark:text-ink-50\">{}</h2><p class=\"mt-2 text-ink-600 dark:text-ink-300\">{}</p></header>",
        esc(eyebrow),
        esc(title),
        esc(description)
    )
}

// ---- pagination --------------------------------------------------------------

/// Static pagination control (brand system).
pub fn pagination(current: i64, total: i64) -> String {
    let mut out = format!(
        "<nav class=\"flex items-center justify-between gap-4\" aria-label=\"Pagination\"><p class=\"text-sm text-ink-500 dark:text-ink-400\">Page <span class=\"font-medium text-ink-800 dark:text-ink-100\">{current}</span> of <span class=\"font-medium text-ink-800 dark:text-ink-100\">{total}</span></p><div class=\"inline-flex items-center gap-1\">\
         <button type=\"button\"{} class=\"inline-flex h-8 w-8 items-center justify-center rounded-md border border-ink-200 bg-white text-ink-600 hover:bg-surface-100 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-ink-900 dark:border-ink-700 dark:text-ink-300 dark:hover:bg-ink-800\"><svg class=\"w-4 h-4\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\"><path fill-rule=\"evenodd\" d=\"M12.79 5.23a.75.75 0 010 1.06L9.06 10l3.73 3.71a.75.75 0 11-1.06 1.06l-4.25-4.24a.75.75 0 010-1.06l4.25-4.24a.75.75 0 011.06 0z\" clip-rule=\"evenodd\"/></svg></button>",
        if current <= 1 { " disabled" } else { "" }
    );
    for page in (current - 2).max(1)..=(current + 2).min(total) {
        out.push_str(&format!(
            "<button type=\"button\" class=\"inline-flex h-8 min-w-8 px-2 items-center justify-center rounded-md text-sm font-medium transition-colors {}\">{page}</button>",
            if page == current { "bg-accent-600 text-white" } else { "text-ink-700 hover:bg-surface-100 dark:text-ink-200 dark:hover:bg-ink-800" }
        ));
    }
    out.push_str(&format!(
        "<button type=\"button\"{} class=\"inline-flex h-8 w-8 items-center justify-center rounded-md border border-ink-200 bg-white text-ink-600 hover:bg-surface-100 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-ink-900 dark:border-ink-700 dark:text-ink-300 dark:hover:bg-ink-800\"><svg class=\"w-4 h-4\" viewBox=\"0 0 20 20\" fill=\"currentColor\" aria-hidden=\"true\"><path fill-rule=\"evenodd\" d=\"M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.24a.75.75 0 010 1.06l-4.25 4.24a.75.75 0 01-1.06 0z\" clip-rule=\"evenodd\"/></svg></button></div></nav>",
        if current >= total { " disabled" } else { "" }
    ));
    out
}

/// Paginator links for dashboard tables (`$items->withQueryString()->links()`).
pub fn links<T: serde::Serialize>(page: &Page<T>) -> String {
    let summary = if page.total > 0 {
        format!(
            "showing <span class=\"text-ink-900\">{}</span> to <span class=\"text-ink-900\">{}</span> of <span class=\"text-ink-900\">{}</span> results",
            page.first_item.unwrap_or(0),
            page.last_item.unwrap_or(0),
            page.total
        )
    } else {
        "no results".into()
    };
    let mut out = format!(
        "<nav role=\"navigation\" aria-label=\"Pagination Navigation\" class=\"flex items-center justify-between gap-4\"><p class=\"font-mono text-xs text-ink-400\">{summary}</p><div class=\"inline-flex items-center gap-1 font-mono text-xs\">"
    );
    match &page.prev_url {
        Some(url) => out.push_str(&format!("<a href=\"{}\" rel=\"prev\" class=\"inline-flex h-8 px-2.5 items-center rounded-sm border border-ink-200 bg-white text-ink-700 hover:bg-surface-50\">« previous</a>", esc(url))),
        None => out.push_str("<span class=\"inline-flex h-8 px-2.5 items-center rounded-sm border border-ink-100 text-ink-300 cursor-default\" aria-disabled=\"true\">« previous</span>"),
    }
    for link in &page.links {
        match &link.url {
            None => out.push_str("<span class=\"inline-flex h-8 min-w-8 px-2 items-center justify-center text-ink-400\">…</span>"),
            Some(_) if link.active => out.push_str(&format!("<span aria-current=\"page\" class=\"inline-flex h-8 min-w-8 px-2 items-center justify-center rounded-sm bg-accent-600 text-white\">{}</span>", esc(&link.label))),
            Some(url) => out.push_str(&format!("<a href=\"{}\" class=\"inline-flex h-8 min-w-8 px-2 items-center justify-center rounded-sm text-ink-700 hover:bg-surface-50\">{}</a>", esc(url), esc(&link.label))),
        }
    }
    match &page.next_url {
        Some(url) => out.push_str(&format!("<a href=\"{}\" rel=\"next\" class=\"inline-flex h-8 px-2.5 items-center rounded-sm border border-ink-200 bg-white text-ink-700 hover:bg-surface-50\">next »</a>", esc(url))),
        None => out.push_str("<span class=\"inline-flex h-8 px-2.5 items-center rounded-sm border border-ink-100 text-ink-300 cursor-default\" aria-disabled=\"true\">next »</span>"),
    }
    out.push_str("</div></nav>");
    out
}

/// Table footer with pager links, only when there is more than one page.
pub fn links_footer<T: serde::Serialize>(page: &Page<T>) -> Option<String> {
    page.has_pages.then(|| links(page))
}

/// The `edit · view · delete` row actions share one delete form.
pub fn delete_form(action: &str, confirm: &str, csrf_field: &str) -> String {
    format!(
        "<form method=\"POST\" action=\"{}\" onsubmit=\"return confirm('{}')\" class=\"inline\">{csrf_field} {}<button type=\"submit\" class=\"text-danger-500 hover:text-danger-700 font-mono\">delete</button></form>",
        esc(action),
        esc(confirm),
        super::method_field("DELETE")
    )
}
