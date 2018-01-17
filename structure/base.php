<?php

class base
{
    private $database;
    
    /*
     * @METHOD  construct
     * @DESC    runs important functions/methods upon initiating
     */
    
    function __construct(database $database = null) {
        if(!is_null($database)) $this->database = $database;
    }
    
    /*
     * @METHOD  redirect
     * @DESC    instead of writing the header function so many times,
     * @DESC    we'll just use this redirect function
     */
    
    public function redirect($url) {
        header('Location: '. $url);
        exit();
    }
    
    /*
     * @METHOD  country
     * @DESC    get the visitors country
     */
    
    public function country(){
        $ch = curl_init('http://api.hostip.info/?ip='. $_SERVER['REMOTE_ADDR']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $xml = @curl_exec($ch);
        curl_close($ch);
        
        $dom = new DOMDocument();
        @$dom->loadXML($xml);
        
        return $dom->getElementsByTagName('countryName')->item(0)->nodeValue;
    }
    
    /*
     * @METHOD  addCountToOjectList
     * @DESC    say you have a list of countries separated by ;, and each country has a value
     * @DESC    country1:50;country6:305;united states:53256326
     * @DESC    If it does not exist, add it!
     * @PARAM   object should be a string with the example format
     */
    
    public function addCountToObjectList($object, $index){
        if(!@strpos($object, ';')){
            $list[$index] = 1;
        }else{
            $objects = explode(';', $object);
            
            $list = array();
            foreach($objects as $object){
                if(strpos($object, ':')){
                    $values = explode(':', $object);

                    $list[$values[0]] = $values[1];
                }
            }
        
            if(!isset($list[$index])){
                $list[$index] = 1;
            }else{
                $list[$index]++;
            }
        }
        
        $string = '';
        foreach($list as $item => $value){
            $string .= $item.':'.$value.';';
        }
        
        return $string;
    }
    
    /*
     * @METHOD  getPageName
     * @DESC    returns the name of the page the viewer is on
     */
	
    public function getPageName() {
        $page = preg_replace('#\/(.+)\/#', '', $_SERVER['PHP_SELF']);
        $page = str_replace('/', null, $page);
        return $page;
    }
    
    /*
     * @METHOD  br2nl
     * @DESC    converts break tags to \n (new lines)
     */
    
    public function br2nl($string){
        return str_replace('&lt;br /&gt;', '<br />', $string);
    }
    
    /*
     * @METHOD  remBr
	 * @DESC	get rid of <br /> (such as when you edit a post)
     */
    
    public function remBr($content){
        return str_replace('<br />', null, $content);
    }
    
    /*
     * @METHOD  seconds_to_time
     * @DESC    converts an int (seconds) to a string, E.G:
     * @DESC    4 Days 3 Hours 26 Seconds
     */
    
    public function seconds_to_time($seconds){
            if($seconds == 0) return 'Never.';
            
            //time units
            $units = array('day' => 86400, 'hour' => 3600, 'minute' => 60, 'second' => 1);

            foreach($units as $name => $key)
            {
                    if($k = intval($seconds / $key))
                    {
                            ($k > 1) ? $s .= $k.' '.$name.'s ' : $s .= $k.' '.$name.' ';

                            //update seconds
                            $seconds -= $k*$key;
                    }
            }

            return $s;
    }
	
	/*
		@METHOD	writeToFile
		@DESC	Does what it says - writes to a file
	*/
    
    public function appendToFile($file, array $string){
            $file_handle = fopen($file, 'a');
            
            foreach($string as $string_to_write)
            {
                fwrite($file_handle, '['. date('M-d-Y h:m:s') .'] '.$string_to_write."\n");
            }

            fclose($file_handle);
    }
    
    /*
     * @METHOD  shorten
     * @DESC    shortens the string to the length, then returns it
     * @PARAM   $cutoff  1 = return without words being cut-off
     */
    
    public function shorten($string, $length, $cutoff = false){
        if(!$cutoff)
        {
            return substr($string, 0, (int) $length);
        }
        else
        {
            $string = substr($string, 0, (int) $length);
            return $string = substr($string, 0, strrpos($string, ' '));
        }
            
    }
    
    public function randomString($length){
        $array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7',
                       '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '!', '&', '^', '#', '@', 'a', 'b', 'c', 'd');
        
        $selected = '';
        
        for($x = 0; $x < (int) $length; $x++)
        {
            $selected .= time();
            $selected .= $array[rand(0,56)];
        }
        
        return $this->shorten(hash(sha256, $selected), $length);
    }
}
?>