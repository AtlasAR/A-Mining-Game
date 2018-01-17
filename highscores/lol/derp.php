<?php

if(isset($_POST['message'])){
    $yes = file_get_contents('derp.txt');
    
    $fh = fopen('derp.txt', 'w');
    fwrite($fh, htmlentities($_POST['message'])."\n".$yes);
    fclose($fh);
}

?>
