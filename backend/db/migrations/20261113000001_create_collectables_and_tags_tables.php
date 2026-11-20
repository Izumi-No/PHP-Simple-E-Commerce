<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCollectablesAndTagsTables extends AbstractMigration
{
    public function up(): void
    {
        // ── collectables ──
        $this
            ->table('collectables', ['id' => false])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => false])
            ->addColumn('images', 'text', ['null' => false, 'default' => '[]'])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('shop_id', 'uuid', ['null' => false, 'reference' => ['table' => 'shops', 'column' => 'id']])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->create();

        $this->execute('ALTER TABLE collectables ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->execute('ALTER TABLE collectables ADD PRIMARY KEY (id)');
        $this->execute('ALTER TABLE collectables ADD CONSTRAINT collectables_price_check CHECK (price >= 0)');

        // ── collectable_tags ──
        $this
            ->table('collectable_tags', ['id' => false])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('collectable_id', 'uuid', [
                'null' => false,
                'reference' => ['table' => 'collectables', 'column' => 'id'],
            ])
            ->addColumn('tag_id', 'uuid', ['null' => false, 'reference' => ['table' => 'tags', 'column' => 'id']])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->create();

        $this->execute('ALTER TABLE collectable_tags ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        $this->execute('ALTER TABLE collectable_tags ADD PRIMARY KEY (id)');
        $this->execute(
            'CREATE UNIQUE INDEX collectable_tags_pair_idx ON collectable_tags (collectable_id, tag_id) WHERE deleted_at IS NULL',
        );

        // ── drop legacy products table ──
        $this->execute('DROP TABLE IF EXISTS products');
    }

    public function down(): void
    {
        $this->execute('DROP INDEX IF EXISTS collectable_tags_pair_idx');
        $this->execute('DROP TABLE IF EXISTS collectable_tags');

        $this->execute('DROP TABLE IF EXISTS collectables');
    }
}
