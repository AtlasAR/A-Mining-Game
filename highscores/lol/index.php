<html>
<head>
	<title>Highscores</title>
	<script type="text/javascript" src="../resources/jquery2.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
                        getMessages();
			setTimeout(function(){
				var audioElement = document.createElement('audio');
				audioElement.setAttribute('src', '../sound/dasboot.mp3');
				audioElement.volume = 0.5;
				audioElement.play();
			},4000);
                        
                        setInterval(function(){
                            getMessages();
                        },1000);
                        
                        $('#submit').click(function(){
                            submitMessage($('input[name="message"]').val());
                        });
                        
                        function submitMessage(message){
                            $.ajax({
                                url: 'derp.php',
                                type: 'POST',
                                data : {
                                    message : message
                                },
                                success: function(){
                                    $('input[name="message"]').val('');
                                    getMessages();
                                }
                            });
                        }
                        
                        function getMessages(){
                             $.ajax({
                                url: 'derp.txt',
                                success: function(chat){
                                    $('#chatbox').html(chat);
                                }
                            });
                        }
		});
	</script>
</head>
</html>
<center>

	<img src="http://i.imgur.com/wjMfggG.gif" style="margin-top:30px;">
	<br/>
	<span style="font-size:5px;">'dev is kill'<br/>'no'</span>
        <br/><br/>
        <table>
            <tr>
                <td><textarea id="chatbox" style="width:600px;height:200px;"></textarea></td>
            </tr>
            <tr>
                <td><input type="text" name="message" style="width:200px;"> <button id="submit">Send Message</button></td>
            </tr>
        </table>
</center>