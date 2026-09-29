//! A tiny validator producing Laravel-style messages.

use super::form::FormData;

#[derive(Debug, Default)]
pub struct Validator {
    pub errors: Vec<String>,
}

fn label(field: &str) -> String {
    field.replace('_', " ")
}

impl Validator {
    pub fn new() -> Self {
        Self::default()
    }

    pub fn required(&mut self, form: &FormData, field: &str) -> &mut Self {
        if !crate::support::text::filled(form.get(field)) {
            self.errors
                .push(format!("The {} field is required.", label(field)));
        }
        self
    }

    pub fn max(&mut self, form: &FormData, field: &str, max: usize) -> &mut Self {
        if form.str(field).chars().count() > max {
            self.errors.push(format!(
                "The {} field must not be greater than {max} characters.",
                label(field)
            ));
        }
        self
    }

    pub fn min(&mut self, form: &FormData, field: &str, min: usize) -> &mut Self {
        let value = form.str(field);
        if !value.is_empty() && value.chars().count() < min {
            self.errors.push(format!(
                "The {} field must be at least {min} characters.",
                label(field)
            ));
        }
        self
    }

    pub fn email(&mut self, form: &FormData, field: &str) -> &mut Self {
        let value = form.str(field);
        if !value.is_empty() && !is_email(&value) {
            self.errors.push(format!(
                "The {} field must be a valid email address.",
                label(field)
            ));
        }
        self
    }

    pub fn one_of(&mut self, form: &FormData, field: &str, allowed: &[&str]) -> &mut Self {
        let value = form.str(field);
        if !allowed.contains(&value.as_str()) {
            self.errors
                .push(format!("The selected {} is invalid.", label(field)));
        }
        self
    }

    pub fn date(&mut self, form: &FormData, field: &str) -> &mut Self {
        if let Some(value) = form.opt(field)
            && crate::support::dates::parse(&value).is_none()
        {
            self.errors
                .push(format!("The {} field must be a valid date.", label(field)));
        }
        self
    }

    pub fn regex(&mut self, form: &FormData, field: &str, re: &regex::Regex) -> &mut Self {
        if let Some(value) = form.opt(field)
            && !re.is_match(&value)
        {
            self.errors
                .push(format!("The {} field format is invalid.", label(field)));
        }
        self
    }

    pub fn taken(&mut self, field: &str, taken: bool) -> &mut Self {
        if taken {
            self.errors
                .push(format!("The {} has already been taken.", label(field)));
        }
        self
    }

    pub fn fail(&mut self, message: impl Into<String>) -> &mut Self {
        self.errors.push(message.into());
        self
    }

    pub fn is_ok(&self) -> bool {
        self.errors.is_empty()
    }
}

pub fn is_email(value: &str) -> bool {
    let Some((local, domain)) = value.split_once('@') else {
        return false;
    };
    !local.is_empty()
        && !domain.is_empty()
        && domain.contains('.')
        && !domain.starts_with('.')
        && !domain.ends_with('.')
        && !value.chars().any(char::is_whitespace)
}
