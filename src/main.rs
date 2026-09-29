use anyhow::Result;
use clap::{Parser, Subcommand};
use sysadmin_journal::{app, commands, config, db, router};

#[derive(Parser)]
#[command(
    name = "sysadmin-journal",
    version,
    about = "SysAdmin Journal — blog and backoffice"
)]
struct Cli {
    #[command(subcommand)]
    command: Option<Command>,
}

#[derive(Subcommand)]
enum Command {
    /// Run the HTTP server (the default).
    Serve,
    /// Apply pending database migrations.
    Migrate,
    /// Import posts, pages, tags and users from a Ghost JSON export.
    GhostImport {
        /// Path to the export file (defaults to ghost-nice/sysadmin-journal.ghost.*.json).
        path: Option<String>,
    },
    /// Turn titled images in saved post HTML into figures with captions.
    PostsWrapFigures {
        /// List affected posts without saving.
        #[arg(long)]
        dry_run: bool,
    },
    /// Create a user account so you can sign in to the dashboard.
    UserCreate {
        #[arg(long)]
        name: String,
        #[arg(long)]
        email: String,
        #[arg(long)]
        password: String,
        /// Author URL slug (defaults to a slug of the name).
        #[arg(long)]
        slug: Option<String>,
    },
}

#[tokio::main]
async fn main() -> Result<()> {
    dotenvy::dotenv().ok();
    tracing_subscriber::fmt()
        .with_env_filter(
            tracing_subscriber::EnvFilter::try_from_default_env()
                .unwrap_or_else(|_| "info,tower_http=info".into()),
        )
        .init();

    let cli = Cli::parse();
    let config = config::Config::from_env()?;
    let db = db::connect(&config.database_url).await?;

    match cli.command.unwrap_or(Command::Serve) {
        Command::Migrate => {
            db::migrate(&db).await?;
            println!("Migrations applied.");
        }
        Command::GhostImport { path } => {
            db::migrate(&db).await?;
            commands::ghost_import(&db, path, config.bcrypt_rounds).await?;
        }
        Command::PostsWrapFigures { dry_run } => {
            commands::wrap_figures(&db, dry_run).await?;
        }
        Command::UserCreate {
            name,
            email,
            password,
            slug,
        } => {
            db::migrate(&db).await?;
            commands::user_create(&db, name, email, password, slug, config.bcrypt_rounds).await?;
        }
        Command::Serve => {
            db::migrate(&db).await?;
            let addr = format!("{}:{}", config.host, config.port);
            let state = app::AppState::new(db, config);
            let app = router::build(state).await?;
            let listener = tokio::net::TcpListener::bind(&addr).await?;
            tracing::info!("listening on http://{addr}");
            axum::serve(listener, app).await?;
        }
    }
    Ok(())
}
