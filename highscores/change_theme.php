<?php
    if(isset($_POST['theme']))
        setcookie('theme', $_POST['theme'], time() + (10 * 365 * 24 * 60 * 60));
?>