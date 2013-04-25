(function($){
 var schemes = [
    ['Plain Gray','#DDDDDD','#333333','#0092CC'],
    ['Browser Style','#F9E4A0','#000000','#03189E'],
    ['Hot Sun','#FFBB00','#D96900','#2E091D'],
    ['Chrome Blue','#BAD0EA','#000000','#03189E'],
    ['Michael Jackson','#000000','#A8A8A8','#FFFFFF']
];

var i = $('.form-table').length;

$('.bvariant').each(function() {
					var id=$(this).attr('id').replace(/[a-zA-Z]+_/g, '');
					update_infobar(id);
				});	
				
	  $('.dispay-setting').click(function() {
			if($('#display_everyday').is(':checked')) 
			{ 
				$('.tr-date-picker').hide(); 
				$('#infobar_start_date').val('');
				$('#infobar_end_date').val('');
			}else
			{
				$('.tr-date-picker').show();
			}
	});

	
	$(document).on('change','.color-picker',function() {
	           var val = $(this).val();
	       var id=$(this).attr('id').replace(/[a-zA-Z]+_/g, '');
		      
			 for (var i = 0; i < schemes.length; i++) 
			 {
				if(i==val)
				{
					$('#bgColor_'+id).attr('value',schemes[i][1]);
					$('#bgColor_'+id).css('backgroundColor',schemes[i][1]);
					$('#textColor_'+id).attr('value',schemes[i][2]);
					$('#textColor_'+id).css('backgroundColor',schemes[i][2]);
					$('#linkColor_'+id).attr('value',schemes[i][3]);
					$('#linkColor_'+id).css('backgroundColor',schemes[i][3]);
					
				    update_infobar(id);
			   }
			}	
	});
   var dateToday = new Date();
		var dates = $( "#infobar_start_date, #infobar_end_date" ).datepicker({
			defaultDate: dateToday,
			changeMonth: true,
			numberOfMonths: 3,
			dateFormat: 'dd/mm/yy',
			minDate: dateToday,
			onSelect: function( selectedDate ) {
				var option = this.id == "infobar_start_date" ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
		
		
		
	$(document).on('change','.linktype',function (){
	    var id=$(this).attr('id').replace(/[a-zA-Z0-9]+_/g, '');
		
		if($(this).val()==='0')
		{
		   
		  $('#tr-cimage_'+id).show();

		  update_infobar(id);
		}
		else if($(this).val()==='-1')
		{
		  $('#tr-cimage_'+id).hide();
		 
		  update_infobar(id);
		}
	});
  function colorPickMulti(id,itemid)
  {
			$(itemid).ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val(hex);
					$(el).ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				},
				onChange: function ( hsb, hex, rgb,el ) {
					$(itemid).attr('value','#' + hex);
					$(itemid).css('backgroundColor', '#' + hex);
					
					update_infobar(id);
				}
	  })
	  .bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
	  });
 }
	
	$(document).on('focus','.pickcolor',function(){
		var itemid=$(this).attr('id');
		var id=$(this).attr('id').replace(/[a-zA-Z]+_/g, '');
		itemid='#'+itemid;
		
		colorPickMulti(id,itemid);
	});
	
    $('#addv').click(function() {
		 i++;
		 var bars = $('.bvariant').length;
		 bars++;

		var variant='<div id="bvariant_'+i+'" class="bvariant" >';
		variant+='<h3 class="var-header">Infobar Variant '+bars+'</h3>';
		variant+='<table class="form-table" ><tbody>';
		variant+='<tr valign="top">';
		variant+='<th scope="row"><label for="mtext">Variant name</label></th>';
		variant+='<td><input type="text" name="vname[]" id="vname_'+i+'" value="Variant '+bars+'" class="regular-text vname"></td>';
		variant+='</tr>';
		variant+='<tr valign="top">';
		variant+='<th scope="row"><label for="mtext">Message text</label></th>';
		variant+='<td><input class="regular-text mtxt" value="Add Your Message Here" id="mtext_'+i+'" name="mtext[]" type="text"></td>';
		variant+='</tr>';
		variant+='<tr valign="top">';
		variant+='<th scope="row">Link type</th>';
		variant+='<td><fieldset><p>';
		variant+='<label for="linktype"><input type="radio" name="linktype['+i+']" id="typelink_'+i+'" class="linktype" value="-1" checked="checked" >&nbsp;&nbsp;&nbsp;text</label><br>';		
		variant+='<label for="linktype"><input type="radio" name="linktype['+i+']" id="typeimage_'+i+'" class="linktype" value="0">&nbsp;&nbsp;&nbsp;text and custom image</label>';
		variant+='</p></fieldset></td>'
		variant+='</tr>';
		variant+='<tr valign="top" style="display:none" id="tr-cimage_'+i+'">';
		variant+='<th scope="row"><label for="cmage">Image URL</label></th>';
		variant+='<td><input type="text" class="regular-text cimage" value="http://" id="cimage_'+i+'" name="cimage[]">&nbsp; recommended size 32x32</td>';
		variant+='<tr valign="top">';
		variant+='<th scope="row"><label for="preview">Live preview</label></th>';
		variant+='<td id="preview_'+i+'" class="preview-ver"></td>';
		variant+='</tr>';
		variant+='<tr valign="top">';
		variant+='<th scope="row"><label for="ltext">Link text</label></th>';
		variant+='<td><input class="regular-text ltxt" value="Enter Link Text" id="ltext_'+i+'" name="ltext[]" type="text"></td>';
		variant+='</tr>';
		variant+='<tr valign="top">';
		variant+='<th scope="row"><label for="lurl">Link url</label></th>'
		variant+='<td><input class="regular-text lurl" value="http://example.com" id="lurl_'+i+'" name="lurl[]" type="text"></td>';
		variant+='</tr>';
		variant+='<tr><th scope="row">Color scheme</th>';
		variant+='<td><fieldset>';
		variant+='<select name="theme[]" class="color-picker" id="colorpicker_'+i+'" >';
		
		for (var ii = 0; ii < schemes.length; ii++) 
			{
					variant+='<option value="'+ii+'" >'+schemes[ii][0]+'</option>';
			}	
			
		variant+='</select></fieldset></td></tr>';
		variant+='<tr valign="top" class="tr-color_'+i+'">';
		variant+='<th scope="row"><label for="bgColor">Background colour</label></th>';
		variant+='<td><input id="bgColor_'+i+'" class="pickcolor bgColor" name="options[bgColor][]" value="#DDDDDD" type="text" style="background-color:#DDDDDD"></td>';
		variant+='</tr><tr valign="top" class="tr-color_'+i+'">';
		variant+='<th scope="row"><label for="textcolor">Text color</label></th>';
		variant+='<td><input id="textColor_'+i+'" class="pickcolor textColor" name="options[textColor][]" value="#333333" type="text" style="background-color:#333333"></td>';
		variant+='</tr><tr valign="top" class="tr-color_'+i+'" >';
		variant+='<th scope="row"><label for="linkColor">Link color</label></th>';
		variant+='<td><input id="linkColor_'+i+'" class="pickcolor linkColor" name="options[linkColor][]" value="#0092cc" type="text" style="background-color:#0092cc"></td>';
		variant+='</tr><tr valign="top">';
		variant+='<th scope="row"><label for="predel"></label></th>';
		variant+='<td><a id="delete_'+i+'" class="delete-ver" href="#">Delete</a></td>';
		variant+='</tr></tbody></table>';
		variant+='</div>';
		
		var j=1;
		$('.bvariant').each(function() {
					$(this).children("h3:first").text('Infobar Variant '+j);
					j++;
				});	
				
        $(variant).fadeIn('slow').appendTo('#bar-variant');
		update_infobar(i);
		return false;
    });
 
	$('.status-ver').on('click',function(){
		var id=$(this).attr('id').replace(/[a-zA-Z]+_/g, '');
		$('#tspin_'+id).html('<img src="'+ my_url.purl + 'images/ajax.gif" />');
		var status=$(this).text()=='Active'? 0 : 1;
		
		var mid=$('#mid_'+id).val();
		

			var data = {
					 action:  'AdminAjax',
					 id:       mid,
					 status: status,
					 dowhat:   'change_status'
				   };
				 $.post(ajaxurl, data, function(response) {
					// alert('Got this from the server: ' + response);
						if(!response)
						{
						 alert('Error!!! please try again');
						 $('#tspin_'+id).html('');
						}
						else
						{
						   // show response from the php script.
						 
						   $('#tspin_'+id).html('');
						   if(status)
						   {
						    $('#status_'+id).text('Active');
						   }
						   else
						   {
						    $('#status_'+id).text('Disabled');
						   }
						
						}
		   
					});
		return false;
	});
	
	$(document).on('click','.delete-ver',function(){
	
		var id=$(this).attr('id').replace(/[a-zA-Z]+_/g, '');
		var mid=$('#mid_'+id).val();
		var pid = $('input[name="title_id"]').val();
		
		if(mid===undefined)
		{
		  $('#bvariant_'+id).fadeOut("slow", function() {
					   $(this).remove();
					   });
		}
		else
		{
			if(disp_confirm('Are you sure? This can\'t be undone.'))
		   {
		     $('#tspin_'+id).html('<img src="'+ my_url.purl + 'images/ajax.gif" />');
			var data = {
					 action:  'AdminAjax',
					 id:       mid,
					 pid:      pid,
					 dowhat:   'vdelete'
				   };
				 $.post(ajaxurl, data, function(response) {
					// alert('Got this from the server: ' + response);
					if(!response)
					{
					 alert('Error!!! please try again');
					 $('#tspin_'+id).html('');
					}
					else
					{
					   // show response from the php script.
					   $('#tspin_'+id).html('');
					   $('#bvariant_'+id).fadeOut("slow", function() {
					   $(this).remove();
					   });
					  
					}
		   
					});
			}
		}
		return false;
	});
     // 
	 
	 $("h3.slidebox-link, .sidebar-name-arrow").click(function(event) {
   event.preventDefault();
   
   if ($("div.slidebox#"+event.target.id+"-box").css('display') == 'none') {
        $("div.slidebox#"+event.target.id+"-box").slideDown();
        //event.target.innerHTML = 'Collapse';
   }
   else {
        $("div.slidebox#"+event.target.id+"-box").slideUp();
        //event.target.innerHTML = 'Expand';
   }
   });
   
    $("#infobar_show_where").change(function() {
		var val = $(this).val();
		
		$(".chkpage1").attr('checked', false);
		$(".chkcat1").attr('checked', false);
		$(".chkpost1").attr('checked', false);

		if(val=='post')
		{
		  $("#showpages").hide();
		  $("#showcategories").hide();
		  $("#showposts").show();
		  
		  $(".tr-exclude").fadeOut(2000);
		}
		else if(val=='page')
		{
		  $("#showposts").hide();
		  $("#showcategories").hide();
		  $("#showpages").show();
		  
		  $(".tr-exclude").fadeOut(2000);
		}
		else if(val=='category')
		{
		   $("#showposts").hide();
		  $("#showpages").hide();
		  $("#showcategories").show();
		  
		   $("#showpages_1").fadeOut('fast');
		  $("#showcategories_1").fadeOut('fast');
		  $("#showposts_1").fadeIn('fast');
		  $(".tr-exclude").fadeIn(2000);
		  
		}
		else
		{
		 if(val=='home' || val=='-1')
		 {
		  $(".tr-exclude").fadeOut(2000);
		 }
		 else
		 {
		   
		  $("#showpages_1").fadeIn('fast');
		  $("#showcategories_1").fadeIn('fast');
		  $("#showposts_1").fadeIn('fast');
		  $(".tr-exclude").fadeIn(2000);
		 
		 }
		 $("#showposts").hide();
		  $("#showpages").hide();
		  $("#showcategories").hide();
		}
	});
	
	
	
	$(".chkall").change(function() {
	     var id= $(this).attr('id');
		 if($(this).is(':checked'))
		 {
		   $('.'+id).each(function(i) {
					  if(!$(this).is(':disabled'))
					  {
						$(this).attr('checked', 'checked');
					  }
				});
		 }
		 else
		 {
		   $('.'+id).removeAttr('checked');
		 }
	
	});
	
			
	
    $("#SubmitButton").click(function() {
	    if(!validate())
		{
		  alert('Oops! Please review the settings highlighted in red before saving again, you can\'t leave them blank!');
		  return false;
		}
		
		var type=$('#infobar_show_where').val();
		 			 
			 if(type=='post')
			 {
			     var count=0;
			    $(".chkpage").attr('checked', false);
			  $(".chkcat").attr('checked', false);
			   $("input[name='chk"+type+"[]']:checked").each(function(i) {
				  count++;
				});
				
				if(count==0)
				{
				  alert('Please select a post before saving the Campaign');
				  return false;
				}
			 }
			 else if(type=='page')
			 {
			   var count=0;
			   $(".chkcat").attr('checked', false);
			  $(".chkpost").attr('checked', false);
			   $("input[name='chk"+type+"[]']:checked").each(function(i) {
				 count++;
				});
				if(count==0)
				{
				  alert('Please select a page before saving the Campaign');
				  return false;
				}
			 }
			 else if(type=='category')
			 {
			   var count=0;
			    $(".chkpage").attr('checked', false);
				$(".chkpost").attr('checked', false);
			   $("input[name='chkcat[]']:checked").each(function(i) {
				 count++;
				});
				
				if(count==0)
				{
				  alert('Please select a category before saving the Campaign');
				  return false;
				}
			 }
			 else
			 {
			   $(".chkpage").attr('checked', false);
			  $(".chkcat").attr('checked', false);
			  $(".chkpost").attr('checked', false);
			   
			 }
			 
			 
			 
			 
			 $('#tspin').html('<img src="'+ my_url.purl + 'images/ajax.gif" />');
			  
		   $("form#form1").submit();
		 
	});
 
     $(document).on('click propertychange keyup input cut paste','.mtxt, .cimage, .icon, .ltxt, .lurl,.linkColor,.textColor,.bgColor',function(){
	    var id=$(this).attr('id').replace(/[a-zA-Z0-9]+_/g, '');  
		
		update_infobar(id);
	 });
	 
	 function update_infobar(id)
	 {
	 
	   var txt=$('#mtext_'+id).val();
		
		if($('#typelink_'+id).is(':checked')) 
		{ 
		   var ltxt=$('#ltext_'+id).val();
		   /*alert($('#infobar_link'+id).height());
		   alert($('#infobar_'+id).height());
		   if($('#infobar_'+id).height() > 32)
		   {
		     alert($('#infobar_'+id).height());
		   } */
		}
		else if($('#typeimage_'+id).is(':checked')) 
		{
		  
		  var text=$('#ltext_'+id).val();
		  var ltxt=text + '<img style="vertical-align:middle;margin-left:5px;" src="'+$("#cimage_"+id).val()+'">';
		  /*alert($('#infobar_link'+id+'> img').height());
		  alert($('#infobar_'+id).height());
		  if($('#infobar_'+id).height() > $('#infobar_link'+id+'> img').height())
		   {
		     alert($('#infobar_'+id).height());
		   } */
		}
		else
		{
		  var text=$('#ltext_'+id).val();
		  var img= $('input[name="icon['+id+']"]:checked').val();
		  
		  var ltxt= text + '<img style="vertical-align:middle;margin-left:5px;" src="'+ my_url.purl +'icons/' + img + '.png">';
		}
		
		var lurl=$('#lurl_'+id).val();
		var bg=$('#bgColor_'+id).val();
		var tc=$('#textColor_'+id).val();
		var lc=$('#linkColor_'+id).val();
		//alert(bg);
		var bar='<div id="infobar_'+id+'" class="infobar" style="background-image: url(\''+ my_url.purl +'images/transparent.png\');background-repeat: repeat-x;background-color:'+bg+';color:'+tc+';font-size: 110%;font-family: Georgia,Verdana,Geneva;border-bottom: 4px solid #FFFFFF;box-shadow: 0 1px 5px 0 rgba(0, 0, 0, 0.5);padding: 0px; text-align:center;width: 100%;font-weight: normal;font-style: italic;min-height: 30px;line-height: 30px;margin: 0;overflow: visible;padding:0px 10px 5px;"><span id="txt_'+id+'">'+txt+'</span><a href="'+lurl+'" style="color:'+lc+';vertical-align:baseline;padding:0 5px;text-decoration:underline;font-size: 110%;font-family: Georgia,Verdana,Geneva;font-weight: normal;font-style: italic;" id="infobar_link'+id+'">'+ltxt+'</a><img src="'+ my_url.purl + 'images/close.png" height="14" width="14" style="float: right;margin-top: 10px;"></div>';
		$("#preview_"+id).html(bar);
	 }
	 
	 
	 
		 $(document).on({
		 click: function() { 
		
	  },
	  mouseenter: function() { 
		$(this).attr('src',my_url.purl + 'images/close_hover.png');
	  },
	  mouseleave: function () {
		 $(this).attr('src', my_url.purl + 'images/close.png');
	  }
	},'.infobar >img');
	   
      function disp_confirm(message)
	{
	  var r=confirm(message);
	  if (r==true)
		{
			return true;
		}
	  else
		{
			return false;
		}
	}
	
	function validate()
	{
	  var count=0;
	  var title= $('#infobar_title').val();
	  title=title.replace(/^\s+|\s+$/g,"");
	  var sdate= $('#infobar_start_date').val();
	  var edate= $('#infobar_end_date').val();
	  var views= $('#pageviews').val();
	  views=views.replace(/^\s+|\s+$/g,"");
	  var ctime= $('#closetime').val();
	  ctime=ctime.replace(/^\s+|\s+$/g,"");
	  
	  
	  
	  var regtitle= new RegExp("^([-a-zA-Z0-9_ ]){1,100}$");
	  var regdate= new RegExp("^([0-9/])+$");
	  var regnum= new RegExp("^([0-9])+$");
	  //validate campaign title
	  if(!regtitle.test(title))
	  {
	    $('#infobar_title').css("border-color", "#FF0000")
		count++;
	  }
	  else
	  {
	  
	    $('#infobar_title').css("border-color", "#DFDFDF")
	  }
	  //if custom date radio button selected
	  if($('#display_custom').is(':checked')) 
	    {
		  //validate start date
		  if(!regdate.test(sdate))
		  {
			$('#infobar_start_date').css("border-color", "#FF0000")
			count++;
		  }
		  else
		  {
		  
			$('#infobar_start_date').css("border-color", "#DFDFDF")
		  }
		  //validate end date
		  if(!regdate.test(edate))
		  {
			$('#infobar_end_date').css("border-color", "#FF0000")
			count++;
		  }
		  else
		  {
		  
			$('#infobar_end_date').css("border-color", "#DFDFDF")
		  }
		}
	  
	  //validate page views
	  if(!regnum.test(views))
	  {
	    $('#pageviews').css("border-color", "#FF0000")
		count++;
	  }
	  else
	  {
	  
	    $('#pageviews').css("border-color", "#DFDFDF")
	  }
	  
	  if($('#infobar_show_where').val()==='-1')
	  {
		$('#infobar_show_where').css("border-color", "#FF0000")
		count++;
	  }
	  else
	  {
		$(this).css("border-color", "#DFDFDF")
	  }
		
		
		   //validate page views
		  if(!regnum.test(ctime))
		  {
			$('#closetime').css("border-color", "#FF0000")
			count++;
		  }
		  else
		  {
		  
			$('#closetime').css("border-color", "#DFDFDF")
		  }
		
		
		if(count>0)
		{
		  return false;
		}
		else
		{
		
		  return true;
		}
	}
 })(jQuery);   