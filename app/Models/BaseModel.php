<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Facades\DB;
use stdClass;

class BaseModel
{
    protected string $table;

    protected bool $created_at = true;

    protected bool $updated_at = true;

    protected stdClass $attributes;

    final public function __construct(array $data = [])
    {
        $this->table = $this->table ?? $this->generateTableName();

        /**
         * Load all fields from the database table and set them to attributes,
         * map the each attribute with its value if available in $data argument
         */
        $this->loadFieldsFromDatabaseAndMapData($data);
    }

    public function __get(string $name)
    {
        return $this->attributes->{$name} ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->attributes->{$name} = $value;
    }

    public static function create(array $data = []): self
    {
        $model = new static($data);

        $currentTimestamp = date('Y-m-d H:i:s');

        if ($model->created_at) {
            $data['created_at'] = $currentTimestamp;
        }

        if ($model->updated_at) {
            $data['updated_at'] = $currentTimestamp;
        }

        $recordId = DB::table($model->table)->insertGetId($data);
        $recordData = (array) DB::table($model->table)->where('id', $recordId)->firstOrFail();

        $model->fillAttributes($recordData);

        return $model;
    }

    public function toArray(): array
    {
        return (array) $this->attributes;
    }

    protected function fillAttributes(array $data)
    {
        $this->attributes = (object) $data;
    }

    /**
     * Generate table (in terms of snake case style) from the class name which is written in Pascal case
     * add 's' at the end
     *
     * in case the table name can't be retrieved from the above pattern, it can be assigned manually to the $table attribute
     */
    protected function generateTableName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();

        $snakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));

        return $snakeCase.'s';
    }

    /**
     * Load all fields (columns) from the database table into the attributes property.
     */
    protected function loadFieldsFromDatabaseAndMapData(array $data): void
    {
        $this->attributes = new stdClass;

        // Get the columns from the database table
        $columns = DB::getSchemaBuilder()->getColumnListing($this->table);

        foreach ($columns as $column) {
            // Initialize the attribute for each column with the associated value or null
            $this->attributes->{$column} = $data[$column] ?? null;
        }
    }
}
