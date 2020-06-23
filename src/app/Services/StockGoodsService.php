<?php
/**
 * 商品資料相關
 * 目前只處理上市櫃公司股票和ETF
 */
namespace App\Services;

use App\Services\RemoteUrlService;
use App\Services\MarketDaysService;
use App\Repositories\GoodsRepository;
use Illuminate\Support\Facades\Redis;
use PHPHtmlParser\Dom;
use Log;

class StockGoodsService {
    
    private $obj_redis;

    public function __construct() {
        $this->obj_redis = Redis::connection();
    }
    
    /**
     * 更新個股清單
     */
    public function updateStockGoods() {
        // 檢查是不是交易日
        $obj_day = new MarketDaysService();
        if ($obj_day->isTodayMarketOpen() == false) {
            echo('今天不是交易日' . PHP_EOL);
            return false;
        }
        // 資料來源網址
//        $url_twse = 'http://cloud.usagi.tw/~usagi/usagilab/index.php/device_uptime/status/usagi/';
        $url_twse = 'https://isin.twse.com.tw/isin/C_public.jsp?strMode=2';
        $url_tpex = 'https://isin.twse.com.tw/isin/C_public.jsp?strMode=4';
        $url_etf = 'https://www.twse.com.tw/zh/page/ETF/list.html';
        
        // 依序取得個股清單（上市，上櫃，ETF）
        
        foreach(['twse', 'tpex', 'etf'] as $type) {
//        foreach(['etf'] as $type) {
            // 定義商品類別
            $type_text = [
                'twse' => '上市',
                'tpex' => '上櫃',
                'etf' => 'ETF',
            ];
        
            // 取得網頁
            if ($type == 'twse') {
                $ret = $this->getDataFromRemote($url_twse, 'twse');
                $ret = iconv('big5', 'utf-8//ignore', $ret);
            } else if ($type == 'tpex') {
                $ret = $this->getDataFromRemote($url_tpex, 'tpex');
                $ret = iconv('big5', 'utf-8//ignore', $ret);
            } else if ($type == 'etf') {
                $ret = $this->getDataFromRemote($url_etf, 'etf');
            }
            
            // 去掉用不到的資料
            if ($type == 'twse') {
                $ret = substr($ret, 0, strpos($ret, "上市認購(售)權證"));
            } else if ($type == 'tpex') {
                $ret = substr($ret, strpos($ret, "股票"));
                $ret = substr($ret, 0, strpos($ret, "特別股"));
            }

            // 去掉一些有問題的tag
            if (in_array($type, ['twse', 'tpex'])) {
                $ret = substr($ret, strpos($ret, "<TABLE class='h4' align=center"));
                $ret = preg_replace('/\ bgcolor=#[0-9A-F]{6}/', '', $ret);
            }
            
            /*
            // 寫入暫存檔
            $tmp_path = tempnam(sys_get_temp_dir(), 'stockdata');
            file_put_contents($tmp_path, $ret);
            */
            
            // 解析資料並寫入DB
            $dom = new Dom;
            $dom->load($ret);
    //        var_dump($dom);
    //        $dom->load("<TABLE class='h4' align=center cellSpacing=3 cellPadding=2 width=750 border=0><tr align=center><td bgcolor=#D5FFD5>有價證券代號及名稱 </td><td bgcolor=#D5FFD5>國際證券辨識號碼(ISIN Code)</td><td bgcolor=#D5FFD5>上市日</td><td bgcolor=#D5FFD5>市場別</td><td bgcolor=#D5FFD5>產業別</td><td bgcolor=#D5FFD5>CFICode</td><td bgcolor=#D5FFD5>備註</td></tr><tr><td bgcolor=#FAFAD2 colspan=7 ><B> 股票 <B> </td></tr><tr><td bgcolor=#FAFAD2>1101　台泥</td><td bgcolor=#FAFAD2>TW0001101004</td><td bgcolor=#FAFAD2>1962/02/09</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>水泥工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1102　亞泥</td><td bgcolor=#FAFAD2>TW0001102002</td><td bgcolor=#FAFAD2>1962/06/08</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>水泥工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1103　嘉泥</td><td bgcolor=#FAFAD2>TW0001103000</td><td bgcolor=#FAFAD2>1969/11/14</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>水泥工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1104　環泥</td><td bgcolor=#FAFAD2>TW0001104008</td><td bgcolor=#FAFAD2>1971/02/01</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>水泥工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1108　幸福</td><td bgcolor=#FAFAD2>TW0001108009</td><td bgcolor=#FAFAD2>1990/06/06</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>水泥工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1109　信大</td><td bgcolor=#FAFAD2>TW0001109007</td><td bgcolor=#FAFAD2>1991/12/05</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>水泥工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1110　東泥</td><td bgcolor=#FAFAD2>TW0001110005</td><td bgcolor=#FAFAD2>1994/10/22</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>水泥工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1201　味全</td><td bgcolor=#FAFAD2>TW0001201002</td><td bgcolor=#FAFAD2>1962/02/09</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1203　味王</td><td bgcolor=#FAFAD2>TW0001203008</td><td bgcolor=#FAFAD2>1964/08/24</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1210　大成</td><td bgcolor=#FAFAD2>TW0001210003</td><td bgcolor=#FAFAD2>1978/05/20</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1213　大飲</td><td bgcolor=#FAFAD2>TW0001213007</td><td bgcolor=#FAFAD2>1981/04/10</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1215　卜蜂</td><td bgcolor=#FAFAD2>TW0001215002</td><td bgcolor=#FAFAD2>1987/07/27</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1216　統一</td><td bgcolor=#FAFAD2>TW0001216000</td><td bgcolor=#FAFAD2>1987/12/28</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1217　愛之味</td><td bgcolor=#FAFAD2>TW0001217008</td><td bgcolor=#FAFAD2>1989/10/28</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1218　泰山</td><td bgcolor=#FAFAD2>TW0001218006</td><td bgcolor=#FAFAD2>1989/11/11</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1219　福壽</td><td bgcolor=#FAFAD2>TW0001219004</td><td bgcolor=#FAFAD2>1990/12/01</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr><tr><td bgcolor=#FAFAD2>1220　台榮</td><td bgcolor=#FAFAD2>TW0001220002</td><td bgcolor=#FAFAD2>1991/11/20</td><td bgcolor=#FAFAD2>上市</td><td bgcolor=#FAFAD2>食品工業</td><td bgcolor=#FAFAD2>ESVUFR</td><td bgcolor=#FAFAD2></td></tr>");
    //        $dom->loadFromFile($tmp_path);
            if (in_array($type, ['twse', 'tpex'])) {
                $rows = $dom->find('tr');
                foreach($rows as $row) {
                    $cols = $row->find('td');
                    if (isset($cols[3])) {
    //                    if ($cols[3]->text == '上市') {
                        if (in_array($cols[3]->text, ['上市', '上櫃'])) {
        //                    echo($cols[0]->text . PHP_EOL);
                            $goods_data = explode(' ', $cols[0]->text);
                            $ret = $this->goodsDataToDB($goods_data[0], $goods_data[1], $type_text[$type]);
                        }
                    }
                }
            } else if ($type == 'etf') {
                $table = $dom->getElementsByClass('grid');
                $rows = $table->find('tbody tr');
                foreach($rows as $row) {
                    $cols = $row->find('td');
                    $ret = $this->goodsDataToDB($cols[1]->text, $cols[2]->text, $type_text[$type]);
                }
            }
        }
    }
    
