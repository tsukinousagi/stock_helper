<?php
/**
 * 理財金融相關話題
 * 
 */
namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\FinanceTopics;

class FinanceTopicsRepository {
    
    public function getRandomTopics() {
        $topic = FinanceTopics::select('topic', 'content')
            ->where('enabled', '=', 1)
            ->inRandomOrder()
            ->limit(1)
            ->first();
        return $topic;
    }

}