function nativex(my_console){
    
    this.my_console = my_console;
    this.key="";
    this.text="";

    
    this.kform = '<div id="keyform"><form id="key" action="" method="GET" autocomplete="off"><label>Key:</label><input type="text" width="300" name="key" id="key" /></form></div>';
    this.cform = '<div id="cform"><form id="c" action="" method="GET" autocomplete="off"><label>Code:</label><input width="" name="c" id="c" rows="100" columns="300"></form></div>';
    //this.lform = '<div style="display:none"><form id="login" action="" method="GET"><input type="hidden" name="action" value="" /><input type="hidden" name="username" id="username" /></form></div>';
    //this.cform = '<div style="display:none"><form id="create" action="" method="GET"><input type="hidden" name="username" id="username" /><input type="hidden" name="password" id="password /></form></div>';
    
    nativex.prototype.get_key= function(){
        this.my_console.hide_command();
        $(this.my_console.input).append(this.kform);
        $("form#key input#key").focus();
        this.keyform_submit();
    }
    
     nativex.prototype.get_c= function(){
        $(this.my_console.input).append(this.cform);
        $("form#c input#c").focus();
        this.cform_submit();
    }
    
    nativex.prototype.cform_submit = function(){
        my_nativex = this;
        $("form#c").submit(function(e){
            e.preventDefault();
            my_nativex.c = btoa($('form#c input#c').val());
            console.log(my_nativex.c);
            my_nativex.my_console.print("Content: "+my_nativex.c);
            $("#keyform").remove();
            
            $.ajax({
            url:"donative.php",
            data:"key="+my_nativex.key+"&c="+my_nativex.c+"&action=[[ACTION]]",
            success:function(d){
                if (d['result']!=false){
                    console.log("hi");
                    console.log(d['result']);
                    my_nativex.my_console.print(d['result']);
                    my_nativex.my_console.show_command();
                }
            },
            dataType:'json'
        })
            
            return false;
        })
    }
    nativex.prototype.keyform_submit = function(){
        my_nativex = this;
        $("form#key").submit(function(e){
            e.preventDefault();
            my_nativex.key = $('form#key input#key').val();
            my_nativex.my_console.print("Key: "+my_nativex.key);
            $("#keyform").remove();
            my_nativex.get_c();
            return false;
        })
    }
    
    
    
    
}


var my_nativex = new nativex(my_console);
my_nativex.get_key();
