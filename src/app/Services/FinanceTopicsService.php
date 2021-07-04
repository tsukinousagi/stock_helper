<?php
/**
 * 理財金融相關話題
 * 
 */
namespace App\Services;

use App\Repositories\FinanceTopicsRepository;

class FinanceTopicsService {
    
    public function getRandomTopics() {
        $obj_repo = new FinanceTopicsRepository();
        return $obj_repo->getRandomTopics();
    }
    

}
