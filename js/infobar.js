jQuery(document).ready(function($) {
var gvar= {
home:'',
purl:''
};

var cookies= {
imp:1,
id:0,
ref: '',
setting:'visiting'
};
	
var infobar = {
events : function(){

   $(document).on({
		 click: function() {
        	 
		$('.infobar').fadeOut('slow');
		if(bar.settings.preview!=1)
		{
			cookies.setting = 'visited';
			var mycookie = JSON.stringify(cookies);
			
			infobar.createCookie(bar.settings.cookie,mycookie,parseInt(bar.settings.closetime, 10));
		}
	  },
	  mouseenter: function() { 
	    //alert('enter');
		$(this).attr('src',gvar.purl + 'images/close_hover.png');
	  },
	  mouseleave: function () {
	   //alert('leave');
		 $(this).attr('src', gvar.purl + 'images/close.png');
	  }
	},'.info_bar > img');
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
deletecookie: function(name){
	//$.cookie(name,null);
	return true;
},
start: function()
{
    this.preload(bar.images);
	if(bar.settings.preview==1)
	{
	   var cvalues=this.readCookie('infobar');
	   if(cvalues!==undefined)
		{
		  var cvalues = JSON.parse(cvalues);
		  if(typeof cvalues =='object')
			{
			   if(parseInt(cvalues.imp) >= 1)
			   {
			     cvalues.imp = cvalues.imp - 1;
			   }
			   
			   var mycookie = JSON.stringify(cvalues);
			  this.createCookie('infobar',mycookie);
			}
		}
	   this.load();
	}
	else
	{
		var cvalues=this.readCookie('infobar');
		
		if(cvalues!==undefined)
		{
		  var cvalues = JSON.parse(cvalues);
		  if(typeof cvalues =='object')
			{
			   
			   cval = cvalues.imp;
			   cookies.ref = cvalues.ref;
			}
			else
			{
			  //alert('cookie empty');
			  cval=-1;
			}
		}
		else
		{
		  //alert('cookie failed');
		 cval=-1;
		}
		
		if (parseInt(cval) < bar.settings.imp) 
			{
				 //alert('still less imp');
				return false;
			}
			
		var cvalues=this.readCookie(bar.settings.cookie);
		if(cvalues!==undefined)
		{
			var cvalues = JSON.parse(cvalues);
			if(typeof cvalues =='object')
			{
				cookies.id = cvalues.id;
				cookies.imp = cvalues.imp;
				cookies.setting = cvalues.setting;
				// Don't Open if visited
				if (cookies.setting === 'visited') 
				{
					return false;
				}
			}
		}
	  this.load();
	}
	
	
		
},
stripSlashes: function(str){
		return (str+'').replace(/\\(.?)/g, function (s, n1) {
			switch (n1) {
				case '\\':
					return '\\';
				case '0':
					return '\0';
				case '':
					return '';
				default:
					return n1;
			}
		});
	},
preload: function(images) {
	$.each(images, function(name, value) {
		$('<img/>')[0].src = value;
		// Alternatively you could use:
        // (new Image()).src = this;
	});
},
load: function() {
var slashGex = new RegExp(/(\\+)'/);
var copy = this.stripSlashes(bar.div).replace(slashGex, "'");
//alert(JSON.stringify(copy));
	if(bar.settings.display_on==1)
		{
			$('body').prepend(copy);
			
		}
		else
		{
			$('body').append(copy);
		}
		
		if($('#infobar_link > img').length)
		{ 
		   $('.infobar').hide();
		   /*$('<img />').load( function(){
				  console.log('loaded');
				  $('.infobar').show().height($('.infobar').height());
				}).attr('src', bar.images.button); */
				
		   $('#infobar_link > img').load(function(){
		       // alert('loaded');
				$('.infobar').show().height($('.info_bar').height());
				if(bar.settings.preview!=1)
				{ 
					infobar.addimp();
				}
			});
			
		}
		else
		{
		 $('.infobar').height($('.info_bar').height());
		 
		 if(bar.settings.preview!=1)
		   {
			this.addimp();
		   }
		}
	
	gvar.home = this.stripSlashes(bar.settings.home).replace(slashGex, "'");
	gvar.purl = this.stripSlashes(bar.settings.purl).replace(slashGex, "'");

},
addimp: function(){

var data = {
		action: 'infobar_addimp',
		barid:bar.id,
		session:bar.settings.session
	};
	$.post(my_ajax.ajaxurl, data, function(data) 
	{
	    
		var cvalues=infobar.readCookie(bar.settings.cookie);
		if(cvalues!==undefined)
		{
			var cvalues = JSON.parse(cvalues);
			if(typeof cvalues =='object')
			{
			  
				cookies.imp = (cookies.imp) ? cookies.imp + 1 : 1;
				var mycookie = JSON.stringify(cookies);
				infobar.createCookie(bar.settings.cookie, mycookie); 
			}
			else 
			{
				 var mycookie = JSON.stringify(cookies);
				infobar.createCookie(bar.settings.cookie, mycookie); 
			}
		}
		else 
		{
			 var mycookie = JSON.stringify(cookies);
			infobar.createCookie(bar.settings.cookie, mycookie); 
		}
	}
	    
	);

}

};  

infobar.start();
infobar.events();
		
});