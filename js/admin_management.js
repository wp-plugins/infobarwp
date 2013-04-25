(function($){ 
   
   $('.status').on('change',function() {
          var id = $(this).attr('id').replace(/[a-zA-Z]+_/g, '');
		  $('#statustxt_'+id).html('<img src="'+ my_url.purl + 'images/ajax.gif" />');
         if ($(this).hasClass('Active')) {
           $(this).removeClass('Active').addClass('Deactivated');
            // do some Ajax here
            var data = {
               action:  'AdminAjax',
               id:       id,
               dowhat:   'deactivate'
             };
             // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
             $.post(ajaxurl, data, function(response) {
			     //alert(response);
			    if(!response || response==0)
				{
                 
				  $('#status_'+id).removeClass('Deactivated').addClass('Active');
				 $('#statustxt_'+id).html('Active');
				 $('#status_'+id).attr('checked', 'checked');
				}
				else
				{
				 $('#statustxt_'+id).html('Deactivated');
				}
             });
            
            
         }else{
           $(this).removeClass('Deactivated').addClass('Active');
           // do some Ajax here
           var data = {
              action:  'AdminAjax',
              id:       id,
              dowhat:   'activate'
            };
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            $.post(ajaxurl, data, function(response) {
			     //alert(response);
                if(!response || response==0)
				{
                
				 $('#status_'+id).removeClass('Active').addClass('Deactivated');
				 $('#statustxt_'+id).html('Deactivated');
				 $('#status_'+id).removeAttr('checked');
				 
				}
				else
				{
				   //alert(response);
				  $('#statustxt_'+id).html('Active');
				}
            });
           
         };
       });
       
		 
	$('.quick_edit').on('click',function (){
		var id= $(this).attr('id').replace(/[a-zA-Z]+_/g,'');
		
		$('.quicke').hide();
		$('.campaign').show();
		$('#campaign_'+id).hide();
		
		if($('#everyday_'+id).is(':checked')) 
			{ 
				$('.sdate').hide(); 
				
			}else
			{
				$('.sdate').show();
			}
			
		$('#quicke_'+id).show();
		return false;
	});
	
	$('.qcancel').on('click',function (){
		var id= $(this).attr('id').replace(/[a-zA-Z]+_/g,'');
		
		$('#quicke_'+id).hide();
		$('#campaign_'+id).show();
		return false;
	});
	
	var dateToday = new Date();
		$( ".start_date, .end_date" ).datepicker({
			defaultDate: dateToday,
			changeMonth: true,
			numberOfMonths: 3,
			dateFormat: 'dd/mm/yy',
			minDate: dateToday,
			onSelect: function( selectedDate ) {
			        var id= $(this).attr('id');
					
				 if ($(this).hasClass("start_date"))
				 {
						var option = "maxDate",
						instance = $( this ).data( "datepicker" ),
						date = $.datepicker.parseDate(
							instance.settings.dateFormat ||
							$.datepicker._defaults.dateFormat,
							selectedDate, instance.settings );
					$('#'+id).datepicker( "option", option, date );
				}
			},
			beforeShow: function( input,inst ) {
			  var id=$(this).attr('id').replace(/[a-zA-Z]+_/g, '');
			 if ($(this).hasClass("start_date"))
			 {
			     var selectedDate=$('#end_date_'+id).val();
			     var option = "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				$('#start_date_'+id).datepicker( "option", option, date );
			 }
			  
			}
		});
		
	$('.qupdate').on('click',function (){
	   
	   var id= $(this).attr('id').replace(/[a-zA-Z]+_/g,'');
	   if(!qvalidate(id))
	   {
	     //alert('Error!!! please check all the input fields');
		  return false;
	   }
	   $('#spinner_'+id).html('<img src="'+ my_url.purl + 'images/ajax.gif" />');
	   var title=$('#qname_'+id).val();
	   title=title.replace(/^\s+|\s+$/g,"");
	   var sdate=$('#start_date_'+id).val();
	   var edate=$('#end_date_'+id).val();
	   var views=$('#pageviews_'+id).val();
	   views=views.replace(/^\s+|\s+$/g,"");
	   var display=0;
	   var duration=1;
	   var status=$('#statustxt_'+id).text();
	   var todaydate = $.datepicker.formatDate('dd/mm/yy', new Date());
	   
	   if($('#everyday_'+id).is(':checked')) 
	    {
		  duration=0;
		  sdate='';
		  edate='';
		}
		
		if($('#display_header_'+id).is(':checked')) 
	    {
		  display=1;
		  
		}
	   
	  
	   var data = {
                 action:  'AdminAjax',
                 id:       id,
				 title: title,
				 sdate: sdate,
				 edate: edate,
				 views: views,
				 display_duration: duration,
				 display_on: display,
				 status: status,
                 dowhat:   'qupdate'
               };
             $.post(ajaxurl, data, function(response) {
                //alert('Got this from the server: ' + response);
				if(!response)
				{
				  
                 $('#spinner_'+id).html('');
				 //$('#quicke_'+id).hide();
				 //$('#campaign_'+id).show();
				 alert('Error!!! Please try again');
				}
				else
				{
				  $('#spinner_'+id).html('');
				  $('#pro_t_'+id).text(title);
				  $('#sdate_'+id).text(sdate);
				  $('#edate_'+id).text(edate);
				  
				 if((Date.parse(todaydate) <= Date.parse(edate) && status=='Expired') || ( duration==0 && status=='Expired'))
				   {
					 $('#statustxt_'+id).text('Active');
					 $('#status_'+id).removeAttr('disabled');
					 $('#status_'+id).attr('checked','checked');
					 $('#status_'+id).removeClass('Expired').addClass('Active');
				   }
				 
				  $('#quicke_'+id).hide();
				  $('#campaign_'+id).show();
				  
				}
             });
	   return false;
	});
	$('.trash').on('click',function (){
	   var id= $(this).attr('id').replace(/[a-zA-Z]+_/g,'');
	   
	   if(disp_confirm())
	   {
	     $('#tspin_'+id).html('<img src="'+ my_url.purl + 'images/ajax.gif" />');
		var data = {
                 action:  'AdminAjax',
                 id:       id,
                 dowhat:   'delete'
               };
             $.post(ajaxurl, data, function(response) {
                // alert('Got this from the server: ' + response);
				if(!response)
				{
                 alert('Error!!! please try again');
				}
				else
				{
				  $('#tspin_'+id).html('');
				  $('#campaign_'+id).fadeOut('slow');
				  
				}
	   
				});
		}
	   return false;
	});
	
	$('.clone').on('click',function (){
	   var id= $(this).attr('id').replace(/[a-zA-Z]+_/g,'');
	    $('.qclone').hide();
		$('.campaign').show();
		$('#campaign_'+id).hide();
	   $('#qclone_'+id).show();
	   return false;
	   
	});
	
	$('.pclone').on('click',function (){
		var id= $(this).attr('id').replace(/[a-zA-Z]+_/g,'');
		
		
		var type=$('#show_where_' +id).val();
		
			 if(type=='-1')
			 {
				return false;
			 }
		 
		     var list = new Array();
			 
			 if(type=='post')
			 {
			     var count=0;
			   $("input[name='chkpost"+id+"[]']:checked").each(function(i) {
				list.push($(this).val());
				count++;
				});
				
				if(count==0)
				{
				  return false;
				}
			 }
			 else if(type=='page')
			 {
			    var count=0;
			   $("input[name='chkpage"+id+"[]']:checked").each(function(i) {
				list.push($(this).val());
				count++;
				});
				
				if(count==0)
				{
				  return false;
				}
			 }
			 else if(type=='category')
			 {
			    var count=0;
			   $("input[name='chkcat"+id+"[]']:checked").each(function(i) {
				list.push($(this).val());
				count++;
				});
				
				if(count==0)
				{
				  return false;
				}
			 }
			 else
			 {
			  list.push(0);
			 }
			 //alert(JSON.stringify(list));
			 
			 $('#cspin_'+id).html('<img src="'+ my_url.purl + 'images/ajax.gif" />');
			var data = {
                 action:  'AdminAjax',
                 id:       id,
				 type: type,
				 list: list,
                 dowhat:   'clone'
               };
             $.post(ajaxurl, data, function(response) {
                // alert('Got this from the server: ' + response);
				if(!response)
				{
                 alert('Error!!! please try again');
				}
				else
				{
				  $('#cspin_'+id).html('');
				   //alert(response);
				   location.reload();
				}
	   
				});
		return false;
	});
	
	
	$('.cclone').on('click',function (){
		var id= $(this).attr('id').replace(/[a-zA-Z]+_/g,'');
		
		//alert(id);
		$('#qclone_'+id).hide();
		$('#campaign_'+id).show();
		return false;
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
	
	  $('#chkall').change(function(){
	  
        if(this.checked)
		{
		  $('.chkbox').attr('checked', 'checked');
		}
		else
		{
		  $('.chkbox').removeAttr('checked');
		}
	});
	
	$('.dispay_setting').click(function() {
	    var id=$(this).attr('id').replace(/[a-zA-Z]+_/g, '');
		     
			if($('#everyday_'+id).is(':checked')) 
			{ 
				$('.sdate').hide(); 
			}
			else
			{
				$('.sdate').show();
			}
	});
	
	function disp_confirm()
	{
	  var r=confirm("Are you sure? \nThis can't be undone.")
	  if (r==true)
		{
			return true;
		}
	  else
		{
			return false;
		}
	}
	
	function qvalidate(id)
	{
	  var count=0;
	  
	   var title=$('#qname_'+id).val();
	   title=title.replace(/^\s+|\s+$/g,"");
	   var sdate=$('#start_date_'+id).val();
	   var edate=$('#end_date_'+id).val();
	   var views=$('#pageviews_'+id).val();
	   views=views.replace(/^\s+|\s+$/g,"");
	  
	  
	  var regtitle= new RegExp("^([-a-zA-Z0-9_ ]){1,100}$");
	  var regdate= new RegExp("^([0-9/])+$");
	  var regnum= new RegExp("^([0-9])+$");
	  //validate campaign title
	  if(!regtitle.test(title))
	  {
	    $('#qname_'+id).css("border-color", "#FF0000")
		count++;
	  }
	  else
	  {
	  
	    $('#qname_'+id).css("border-color", "#DFDFDF")
	  }
	  //if custom date radio button selected
	  if($('#custom_'+id).is(':checked')) 
	    {
		  //validate start date
		  if(!regdate.test(sdate))
		  {
			$('#start_date_'+id).css("border-color", "#FF0000")
			count++;
		  }
		  else
		  {
		  
			$('#start_date_'+id).css("border-color", "#DFDFDF")
		  }
		  //validate end date
		  if(!regdate.test(edate))
		  {
			$('#end_date_'+id).css("border-color", "#FF0000")
			count++;
		  }
		  else
		  {
		  
			$('#end_date_'+id).css("border-color", "#DFDFDF")
		  }
		}
		
	  
	  //validate page views
	  if(!regdate.test(views))
	  {
	    $('#pageviews_'+id).css("border-color", "#FF0000")
		count++;
	  }
	  else
	  {
	  
	    $('#pageviews_'+id).css("border-color", "#DFDFDF")
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
	
	    $(".show_where").change(function() {
		
		var val = $(this).val();
		var id=$(this).attr('id').replace(/[a-zA-Z]+_/g, '');  
		
		if(val=='post')
		{
		  $("#showpages_" + id).hide();
		  $("#showcategories_"+ id).hide();
		  $("#showposts_" + id).show();
		}
		else if(val=='page')
		{
		  $("#showposts_" + id).hide();
		  $("#showcategories_" + id).hide();
		  $("#showpages_" + id).show();
		}
		else if(val=='category')
		{
		   $("#showposts_" + id).hide();
		  $("#showpages_" + id).hide();
		  $("#showcategories_" +id ).show();
		}
		else
		{
		 $("#showposts_" + id).hide();
		 $("#showpages_" + id).hide();
		 $("#showcategories_" + id).hide();
		}
	});
	
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
   
   
})(jQuery);