<?php
namespace Izumi\Backend\app\Shared;

abstract class Model {

    public readonly string $id;

    protected function __contruct(string $id = ''){
        $this->id = $id !== '' ? $id : uuid_create(UUID_TYPE_RANDOM);
    }

    /**
     * @return array<Error>|self
     */
    abstract public static function create(array $data): array|self;

    public function equals(Model $other): bool {
        if (get_class($this) !== get_class($other)) {
            return false;
        }
        return $this->id === $other->id;
    }
}



?>
