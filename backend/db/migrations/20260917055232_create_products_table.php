<?php

declare(strict_types=1);

use Decimal\Decimal;
use Phinx\Migration\AbstractMigration;

final class CreateProductsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->execute('CREATE EXTENSION IF NOT EXISTS pgcrypto');

        $this
            ->table('products', [
                'id' => false,
            ])
            ->addColumn('id', 'uuid', [
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('price', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'null' => false,
            ])
            ->addColumn('quantity', 'integer', [
                'null' => false,
            ])
            ->addColumn('image_url', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('deleted_at', 'timestamp', [
                'null' => true,
            ])
            ->create();

        $this->execute('ALTER TABLE products
             ALTER COLUMN id SET DEFAULT gen_random_uuid()');

        $this->execute('ALTER TABLE products
             ADD PRIMARY KEY (id)');
    }
}
