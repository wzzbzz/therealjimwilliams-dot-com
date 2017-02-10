<?php
include("bootstrap.php");
include("_lib/includer.php");

$db = _db();
$logged = $db->insert(array('table'=>'visitors','keyvals'=>array("domain"=>'therealjimwilliams.com', "ip"=>$_SERVER['REMOTE_ADDR'],'stamp'=>date('Y-m-d H:i:s',time()))));

?>
<!DOCTYPE html>
<html>
<head>
<title>Who is the real Jim Williams?</title>
<?php _basejs();?>
<script type="text/javascript" src="/js/jw_console.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    my_console = new jw_console();
    $("#f").submit(function(e){
        e.preventDefault();
        my_console.print($("#a").val());
        $.ajax({
            type: 'POST',
            url: 'listen.php',
            data: "t="+$("#a").val(),
            success: function(d){
                if (d['action']=="OUTPUT"){
                    my_console.print(d['data']);
                }
                else if(d['action']=="EXECUTE"){
                    eval(d['data']);
                }
                },
            dataType: "json"
          });
        $("#a").val("");
        $("#a").focus();
        $(document).scrollTop($(document).height());
        return false;
    });
    
	getEvents();
    id = setInterval('getEvents()',1000);

    $("#a").focus();
});

function getEvents(){
    $.ajax({
        type: 'POST',
        url: "get_events.php",
        success: function(d){
            for(i=0;i<d.length;i++){
                e = d[i];
                if (e['type']=='EXECUTE'){
                    eval(e['js']);
                }
            }
        },
        dataType:'json'
    })
}

</script>

<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<style>

@font-face {
	font-family: apple;
	src: url('fonts/PrintChar21.ttf');
}

@font-face {
	font-family: smallapple;
	src: url('fonts/PRNumber3.ttf');
}

@font-face {
	font-family: commodore-angled;
	src: url('fonts/Commodore Angled v1.2.ttf');
}

@font-face {
	font-family: commodore-rounded;
	src: url('fonts/Commodore Rounded v1.2.ttf');
}

@font-face {
	font-family: commodore-v63;
	src: url('fonts/Commodore-64-v6.3.TTF');
}

@font-face {
	font-family: trs-80;
	src: url('fonts/TRS-80.TTF');
}

body.apple{
    background:#000;
    color:#33ff66;
    font-family: apple;
    font-size:16px;
    
}

body.c64{
    background:#0000ff;
    color:#6699ff;
    font-family:commodore-rounded;
    font-size:16px;
}

body.trs80{
    background:#000;
    color:#fff;
    font-family:smallapple;
    font-size:16px;
}
input:focus {
    outline: none !important;
}

input{
    font-size:16px;
    background:none;
    border:none;
}
.apple input{
    color:#33ff66;
    font-family:apple;
}

.apple.smalltext{
    font-family:smallapple;
}

.c64 input{
    color:#6699ff;
    font-family:commodore-rounded;
}
.c64.smalltext{
    font-size:14px;
}

.trs80 input{
    color:#fff;
    font-family:smallapple;
}
.trs80.smalltext{
    font-size:14px;
}

#input{
    padding-bottom:40px;
}
</style>
<body class="trs80">
<div id="console">
    <div id="output"></div>
    <div id = "input">
        <form action="" id="f" autocomplete = "off">
            ]<input type="text" name="a" id="a" />
        </form>
    </div>
</div>
</body>

</html> 