    /**
     * 把商品資料塞入DB
     * @param string $code
     * @param string $name
     * @param string $type
     */
    public function goodsDataToDB(string $code, string $name, string $type) {
        $obj_goods = new GoodsRepository();
        return $obj_goods->updateGoods(trim($code), trim($name), trim($type));
    }
    
    /**
     * 傳入商品代碼，傳回是上市、上櫃或ETF
     * @param string $goods
     * @return string
     */
    public function getGoodsType(string $code) {
        try {
            $goods_type = $this->obj_redis->get('type_' . trim($code));
            if (!$goods_type) {
                $obj_goods = new GoodsRepository();
                $goods_type = $obj_goods->getGoodsType(trim($code));
                $this->obj_redis->set('type_' . trim($code), $goods_type);
            }
            return $goods_type;
        } catch (Exception $e) {
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 傳入商品代碼，傳回名稱
     * @param string $goods
     * @return string
     */
    public function getGoodsName(string $code) {
        try {
            $goods_name = $this->obj_redis->get('name_' . trim($code));
            if (!$goods_name) {
                $obj_goods = new GoodsRepository();
                $goods_name = $obj_goods->getGoodsName(trim($code));
                $this->obj_redis->set('name_' . trim($code), $goods_name);
            }
            return $goods_name;
        } catch (Exception $e) {
            Log::error($e->getLine() . ' ' . __CLASS__ . ':' . __FUNCTION__ . ' ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 從外部網址取得資料
     * @param string $url
     * @param string $source
     */
    public function getDataFromRemote(string $url, string $source) {
        $obj_remote = new RemoteUrlService();
        $page = $obj_remote->getUrl($url, 21600);
        
        return $page;
        
    }
    
    public function getAllValidGoods() {
        $obj_goods = new GoodsRepository();
        $result = $obj_goods->getAllValidGoods();
        $codes = [];
        foreach ($result as $v) {
            $codes[] = $v->code;
        }
        return $codes;
    }

}