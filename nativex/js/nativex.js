//client side coding for nativex encoding application.

function nativex()
{
    this.key="default";
    this.c="";
    this.result="";
    this.action="";
	this.special="";
    
    nativex.prototype.set_key = function()
    {
        this.key = $("#key").val();
    }
    
    nativex.prototype.crypt = function(key,c,action)
    {
        my_nativex=this;
		special="";
		data = { key: my_nativex.key, c:my_nativex.c, action : my_nativex.action };
        console.log(data);
        /*
		if (this.special=="checked"){
			url += "&special=checked";
		}*/
        $.ajax({
            type:'post',
            url:"/donative.php",
            data:data,
            dataType: 'json',
            async : false,
            success:function(d)
            {
                my_nativex.result=d['result'];
                console.log(my_nativex.result);
            }
            });

        return my_nativex.result
    }
    
    nativex.prototype.get_scrypt_list = function()
    {
        
        mynativex=this;
        var s=$("#u_s").val();
        var u=$("#u_u").val();
        var k=$("#u_k").val();
        
        $.ajax({
            url:"https://www.therealjimwilliams.com/nativex/get_scrypts.php",
            data:"s="+s+"&u="+u+"&k="+k,
            dataType:"json",
            success:function(d){

                if(d.result=='failure'){
                   console.log('failure');
                }
                else
                {
                    mynativex.scripts = d.scrypts;
                    for (i=0;i<d.scrypts.length;i++)
                    {
                        eval(d.scrypts[i].scrypt);
                    }
                    new_nativex = new nativex();
                    test = new_nativex.bandit("this is a test",1);
                    console.log(test);
                    console.log(new_nativex.bandit(test,-1));
                }
                
            }
        });
        
        
    }
    
    
    nativex.prototype.encode = function(text)
    {
        text = this.base64(text,1);
		text = this.shell_shock(text,1);
		text = this.straight_shuffle(text,1);
		text = this.base64(text,1);
		text = this.shell_shock(text,1);		
		text = this.straight_shuffle(text,1);
        
        return text;
    }
    
    nativex.prototype.decode = function(text)
    {
        text = this.straight_shuffle(text,-1);
        text = this.shell_shock(text,-1);
        text = this.base64(text,-1);
		text = this.straight_shuffle(text,-1);		
        text = this.shell_shock(text,-1);
		text = this.bandit(text,-1);		
        text = this.base64(text,-1);
        
        return text;
    }
    
    nativex.prototype.base64 = function(text,encoding)
    {
     
        if (encoding==1)
        {
            return btoa(text);
        }
        if (encoding==-1)
        {
            return atob(text);
        }
    }
    
    nativex.prototype.generateX = function(key,x)
    {
        x = 2^x;
        total=0;
        for(i=0;i<key.length;i++)
        {
            total += key.charCodeAt(i);
        }

        return total%x+1;
    }

    nativex.prototype.straight_shuffle = function(text,encode)
    {
        even = new String();
        odd = new String();
        var shuffled = new String();
        if(encode==1)
        {
            for (i=0;i<text.length;i++)
            {
                if(i%2==0)
                {
                    even+=text.charAt(i);
                }
                else
                {
                    odd+=text.charAt(i);
                }
            }
            var shuffled = even+odd

            return shuffled;
        }
        else
        {
            split = Math.ceil(text.length/2);
            even = text.slice(0,split);
            odd = text.slice(split);
            for(i=0;i<even.length;i++)
            {
                if(even[i]!==undefined)
                {
                    shuffled+=even[i];
                }
                if(odd[i]!==undefined)
                {
                    shuffled+=odd[i];
                }
            }
            
            return shuffled;
            
        }
        
    }
    
    
    nativex.prototype.shell_shock = function(text,encode)
    {
        
        for(i=0;i<Math.floor(text.length/2);i++)
        {
            if(i%2==1)
            {
                temp = text.charAt(i);
                text = text.replaceAt(i,text.charAt(text.length-i-1));
                text = text.replaceAt(text.length-i-1,temp);
            }
        }
        return text;
    }
    
    nativex.prototype.split_deck = function(text,encode)
    {
     
		if (encode==1)
		{
            top = text.slice(0,Math.floor(text.length/2));
            bottom = text.slice(Math.floor(text.length/2));
            return bottom+top;
		
		}
		else
		{
            top = text.slice(0,Math.ceil(text.length/2));
            bottom = text.slice(Math.ceil(text.length/2));
            return bottom+top;
		}
	
    }

}

function atos(arr)
{
    text="";
    for(i=0;i<arr.length;i++)
    {
        text += arr[i];
    }
    return text;
}

String.prototype.replaceAt=function(index, character) {
      return this.substr(0, index) + character + this.substr(index+character.length);
   }
