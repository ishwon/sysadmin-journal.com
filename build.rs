fn main() {
    // Templates are embedded with `include_dir!`; rebuild when they change.
    println!("cargo:rerun-if-changed=templates");
    println!("cargo:rerun-if-changed=migrations");
}
