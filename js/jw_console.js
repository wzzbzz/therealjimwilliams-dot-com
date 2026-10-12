function jw_console(){

    this.status = "ok";
    
    this.output = $("#output");
    this.input = $("#input");
    
    jw_console.prototype.clear = function(){
        this.output.text("");
    }
    
    jw_console.prototype.print = function(text){
        o = "<div>"+text+"</div>";
        this.output.append(o);
    }
    
    jw_console.prototype.hide_command = function(){
        this.input.children("form#f").hide();
    }
    
    jw_console.prototype.show_command = function(){
        $('form#f input#a').one("focus", function() {
            $(this).val("");
          });
        this.input.children("form#f").show();
        console.log("setting focus");
        this.input.find("form#f input#a").focus();
        $("html").scrollTo($(document).height()+9999);
    }

    jw_console.prototype.append = function(dom){
        this.output.append(dom);
    }
    
    jw_console.prototype.listener = function(){
        $.ajax({
            url:"update.php",
            success : function(d){
                this.print(d['message']);
            },
            dataType : "json"
        });
    }
}