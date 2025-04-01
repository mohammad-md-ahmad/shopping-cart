<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @property string $id
 * @property string $type
 * @property int $value
 * @property int $min_quantity
 * @property string $applicable_model_type
 * @property string $applicable_model_id
 * @property string $valid_from
 * @property string $valid_until
 */
class Discount extends BaseModel {}
