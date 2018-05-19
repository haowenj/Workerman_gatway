<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 2018/1/10
 * Time: 上午11:30
 */

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

class RotateLogger
{
    public static function getLogger($method)
    {
        date_default_timezone_set('Asia/Shanghai');

        $dotenv = new Dotenv\Dotenv(__DIR__.'/../../');
        $dotenv->load();

        $logger  = new Logger('Events:'.$method);
        $logger->pushHandler(new RotatingFileHandler(getenv('LOG_PATH').'/app.log'));

        return $logger;
    }
}