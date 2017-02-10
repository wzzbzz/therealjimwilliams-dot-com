function login(my_console){
    
    this.my_console = my_console;
    this.username = "";
    this.password = "";
    this.has_session = false;
    this.domain = 'therealjimwilliams.com';
    
    
    this.uform = '<div id="userform"><form id="username" action="" method="GET" autocomplete="off"><label>Username:</label><input type="text" width="300" name="username" id="username" /></form></div>';
    this.pform = '<div ><form id="password" action="" method="GET" autocomplete="off"><label>Password:</label><form></div>';
    this.lform = '<div style="display:none"><form id="login" action="" method="GET"><input type="hidden" name="action" value="" /><input type="hidden" name="username" id="username" /></form></div>';
    this.cform = '<div style="display:none"><form id="create" action="" method="GET"><input type="hidden" name="username" id="username" /><input type="hidden" name="password" id="password /></form></div>';
    
    login.prototype.check_session = function(){
        my_login = this;
        $.ajax({
            url:"checksession.php",
            success:function(d){
                if (d['result']!=false){
                    my_login.my_console.print("ALREADY LOGGED IN AS "+d['result']+".  LOGOUT IF YOU WANT");
                    my_login.my_console.show_command();
                }
                else{
                    my_login.get_user();
                }
            },
            dataType:'json'
        })
    }
    
    login.prototype.get_user = function(){
        this.my_console.hide_command();
        $(this.my_console.input).append(this.uform);
        $("form#username input#username").focus();
        this.userform_submit();
    }
    
    login.prototype.userform_submit = function(){
        my_login = this;
        $("form#username").submit(function(e){
            e.preventDefault();
            my_login.username = $('form#username input#username').val();
            my_login.my_console.print("Username: "+my_login.username);
            $("#userform").remove();
            $.ajax({
                url:'checkuser.php',
                data:'u='+my_login.username,
                success: function(d){
                    if (d['result']=='true'){
                        my_login.capture_password("login");
                    }
                    else{
                        if (d['reason']=='USERLOGGEDIN'){
                            my_console.print("'"+my_login.username+"' ALREADY LOGGED IN.  LOGOUT IF YOU WANT.");
                            my_console.show_command();
                        }
                        else{
                            my_login.prompt_create();
                        }
                    }
                },
                dataType:'json'
                })
            return false;
        })
    }
    
    login.prototype.prompt_create = function(){
        my_console.print("USERNAME: " + this.username);
        my_console.print("NEW USER.  CREATE? (Y/N)");
        my_login=this;
        $(document).keydown(function(e){
            e.preventDefault();
            if (e.keyCode==89){
                //yes
                $(document).unbind("keydown");
                my_login.capture_password("create");

            }
            else if (e.keyCode==78){
                //no
                $(document).unbind("keydown");
                my_console.show_command();
            }
        });
    }
    
    login.prototype.password_form_submit = function(){
    }
    
    login.prototype.capture_password = function(action){
        var buff = "";
        my_login = this;        
        my_console.append(my_login.pform);

        $(document).keydown(function(e){
            e.preventDefault();
            if (e.keyCode==13){
                $(document).unbind("keydown");
                if (action == "create")
                    my_login.confirm_password(buff);
                else{
                    my_login.password = buff;
                    my_login.do_login();
                }
            }
            else{
                buff += String.fromCharCode(e.keyCode);
            }
        })
    }
    
    login.prototype.confirm_password = function(pass){
        my_console.print("CONFIRM PASSWORD: ");
        my_login = this;
        var buff="";
        $(document).keydown(function(e){
            e.preventDefault();
            if (e.keyCode==13){
                $(document).unbind("keydown");
                if (buff==pass){
                    my_login.password = pass;
                    my_login.create_user();
                }
                else{
                    my_console.print("PASSWORDS DON'T MATCH");
                    my_console.show_command();
                }
            }
            else{
                buff += String.fromCharCode(e.keyCode);
            }
            });
    }
    
    login.prototype.create_user = function(){
        my_console = this.my_console;
        my_login = this;
        $.ajax({
            url : "create_user.php",
            data : "u="+my_login.username+"&p="+my_login.password,
            success : function(d){
                my_console.print("CREATED USER "+my_login.username);
                my_console.print("YOU MAY NOW LOG IN WITH YOUR USERNAME / PASSWORD");
                my_console.show_command();
            },
            dataType : "json"
            })
    }
    
    login.prototype.do_login = function(){
        my_console = this.my_console;
        my_login = this;
        $.ajax({
         type: 'GET',
            url: 'dologin.php',
            data: "u="+my_login.username+"&p="+my_login.password+"&d="+my_login.domain,
            success: function(d){
                my_console.print(d['message']);
                my_console.show_command();
            },
            dataType: "json"
        });
        
        
    }
    
    
}


var my_login = new login(my_console);
my_login.check_session();
