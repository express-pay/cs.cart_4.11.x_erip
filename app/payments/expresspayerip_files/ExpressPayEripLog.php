<?php 
class ExpressPayEripLog{
    public function log_error_exception($name, $message, $e) {
        $this->log($name, "ERROR" , $message . '; EXCEPTION MESSAGE - ' . $e->getMessage() . '; EXCEPTION TRACE - ' . $e->getTraceAsString());
    }

    public function log_error($name, $message) {
        $this->log($name, "ERROR" , $message);
    }

    public function log_info($name, $message) {
        $this->log($name, "INFO" , $message);
    }

    public function log($name, $type, $message) {
        $log_url = dirname(__FILE__) . '/Log';

        if(!file_exists($log_url)) {
            $is_created = mkdir($log_url, 0777);

            if(!$is_created)
                return;
        }

        $log_url .= '/express-pay-erip' . date('Y.m.d') . '.log';

        file_put_contents($log_url, $type ." -Time:".date('h:i:s'). " - IP - " . $_SERVER['REMOTE_ADDR'] . ";  FUNCTION - " . $name . "; MESSAGE - " . $message . ';' . PHP_EOL, FILE_APPEND);
    
    }


}