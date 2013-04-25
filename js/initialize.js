jQuery(document).ready(function($) {
var init = {

cookies: {
imp:1,
ref:document.referrer.replace('http://', '').replace('https://', '')
},
createCookie: function(name, value, days){
	var exdate=new Date();
exdate.setDate(exdate.getDate() + days);
var c_value=escape(value) + ((days==null || days==0) ? "" : "; expires="+exdate.toUTCString())+ "; path=/";
document.cookie=name + "=" + c_value;
},
readCookie: function(name){
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++)
	  {
	  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
	  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
	  x=x.replace(/^\s+|\s+$/g,"");
	  if (x==name)
		{
		 return unescape(y);
		}
	  }
	
},
start: function(){
    var cvalues=this.readCookie('infobar');
    
	if(cvalues!==undefined)
	{
		var cvalues = JSON.parse(cvalues);
		if(typeof cvalues =='object')
		{
			cval=parseInt(cvalues.imp);
			this.cookies.imp = (cval) ? cval + 1 : 1;
			this.cookies.ref=cvalues.ref;
			var mycookie = JSON.stringify(this.cookies);
			this.createCookie('infobar',mycookie);
		}
	}
	else
	{
		var mycookie = JSON.stringify(this.cookies);
		this.createCookie('infobar',mycookie);
	}
this.load();
},
load: function(){
			var href = location.href.replace(location.hash, '').replace('http://', '').replace('https://', '');
			var url = my_ajax.ajaxurl+'?action=infobar_initialize&type='+page.type+'&href='+href;
			var s = document.createElement('script');
			s.id = 'infobar_load';
			s.src = url;
			s.text = 'text/javascript';
			document.getElementsByTagName('head')[0].appendChild(s);
			
		}

};  	
init.start();
});