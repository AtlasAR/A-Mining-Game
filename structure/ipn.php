<?php
class ipn {
    private $db;
    private $postData;
    
    const TESTING = false;
    const TEST_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    const LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';
    const NPN_URL = 'https://api-3t.paypal.com/nvp';
    const PAYPAL_EMAIL = '';
    
    //PAYPAL API DETAILS
    const API_USERNAME = '';
    const API_PASSWORD = '';
    const API_SIGNATURE = '';
    
    function __construct(database $database) {
        $this->db = $database;
        
        if(TESTING)
            $this->retrieve();
    }
    
    /*
     * Verify a PayPal payment
     */
    
    public function verify(){
        if($_POST['test_ipn'] == 1 && !self::TESTING){
            $this->logError('null', 'null', 'Received test notification on live environment.');
        }else{
            //define the url we are communicating with
            $url = (self::TESTING) ? self::TEST_URL : self::LIVE_URL;
            
            $request = 'cmd=_notify-validate';
            foreach ($_POST as $key => $value) {
                $value = urlencode(stripslashes($value));
                $request .= "&$key=$value";
            }

            $txn_id = $_POST['txn_id'];
            $user_id = $_POST['custom'];
            $paid = $_POST['mc_gross'];

            //send unaltered data back to PayPal to
            //check validity of received data
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            $message = curl_exec($ch);
            curl_close($ch);

            if($message == 'VERIFIED') {
                if(!isset($_POST['reason_code']) && $paid > 0 ) {

                    //make sure it's USD
                    if($_POST['mc_currency'] != 'USD') {
                        $this->logError($txn_id, null, 'Incorrect currency.');
                        return false;
                    }

                    if($_POST['receiver_email'] != self::PAYPAL_EMAIL) {
                        $this->logError($txn_id, null, 'Incorrect receiver email.');
                        return false;
                    }

                    return true;
                }
            }else{
                $this->logError($txn_id, $user_id, 'PayPal returned "INVALID" for attempted verification.');
                return false;
            }
        }
    }
    
    /*
     * Gets a TXN_ID, and lookups the transaction details
     */
    
    public function lookupTransaction($txn_id){
        $parameters = '?VERSION=111&METHOD=TransactionSearch&USER='. self::API_USERNAME .'&PWD='. self::API_PASSWORD .'&SIGNATURE='. self::API_SIGNATURE .'&STARTDATE=2012-08-24T05:38:48Z&STATUS=Success&TRANSACTIONID='. $txn_id;
        
        //send unaltered data back to PayPal to
        //check validity of received data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::NPN_URL.$parameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        
        parse_str(urldecode($data), $data);
        return $data;
    }
    
    //kind of a useless function
    private function retrieve(){
        if(isset($_POST)){
            $this->postData = $_POST;

            //if it's an IPN test, log the sent information
            if($_POST['test_ipn'] == 1)
                $this->logPostData();
        }else{
            throw new Exception("No data retrieved.");
        }
    }
    
    private function logPostData(){
        $fh = fopen('log.txt', 'a+');

        fwrite($fh, PHP_EOL.PHP_EOL."\t \t ====================== \t ".date('M-d-Y hh:mm:ss')." \t ======================".PHP_EOL);

        foreach ($_POST as $key => $data){
            fwrite($fh, "\t \t $key \t = $data".PHP_EOL);
        }

        fclose($fh);
    }
    
    private function logError($txn_id, $user_id, $error){
        $user_id = (!ctype_digit($user_id)) ? $_SERVER['REMOTE_ADDR'] : $user_id;
        
        $fh = fopen('ipn_error.txt', 'a+');
        fwrite($fh, date('M d, Y g:i:s A')." ~~~ [$txn_id : $user_id] $error ".PHP_EOL);
        fclose($fh);
    }
}

?>