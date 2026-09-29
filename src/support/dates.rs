//! Date formatting with PHP `date()` format characters, so the templates keep
//! the same format strings the Blade views used.

use chrono::{Datelike, NaiveDateTime, Timelike, Utc};

pub const DB_FORMAT: &str = "%Y-%m-%d %H:%M:%S";

pub fn now() -> NaiveDateTime {
    Utc::now()
        .naive_utc()
        .with_nanosecond(0)
        .unwrap_or_else(|| Utc::now().naive_utc())
}

/// Format for storage (`YYYY-MM-DD HH:MM:SS`, what Laravel wrote).
pub fn to_db(dt: NaiveDateTime) -> String {
    dt.format(DB_FORMAT).to_string()
}

/// Parse a stored or serialised datetime. Accepts `YYYY-MM-DD HH:MM:SS`,
/// ISO-8601 (`T` separator, optional fraction/offset) and `YYYY-MM-DDTHH:MM`.
pub fn parse(value: &str) -> Option<NaiveDateTime> {
    let value = value.trim();
    if let Ok(dt) = NaiveDateTime::parse_from_str(value, DB_FORMAT) {
        return Some(dt);
    }
    if let Ok(dt) = NaiveDateTime::parse_from_str(value, "%Y-%m-%dT%H:%M:%S") {
        return Some(dt);
    }
    if let Ok(dt) = NaiveDateTime::parse_from_str(value, "%Y-%m-%dT%H:%M") {
        return Some(dt);
    }
    if let Ok(dt) = NaiveDateTime::parse_from_str(value, "%Y-%m-%d %H:%M") {
        return Some(dt);
    }
    if let Ok(dt) = chrono::DateTime::parse_from_rfc3339(value) {
        return Some(dt.naive_utc());
    }
    if let Ok(dt) = NaiveDateTime::parse_from_str(value, "%Y-%m-%dT%H:%M:%S%.f") {
        return Some(dt);
    }
    if let Ok(dt) = NaiveDateTime::parse_from_str(value, "%Y-%m-%d %H:%M:%S%.f") {
        return Some(dt);
    }
    if let Ok(d) = chrono::NaiveDate::parse_from_str(value, "%Y-%m-%d") {
        return d.and_hms_opt(0, 0, 0);
    }
    None
}

/// `Carbon::toIso8601String()`.
pub fn iso8601(dt: NaiveDateTime) -> String {
    dt.format("%Y-%m-%dT%H:%M:%S+00:00").to_string()
}

/// `Carbon::toRssString()` (RFC 2822).
pub fn rss(dt: NaiveDateTime) -> String {
    dt.format("%a, %d %b %Y %H:%M:%S +0000").to_string()
}

const MONTHS: [&str; 12] = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
];
const DAYS: [&str; 7] = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
];

/// PHP-style `date()` formatting.
pub fn php_format(dt: NaiveDateTime, fmt: &str) -> String {
    let mut out = String::new();
    let mut chars = fmt.chars();
    while let Some(c) = chars.next() {
        match c {
            '\\' => {
                if let Some(next) = chars.next() {
                    out.push(next);
                }
            }
            'd' => out.push_str(&format!("{:02}", dt.day())),
            'j' => out.push_str(&dt.day().to_string()),
            'D' => out.push_str(&DAYS[dt.weekday().num_days_from_monday() as usize][..3]),
            'l' => out.push_str(DAYS[dt.weekday().num_days_from_monday() as usize]),
            'N' => out.push_str(&dt.weekday().number_from_monday().to_string()),
            'm' => out.push_str(&format!("{:02}", dt.month())),
            'n' => out.push_str(&dt.month().to_string()),
            'F' => out.push_str(MONTHS[dt.month0() as usize]),
            'M' => out.push_str(&MONTHS[dt.month0() as usize][..3]),
            'Y' => out.push_str(&dt.year().to_string()),
            'y' => out.push_str(&format!("{:02}", dt.year() % 100)),
            'H' => out.push_str(&format!("{:02}", dt.hour())),
            'G' => out.push_str(&dt.hour().to_string()),
            'h' => out.push_str(&format!("{:02}", dt.hour12().1)),
            'g' => out.push_str(&dt.hour12().1.to_string()),
            'i' => out.push_str(&format!("{:02}", dt.minute())),
            's' => out.push_str(&format!("{:02}", dt.second())),
            'a' => out.push_str(if dt.hour12().0 { "pm" } else { "am" }),
            'A' => out.push_str(if dt.hour12().0 { "PM" } else { "AM" }),
            'U' => out.push_str(&dt.and_utc().timestamp().to_string()),
            'c' => out.push_str(&iso8601(dt)),
            'r' => out.push_str(&rss(dt)),
            other => out.push(other),
        }
    }
    out
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn formats_like_php() {
        let dt = parse("2026-04-05 09:07:03").unwrap();
        assert_eq!(php_format(dt, "j F Y"), "5 April 2026");
        assert_eq!(php_format(dt, "d F Y"), "05 April 2026");
        assert_eq!(php_format(dt, "j M Y"), "5 Apr 2026");
        assert_eq!(php_format(dt, "Y-m-d\\TH:i"), "2026-04-05T09:07");
        assert_eq!(php_format(dt, "l, j F Y"), "Sunday, 5 April 2026");
        assert_eq!(rss(dt), "Sun, 05 Apr 2026 09:07:03 +0000");
        assert_eq!(iso8601(dt), "2026-04-05T09:07:03+00:00");
    }
}
