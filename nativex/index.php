<?php
include("../bootstrap.php");
include("../_lib/includer.php");

$db = _db();
$logged = $db->insert(array('table'=>'visitors','keyvals'=>array("domain"=>'cryptstack.com', "ip"=>$_SERVER['REMOTE_ADDR'],'stamp'=>date('Y-m-d H:i:s',time()))));
_user();
$user = new user();

$logged_in=$user->session_logged_in("cryptstack.com");
$users = $user->get_logged_in_users("cryptstack.com");
$id = $user->get_logged_in_user_id();

//$user->initialize_user($id);

?>
<!DOCTYPE html>
<html>
<head>
<title>Cryptstack.com</title>
<?php _basejs();?>
<style>
    #header ul{list-style:none;display:inline;}
    #results {width:1000px;max-width:1000px;word-wrap:break-word;}
    #results p{word-wrap:break-word;}
</style>
<script type="text/javascript" src="js/nativex.js"></script>
<script>
    var my_nativex = new nativex();
    $(document).ready(function()
        {
            $("#do-crypt").click(function(e){
                my_nativex.key = $("#key").val();
                my_nativex.c = btoa($("#c").val());
                my_nativex.action = $("#action:checked").val();
                my_nativex.special = $("#special").attr("checked");
     
                e.preventDefault();
                result = my_nativex.crypt();

                $("#results p").html("<pre>"+result+"</pre>");
                return false;
            }
         );
            
            $("#login").click(function(e){
               $.ajax({
                url:"/dologin.php",
                data:'u=' + $("#u").val() + "&p=" + $("#p").val() + "&d=cryptstack.com",
                dataType:"json",
                success:function(d){
                    if(d['result']=='fail')
                    {
                        console.log("failed");
                    }
                    else
                    {
                        console.log(d);
                        $("#loginform").hide();
                        $("#loggedin").show();
                        $("#username").text(d.username);
                    }
                }
                
                
                });
            });
            my_nativex.get_scrypt_list();
        
    });
    
</script>

</head>
<body>
<input type="hidden" id="u_u" value="<?php echo $user->get_logged_in_username();?>" />
<input type="hidden" id="u_k" value="<?php //echo $user->get_logged_in_userkey();?>" />
<input type="hidden" id="u_s" value="<?php echo $_REQUEST['PHPSESSID']?>" />
    <div id="header">
    <!--    <ul>
            <li id="sessionstuff">
                <div id="loginform" <?php if((true==$logged_in)):?>style="display:none"<?php endif;?>>
                    <a href="javascript:void(0);" id="login">Login</a>
                    <div id="inputs">
                        <div><input type="text" name="u" id="u" /></div>
                        <div><input type="text" name="p" id="p" /></div>
                        
                    </div>
                </div>
                <div id="loggedin" <?php if(!(true==$logged_in)):?>style="display:none"<?php endif;?>>
                    'Sup <span id="username"><?php echo $user->get_logged_in_username();?></span>
                </div>
            </li>
        </ul>
        -->
    </div>
    <div id="left">
        <div id="text_entry">
            <textarea rows="10" cols="100" id="c" name="c" value=""></textarea>
        </div>
        <div id="key_entry">
            #<input type="text" width="300" id="key" name="key" value = "default" />
        </div>
        <div id="button_wrap"><input type="button" id="do-crypt" name="do-crypt" value="Crypt!" /></div>
        <div id="radio_wrap">
            <p>encode <input type="radio" id="action" name="action" value="classic_encode" checked></p>
            <p>decode <input type="radio" id="action" name="action" value="classic_decode"></p>
        </div>
        <div id="checkbox_wrap">
            <p>special <input type="checkbox" id="special" name="special" value="special"></p>
        </div>
        <div id="results"><p></p></div>
    </div>
    <!--<div id="right">
        <h3>logged in users</h3>
        <ul>
            <?php foreach($users as $user):?>
            <li><?php echo $user->username;?></li>
            <?php endforeach;?>
        </ul>
    </div>
    -->
</body>
</html>
