<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAuthTables extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('CREATE EXTENSION IF NOT EXISTS pgcrypto');

        // ── users ──
        $this
            ->table('users', ['id' => false])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('nickname', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->create();

        $this->execute('ALTER TABLE users ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->execute('ALTER TABLE users ADD PRIMARY KEY (id)');
        $this->execute('CREATE UNIQUE INDEX users_nickname_idx ON users (nickname) WHERE deleted_at IS NULL');

        // ── user_identities ──
        $this
            ->table('user_identities', ['id' => false])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('user_id', 'uuid', ['null' => false, 'reference' => ['table' => 'users', 'column' => 'id']])
            ->addColumn('provider', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('provider_subject', 'string', ['limit' => 512, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->create();

        $this->execute('ALTER TABLE user_identities ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->execute('ALTER TABLE user_identities ADD PRIMARY KEY (id)');

        // Check constraint: password requires password_hash, oidc requires provider_subject
        $this->execute("ALTER TABLE user_identities ADD CONSTRAINT user_identities_provider_check CHECK (
                (provider = 'password' AND password_hash IS NOT NULL) OR
                (provider = 'openid_connect' AND provider_subject IS NOT NULL)
            )");

        // Partial unique index: (provider, provider_subject) for active rows
        $this->execute(
            'CREATE UNIQUE INDEX user_identities_provider_subject_idx ON user_identities (provider, provider_subject) WHERE deleted_at IS NULL',
        );

        // Partial unique index: email for password identities
        $this->execute(
            "CREATE UNIQUE INDEX user_identities_email_idx ON user_identities (email) WHERE provider = 'password' AND deleted_at IS NULL",
        );

        // Index on user_id for lookups
        $this->execute(
            'CREATE INDEX user_identities_user_id_idx ON user_identities (user_id) WHERE deleted_at IS NULL',
        );

        // ── sessions ──
        $this
            ->table('sessions', ['id' => false])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('user_id', 'uuid', ['null' => false, 'reference' => ['table' => 'users', 'column' => 'id']])
            ->addColumn('expiration', 'timestamp', ['null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->create();

        $this->execute('ALTER TABLE sessions ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->execute('ALTER TABLE sessions ADD PRIMARY KEY (id)');

        // Index on expiration for expiry checks and cleanup
        $this->execute('CREATE INDEX sessions_expiration_idx ON sessions (expiration)');

        // Index on user_id for session lookups
        $this->execute('CREATE INDEX sessions_user_id_idx ON sessions (user_id) WHERE deleted_at IS NULL');

        // ── temp_users ──
        $this
            ->table('temp_users', ['id' => false])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('expiration', 'timestamp', ['null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->create();

        $this->execute('ALTER TABLE temp_users ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->execute('ALTER TABLE temp_users ADD PRIMARY KEY (id)');

        // Partial unique index on email
        $this->execute('CREATE UNIQUE INDEX temp_users_email_idx ON temp_users (email) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        $this->execute('DROP INDEX IF EXISTS temp_users_email_idx');
        $this->execute('DROP TABLE IF EXISTS temp_users');

        $this->execute('DROP INDEX IF EXISTS sessions_user_id_idx');
        $this->execute('DROP INDEX IF EXISTS sessions_expiration_idx');
        $this->execute('DROP TABLE IF EXISTS sessions');

        $this->execute('DROP INDEX IF EXISTS user_identities_user_id_idx');
        $this->execute('DROP INDEX IF EXISTS user_identities_email_idx');
        $this->execute('DROP INDEX IF EXISTS user_identities_provider_subject_idx');
        $this->execute('DROP TABLE IF EXISTS user_identities');

        $this->execute('DROP INDEX IF EXISTS users_nickname_idx');
        $this->execute('DROP TABLE IF EXISTS users');
    }
}
