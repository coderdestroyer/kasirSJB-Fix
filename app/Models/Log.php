<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    // Specify the table name
    protected $table = 'log_activity';  // Change this line

    // Optionally, if your table doesn't have the default 'id' as primary key
    // protected $primaryKey = 'your_primary_key_column';

    // Define fillable columns (if needed)
    protected $fillable = ['log_time', 'name', 'log_target', 'log_description', 'activity_type', 'old_value', 'new_value'];
}
