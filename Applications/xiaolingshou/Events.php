<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        // 向当前client_id发送数据 
        //Gateway::sendToClient($client_id, "Hello $client_id\n");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\n");

        $_SESSION['auth_timer_id'] = Timer::add(60, function($client_id){
            echo json_encode($_SESSION);
            Gateway::closeClient($client_id);
        }, array($client_id), false);

        echo '['.date("Y-m-d H:i:s").' onConnect] new client online:'.$client_id;


        RotateLogger::getLogger('onConnect')->info('new client online:'.$client_id);

        Gateway::sendToClient($client_id, json_encode(array(
            'type'      => 'init',
            'client_id' => $client_id
        )));
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message) {
        // 向所有人发送 
        //Gateway::sendToAll("$client_id said $message");
       $msg = json_decode($message, true);
       echo $msg['type'];
       switch($msg['type'])
       {
           case 'login':
               // 认证成功，删除 30关闭连接定 的时器
               echo '['.date("Y-m-d H:i:s").' login] client login, client_id:'.$client_id.', customer_id'.$msg['customer_id'];

               RotateLogger::getLogger('onMessage')->info('client login, client_id:'.$client_id.', customer_id'.$msg['customer_id']);
               Timer::del($_SESSION['auth_timer_id']);
               Gateway::bindUid($client_id, $msg['customer_id']);
               Gateway::joinGroup($client_id, 1);
               Gateway::sendToUid($msg['customer_id'], json_encode(array(
                   'type'      => 'bind',
                   'message' => '绑定成功'
               )));
               break;
       }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送 
       GateWay::sendToAll("$client_id logout");
   }
}
