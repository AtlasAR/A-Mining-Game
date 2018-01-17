$(document).ready(function(){
    var lock = $('a[name|="lock"]');
    var sticky = $('a[name|="sticky"]');
    var _delete = $('a[name|="delete"]');
    var hidepost = $('a[name|="hidepost"]');
    
    lock.click(function(){
        var id = $(this).attr('name').split('-')[1];

        $.ajax({
            url: 'ajax/toggle_lock.php',
            type: 'POST',
            data : {id:id},
            success: function(r){
                if(r == 'locked')
                    lock.text('UNLOCK');
                else
                    lock.text('LOCK');
            }
        });
    });
    
    sticky.click(function(){
        var id = $(this).attr('name').split('-')[1];

        $.ajax({
            url: 'ajax/toggle_sticky.php',
            type: 'POST',
            data : {id:id},
            success: function(r){
                if(r == 'stuck')
                    sticky.text('UNSTICK');
                else
                    sticky.text('STICK');
            }
        });
    });
    
    _delete.click(function(){
        if(confirm('Are you sure you wish to delete this thread?')){
            var id = $(this).attr('name').split('-')[1];

            $.ajax({
                url: 'ajax/delete.php',
                type: 'POST',
                data : {id:id},
                success: function(r){
                    alert('Thread deleted.');
                }
            });
        }
    });
    
    hidepost.click(function(e){
        e.preventDefault();
        var id = $(this).attr('name').split('-')[1];

        $.ajax({
            url: 'ajax/toggle_hidepost.php',
            type: 'POST',
            data : {id:id},
            success: function(r){
                if(r == 'hidden'){
                    $('a[name="hidepost-'+ id +'"]').text('unhide');
                    $('table[name="post-'+ id +'"] .left').append('<span name="hidden" style="display:inline-block;clear:both;margin-top:15px;"><b>**hidden**</b></span>');
                }else{
                    $('a[name="hidepost-'+ id +'"]').text('hide');
                    $('table[name="post-'+ id +'"] span[name="hidden"]').remove();
                }
            }
        });
    });
});