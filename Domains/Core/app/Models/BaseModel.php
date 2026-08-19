<?php

namespace Domains\Core\Models;

use Domains\Core\Traits\HasEditor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasEditor, HasFactory;
}
