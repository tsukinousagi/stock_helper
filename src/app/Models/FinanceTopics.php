<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceTopics extends Model
{
    //
    protected $table = 'finance_topics';
    protected $fillable = ['topic', 'content', 'enabled'];
}
