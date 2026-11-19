<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateShopsAndTagsTables extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('CREATE EXTENSION IF NOT EXISTS pgcrypto');

        // ── shops ──
        $this
            ->table('shops', ['id' => false])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('banner', 'string', ['limit' => 2048, 'null' => true])
            ->addColumn('owner', 'uuid', ['null' => false, 'reference' => ['table' => 'users', 'column' => 'id']])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->create();

        $this->execute('ALTER TABLE shops ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->execute('ALTER TABLE shops ADD PRIMARY KEY (id)');
        $this->execute('CREATE UNIQUE INDEX shops_owner_idx ON shops (owner) WHERE deleted_at IS NULL');

        // ── tags ──
        $this
            ->table('tags', ['id' => false])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('shop_id', 'uuid', ['null' => false, 'reference' => ['table' => 'shops', 'column' => 'id']])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->create();

        $this->execute('ALTER TABLE tags ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->execute('ALTER TABLE tags ADD PRIMARY KEY (id)');
        $this->execute('CREATE UNIQUE INDEX tags_shop_name_idx ON tags (shop_id, name) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        $this->execute('DROP INDEX IF EXISTS tags_shop_name_idx');
        $this->execute('DROP TABLE IF EXISTS tags');

        $this->execute('DROP INDEX IF EXISTS shops_owner_idx');
        $this->execute('DROP TABLE IF EXISTS shops');
    }
}
