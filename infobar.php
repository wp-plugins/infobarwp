<?php

/*
Plugin Name: InfobarWP 
Plugin URI: http://infobarwp.com
Description: Create fully customizable notification bars, rotate different variants and target specific pages and posts on your website.
Version: 1.0
Author: YMB Properties
Author URI: http://ymbproperties.com
License: GPLv2 

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

   

  $plugin_loc         = plugin_dir_url( __FILE__ );

  $plugname           = "Infobar";

  define( P_URL,plugin_dir_url(__FILE__) );

  class wpinfobar

{      
    // start Construct
	function wpinfobar()

	{

	 // Always set wpdb globally!

		global $wpdb;

	  // Menu

	      add_action('admin_init', array( $this, 'infobar_admin_init' ) );

		  add_action('admin_menu',  array( $this,'infobar_menu'  ) );

	  // add Ajax call

	  if(is_admin())

	  {

		add_action('wp_ajax_AdminAjax', array($this,'infobar_ajax'));

	  }

	  
      // call initializing script
	  add_action('template_redirect', array( $this, 'infobar_frontend_init' ) );
	 // add Ajax call
	  add_action('wp_ajax_nopriv_infobar_initialize', array($this,'initialize'));
	
	  add_action('wp_ajax_infobar_initialize', array($this,'initialize'));
	  
	  add_action('wp_ajax_nopriv_infobar_addimp', array($this,'infobar_addimp'));
	  
	  add_action('wp_ajax_infobar_addimp', array($this,'infobar_addimp'));
		  

	} // End Construct

	

	// = Front End

	//function to initialize plugin and get infobar data
    // this function is called from initialize.js using ajax
	function initialize()

	{

	       global $wpdb, $wp_query;

		     $id=0;

			/*if($this->check_mobile())

			{

			  die();

			} */

			

			$wp = new WP();

			$href = isset($_REQUEST['href']) ? explode('?', $_REQUEST['href']) : '';

			$uri = str_replace('https://', '', str_replace('http://', '', $href[0]));

			$uri = substr($uri, strpos($uri, '/'));

			$_SERVER['REQUEST_URI'] = $uri;

			$query = isset($href[1]) ? $href[1] : ''; 

			if($query!='')

			{

			  $preview= substr($query,0, strpos($query, '='));

			  if($preview=='preview')

			  {

			    $id= substr($query,strpos($query, '=')+1);

			  }

			}

			$_SERVER['PHP_SELF'] = 'index.php';

			$wp->main($query);

			$wp_query->parse_query();

			$bar=$this->show_infobar($id,$_REQUEST['type']);

			

			if(!$bar)

			{

			  die();

			}

			$js = file_get_contents('js/infobar.js', true);

			$bar = "var bar=".json_encode($bar).";";

			$js = $bar . ' ' . $js;

			ob_clean();

			header( "Content-type: text/javascript" );

			die($js);

	}
	
    // function to add scrips to front end and track clicks

	function infobar_frontend_init()
	{
	   if(!is_admin())
	  {
		
		wp_register_script('initJs', P_URL.'js/initialize.js', array('jquery'));

		wp_enqueue_script( 'initJs' );

		wp_localize_script( 'initJs', 'my_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		
        wp_register_style( 'infobarsheet', P_URL . 'css/infobar.css', false);
		
		wp_enqueue_style( 'infobarsheet' );

		if (is_home() || is_front_page() ) 

			{

			   wp_localize_script( 'initJs', 'page', array( 'type' => 'home' ) );

			}

			else if(is_page())

			{

			  wp_localize_script( 'initJs', 'page', array( 'type' => 'page' ) );

			}

			elseif (is_single())

			{

			  wp_localize_script( 'initJs', 'page', array( 'type' => 'post' ) );

			}

			elseif(is_category())

			{

			  wp_localize_script( 'initJs', 'page', array( 'type' => 'category' ) );

			}

			else

			{

			  wp_localize_script( 'initJs', 'page', array( 'type' => 'error' ) );

			}


		wp_register_script('jsonJs', P_URL.'js/json2.js');

		wp_enqueue_script( 'jsonJs' );

	  }

	}
	
	
	// function to add impressions
	function infobar_addimp()
	{

	   $barid=mysql_real_escape_string($_POST['barid']);

	   $session=mysql_real_escape_string($_POST['session']);

		 if($session==0 || $session==1){ $this->update_session($barid,$session);}

		 echo json_encode($responseVar);

	     die();
	}
	
	// function get color schemes for infobar
	
	function colorschemes()

	{

	  $schemes = array();

	  $schemes[] = 'Plain Gray';$schemes[] ='Browser Style';$schemes[] ='Hot Sun';$schemes[] ='Chrome Blue';$schemes[] ='Michael Jackson';

	  return $schemes;

	

	}

        

     // this function is used to get infobar data for displaying on frontend

	function show_infobar($pid,$type)

	{
		global $post;
        $preview_infobar=0;
		
		// if preview page
		if($pid)

		{
          $preview_infobar=1;
		  $sql = "SELECT * FROM infobar_campaigns a inner join infobar_variants b on b.int_title_id=a.int_title_id where a.int_title_id=".$pid." and b.int_active=1 ORDER BY b.int_message_id ASC";

			 $data =  mysql_query($sql);

		     $count_max=mysql_num_rows ($data);

		} 

		else

		{
			$postid=$post->ID; 

			  $xtra='';
			  $ex=0;
			if ($type=='home') 

			{

			   //echo "is_home<br/>";

				$xtra .= " AND (a.show_where = 'all' or a.show_where='home')";

				$postid=0;
				$ex=0;

			}

			elseif($type=='page')

			{

			  //echo "is_page<br/>";
    
			  $xtra .= " AND (a.show_where = 'all' or a.show_where='inner' or (a.show_where='page' and c.link_id=$postid))";
			  $ex_xtra .=" AND (b.type='page' and b.link_id=$postid)";
			  $ex=1;
              
			}

			elseif ($type=='post')

			{

			   //echo "is_sigle<br/>";

			   $categories = get_the_category($postid);

			

				foreach($categories as $category)

				{

					

					$cat=$category->cat_ID;

					$parentCatList = get_category_parents($cat,false,',');

					$parentCatListArray = split(",",$parentCatList);

					$topParentName = $parentCatListArray[0];

					$cat_ids[]=get_cat_ID($topParentName);

				}

				$cat_ids=array_unique($cat_ids);

				

				$xtra .= " AND (a.show_where = 'all' or a.show_where='inner' or (a.show_where='category' and c.link_id in (".implode(',', $cat_ids).")) or (a.show_where='post' and c.link_id=$postid))";
				
				$ex_xtra .=" AND ((b.type='category' and b.link_id in (".implode(',', $cat_ids).")) OR (b.type='post' and b.link_id=$postid))";
				$ex=1;
			}

			elseif($type=='category')

			{

				//echo "is_category<br/>";

				$cat = get_query_var('cat');

				$parentCatList = get_category_parents($cat,false,',');

				$parentCatListArray = split(",",$parentCatList);

				$topParentName = $parentCatListArray[0];

				$cat_id=get_cat_ID($topParentName);

				$xtra .= " AND (a.show_where = 'all' or a.show_where='inner' or (a.show_where='category' and c.link_id=".$cat_id."))";
				$ex_xtra .=" AND  (b.type='category' and b.link_id=".$cat_id.")";
				$ex=1;
			}

			else

			{

			  //echo "is_else<br/>";
              $ex=0;
			 return false;

			}


			//$xtra .= " GROUP BY a.int_title_id, b.int_message_id ORDER BY a.int_title_id DESC, b.int_message_id ASC";

			$xtra .= " GROUP BY a.int_title_id ORDER BY a.last_shown ASC,a.int_title_id DESC";
			
			//$where     =  "WHERE a.status = 1 and b.int_active=1 and ((a.start_date < now() and a.end_date > now()) or a.duration=0) ";

	        $where     =  "WHERE a.status = 1 and ((a.start_date < now() and a.end_date > now()) or a.duration=0) ";


			//$sql = "SELECT * FROM infobar_campaigns a inner join infobar_variants b on b.int_title_id=a.int_title_id inner join infobar_link_pages c on c.campaign_id=a.int_title_id ".$where .$xtra;
			
			$sql = "SELECT * FROM infobar_campaigns a inner join infobar_link_pages c on c.campaign_id=a.int_title_id ".$where .$xtra;

			 $data =  mysql_query($sql);

		 $count_max=mysql_num_rows ($data);

		}
        // if infobar campaings exits for this page
		if($count_max!=0)

		{
			$count=0;
			
			while($result1=mysql_fetch_assoc($data))
			{
				$title_id=$result1[int_title_id];
				
				$tracking=$result1[tracking];

				$imps=$result1[track_imps];
				//mysql_data_seek($data,0);
				
				//check if this page is excluded 
				if($ex && $preview_infobar!=1)
				{
					$ex_where     =  " WHERE a.int_title_id=".$title_id;
					$sql = "SELECT b.link_id as id FROM infobar_campaigns a inner join infobar_exclude_pages b on b.campaign_id=a.int_title_id".$ex_where .$ex_xtra;
					$result=mysql_query($sql);
					
					 if(!mysql_num_rows($result))
					 {
					   $count=1;
						break 1;
					 }
				}
				else
				{
				  $count=1;
				  break 1;
				}
			}
			
			$sql1="update infobar_campaigns set last_shown=null where int_title_id=$title_id";
			  mysql_query($sql1);
			  
			if(!$count)
			{
			  return false;
			}
            //get campaign session id
           $key1="SELECT int_session_id from infobar_session where int_title_id=$title_id";

                   $key1= mysql_query($key1);

                   //print_r($key1);

              $count=mysql_num_rows($key1);

			  $key1=mysql_fetch_assoc($key1);

             //if session id does not exists
		    if($count==0)
            {
			        //echo "no records";

					$data_sql4="INSERT INTO `infobar_session` (`int_session_id`,`int_title_id`) VALUES(1,$title_id)";

					mysql_query($data_sql4);

					$session_id=1;
			}
			else
			{
			     $session_id=$key1[int_session_id];

			}

            // get infobar data
            $data=$this->infobar_data($session_id,$title_id,$preview_infobar);

			 //var_dump($data); die;

            if(!$data) // check of data 

            { 
               return false;
            }

			else

			{ 
			    // get campaing data
				$show_where = $data['show_where'];

				$start_date = $this->convert_dates($data['start_date']);

				$end_date   = $this->convert_dates($data['end_date']);

				$int_title_id=   $data['int_title_id'] ;

				$infobar_id         =  $data['int_title_id'];

				$infobar_imp         =  $data['imp'];

				$infobar_ctime		=  $data['closetime'];

				$infobar_cookie     =  $data['cookie_name'];

				$infobar_display_on  =  $data['display_on'];
				
				$link_target  =  $data['link_target'];

				$duration       =  $data['duration'];

				$infobar_variants    =  $data['str_message'];

				$infobar_variants_id	=  $data['int_message_id'];

				$infobar_link_type  =  $data['link_type'];

				$infobar_link  =  $data['str_link_text'];

				$infobar_link_img   =  $data['str_link_img'];

				$infobar_link_url   =  $data['str_link_url'];

				$infobar_session       = $data['session'];

				$infobar_options    =  json_decode($data['str_options'],true);

				

				$infobar_bgColor    =  $infobar_options['bgColor'];

				$infobar_textColor  =  $infobar_options['textColor'];

				$infobar_linkColor  =  $infobar_options['linkColor'];


                 // if custom image seleced
				if($infobar_link_type==0)

				{

				

				   $infobar_link_text= $infobar_link.'<img src="'.$infobar_link_img.'" style="margin: 5px;vertical-align: middle;display:inline;">';

				}

				else

				{
				
				  $infobar_link_text=$infobar_link;

				}

				$infobar_style  = "background-image: url('".P_URL."images/transparent.png');background-repeat: repeat-x;";
				
				$infobar_style  .= "background-color: $infobar_bgColor;";
				
				$infobar_style .= "color: $infobar_textColor;";

				$infobar_style .= "border-bottom: 4px solid #FFFFFF;";

				$infobar_style .= "box-shadow: 0 1px 5px 0 rgba(0, 0, 0, 0.5);";

				$infobar_style .= "padding: 0px; text-align:center;";

				$infobar_style .= "width: 100%;";

				$infobar_style .= "min-height: 30px;line-height: 30px;";

				$infobar_style .= "margin: 0;overflow: visible;position: fixed;";

				 	

					if($infobar_display_on)

					{

					  $infobar_style .= "left:0;top:0;"; 

					}

					else

					{

					  $infobar_style .= "left:0;bottom:0;"; 

					}
					
				if($link_target){$target='target="_blank"';}else{$target='';}

				$infobar_link_style = "vertical-align: baseline;text-decoration: underline;font-style: italic; font-size: 110%;font-weight: normal;font-family: Georgia,Verdana,Geneva;color: ".$infobar_linkColor.";";
				   
				$infobar_b_html  = '<div style="width:100%;position:relative;z-index: 2147483647;" class="infobar" ><div id="infobar_'.$infobar_id.'_'.$infobar_variants_id.'"  class="info_bar" style="'.$infobar_style.'">';

				$infobar_b_html .= '<div class="infobar_inner"><span id="infobar_txt" style="vertical-align: baseline;font-style: italic;font-weight:normal;font-size: 110%;font-family: Georgia,Verdana,Geneva;color: '.$infobar_textColor.';">'.$infobar_variants.'</span>&nbsp;';

				$infobar_b_html .= '<a id="infobar_link" style="'.$infobar_link_style.'" href="'.$infobar_link_url.'" '.$target.'>'.$infobar_link_text.'</a></div>';

				$infobar_b_html .= '<img src="'.P_URL.'images/close.png" style="position: absolute;right: 8px;top: 10px;"></div></div>';

				$infobar['id']=$infobar_id;

				$infobar['mid']=$infobar_variants_id;

				$infobar['settings']['imp']=$infobar_imp;

				$infobar['settings']['closetime']=$infobar_ctime;

				$infobar['settings']['cookie']=$infobar_cookie.'_'.$infobar_id;

				$infobar['settings']['display_on']=$infobar_display_on;

				$infobar['settings']['home']=home_url();

				$infobar['settings']['purl']=P_URL;

				$infobar['settings']['session']=$infobar_session;
				$infobar['settings']['preview']=$preview_infobar;
				$infobar['settings']['target']=$link_target;
				

				//$infobar['settings']['type']=$type;

				if($infobar_link_type==0)

				{

				  $infobar['images']['button']=$infobar_link_img;

				}

                $infobar['images']['close']=P_URL.'images/close.png';

				$infobar['images']['close_hover']=P_URL.'images/close_hover.png';

				$infobar['div']=$infobar_b_html;

				//echo($infobar_b_html);

				return $infobar;

				//return true;

			}    

        }

        return false;		

    } // End fun infobar_show()

      

        

        // Register javascript and stylesheet for admin

	function infobar_admin_init()

	{

			   //wp_enqueue_script( 'jquery' );

			   //wp_register_script( 'jqueryui', P_URL.'js/jquery-ui-1.10.1.js');

			   //wp_enqueue_script( 'jqueryui' );

			    wp_register_script('jquery-ui-datepicker', P_URL.'js/ui.datepicker.js', array('jquery','jquery-ui-core'),false,true);

			    wp_register_script('AdminPJs', P_URL.'js/admin_campaign.js', array('jquery'),false,true);

				wp_localize_script( 'AdminPJs', 'my_url', array( 'purl' => P_URL ) );

				wp_register_script('s-colorpicker', P_URL.'js/colorpicker.js', array('jquery'),false,true);

				wp_register_script('AdminMJs', P_URL.'js/admin_management.js', array('jquery'),false,true);

				wp_localize_script( 'AdminMJs', 'my_url', array( 'purl' => P_URL ) );

				wp_register_style( 's-colorpicker', P_URL . 'css/colorpicker.css', false);                    

				wp_register_style( 'jquery-ui-datepicker', P_URL . 'css/ui.datepicker.css');

				wp_register_style( 'infobarStylesheet', P_URL . 'css/stylesheet.css', false);

								  

		$page  = $_GET['page'];

		

			if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'infobar_campaign')

		{

		          wp_enqueue_script('jquery-ui-datepicker');

		          wp_enqueue_script('AdminPJs');

				  wp_enqueue_script('s-colorpicker');

				  //wp_enqueue_style('myUicss','http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css');

				  wp_enqueue_style('s-colorpicker');

				  wp_enqueue_style('jquery-ui-datepicker');

				  wp_enqueue_style( 'infobarStylesheet' );

		}

			if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'infobar_management') {

			      wp_enqueue_script('jquery-ui-datepicker');

				  wp_enqueue_script('AdminMJs');

				  wp_enqueue_style('jquery-ui-datepicker');

				  wp_enqueue_style( 'infobarStylesheet' );

			}

	

	}       

        //function to Add menu to admin page

	function infobar_menu()

	{

	    $myIcon =P_URL.'images/logoicon.png';

		

		add_menu_page('Campaign Management Page','Infobars', 'administrator','infobar_management','',$myIcon);



		add_submenu_page('infobar_management','Campaign Management Page','All Infobars','administrator','infobar_management',array($this,'infobar_management'));

		

		add_submenu_page('infobar_management','Add/Edit Campaign','Add New', 'administrator','infobar_campaign',array($this,'infobar_campaign'));

	}

	  // this function is for displaying Campaign Management Page in admin

	function infobar_management()

	{

	  global $wpdb;

	    //do bulk actions

			   

		  $this->disable_expired();

	    if(isset($_POST['bulk']) && $_POST['bulk_action']!=-1)

		{

		    //var_dump($_POST);

		   //bulk activate/deactivate selected campaigns

		  if($_POST['bulk_action']=='pdeactivate')

		  {

		      

			 $active = 0;

				//loop through all the selected campaigns

		    foreach($_POST['id'] as $id)

			{

			    //update status

			  $sql = "UPDATE infobar_campaigns SET `status`=$active WHERE int_title_id=$id";

			  mysql_query($sql);

			}

		  }

		  else if($_POST['bulk_action']=='pactivate')

		  {

		     $active = 1;

		     foreach($_POST['id'] as $id)

			{

			    //update status

					$sql = "UPDATE infobar_campaigns SET `status`=$active WHERE int_title_id=$id";

					mysql_query($sql);

			}

		  }

		  // bulk delete selected campaigns

		  else if($_POST['bulk_action']=='trash')

		  {

		     //loop through all the selected campaigns

		     foreach($_POST['id'] as $id)

			{

			    //delete selected campaigns

			   $this->delete_campaign($id);

			}

		  

		  }

		}

		

		//filter campaigns

	    if(isset($_POST['filter']) && ($_POST['filter_date']!=-1 || $_POST['filter_type']!=-1))

		{

			 // var_dump($_POST);

			  $where='';

			  $orderby= 'Order by int_title_id DESC';

			  if($_POST['filter_date']=='current')

			  {

			     

				 $where.='where YEAR( end_date ) = YEAR( CURDATE( ) ) AND MONTH( end_date ) = MONTH( CURDATE( ) ) ';

			  }

			  else if($_POST['filter_date']=='last1')

			  {

				$where.="where YEAR(end_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)AND MONTH(end_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) ";

			  }

			  else if($_POST['filter_date']=='last3')

			  {

				$where.='where end_date >= date_sub(NOW(), INTERVAL 3 MONTH) ';

			  }

			  else if($_POST['filter_date']=='last6')

			  {

				$where.='where end_date >= date_sub(NOW(), INTERVAL 6 MONTH) ';

			  }

			  else if($_POST['filter_date']=='1year')

			  {

				$where.='where end_date >= date_sub(NOW(), INTERVAL 1 YEAR) ';

			  }

			  else

			  {

				$where.='where ';

			  }

			  

			  if($_POST['filter_type']!=-1 && $_POST['filter_date']!=-1)

			  {

			    $where.='AND ';

			  }

			  

			  if($_POST['filter_type']=='cat')

			  {

				 $where.='show_where="category"';

			  }

			  else if($_POST['filter_type']=='pages')

			  {

				$where.='show_where="page"';

			  }

			  else if($_POST['filter_type']=='posts')

			  {

				$where.='show_where="post"';

			  }

			  else if($_POST['filter_type']=='active')

			  {

				$where.='status=1';

			  }

			  else if($_POST['filter_type']=='deactivated')

			  {

				$where.='status=0 ';

			  }

			  else if($_POST['filter_type']=='expired')

			  {

				$where.='end_date < NOW()';

			  }

			  else

			  {

			    $where.='';

			  }

			  

		    $sql  =  "SELECT * FROM infobar_campaigns ".$where." ".$orderby."";

		}

		else

		{

		  $sql  =  "SELECT * FROM infobar_campaigns ORDER BY int_title_id DESC";

		}

		

		//$sql  =  "SELECT int_title_id , str_title, start_date, end_date, show_where, status FROM infobar_campaigns ORDER BY int_title_id DESC";

	      $result         =   mysql_query($sql); 

		

		

		if (!current_user_can('manage_options'))  {

			wp_die( __('You do not have sufficient permissions to access this page.') );

		}

		echo '<div class="wrap">';

       echo '<div class="icon32" id="icon-infobar"><br></div>';

		echo '<h2>'.__('All infobars').'<a class="add-new-h2" href="'.$_SERVER['PHP_SELF'].'?page=infobar_campaign">Add New</a></h2>';

	 

	  if(!$result)

	  {

	    echo '<p>You have no Campaigns add one now</p>';

	  }

	  else

	  {

	    echo '<form method="post" action="" >';

	    echo '<div class="tablenav top">';

	    echo '<div class="alignleft actions">';

	    echo '<select name="bulk_action">';

		echo '<option selected="selected" value="-1">Bulk Actions</option>';

		echo '<option class="changestatus" value="pactivate">Activate</option>';

		echo '<option class="changestatus" value="pdeactivate">Deactivate</option>';

		echo '<option value="trash">Trash</option>';

		echo '</select>';

		echo '<input type="submit" value="Apply" class="button action" id="doaction" name="bulk">';

		echo '</div>';

		echo '<div class="alignleft actions">';

		echo '<select name="filter_date">';

		echo '<option value="-1" selected="selected">Show all dates</option>';

		echo '<option value="current">Current month</option>';

		echo '<option value="last1">Last Month</option>';

		echo '<option value="last3">Last 3 Months</option>';

		echo '<option value="last6">Last 6 Months</option>';

		echo '<option value="1year">Last 1 year</option>';

		echo '</select>';

		echo '<select class="postform" name="filter_type">';

		echo '<option value="-1" selected="selected" >All</option>';

		echo '<option value="cat">categories</option>';

		echo '<option value="posts" class="level-0">Posts</option>';

		echo '<option value="pages" class="level-0">Pages</option>';

		echo '<option value="active" class="level-0">Active</option>';

		echo '<option value="deactivated" class="level-0">Deactivated</option>';

		echo '<option value="expired" class="level-0">Expired</option>';

		echo '</select>';

		echo '<input type="submit" value="Filter" class="button" id="post-query-submit" name="filter">';	

	    echo '</div>';

		echo '<br class="clear"></div>';

		 echo '<table border="0" cellpadding="0" cellspacing="0" class="widefat">';

		  echo '<thead>

								<tr>                           

									 <th class="check-column"><input type="checkbox" name="chkall" id="chkall" value="1"></th>

									<th>'  .__("Campaign Name"). '</th>

								   <th>'  .__(" Start Date").     '</th> 

									<th>'  .__("End Date").     '</th> 								   

								   <th>'  .__("Location").  '</th>           

								   <th>'  .__("Status").    '</th>          

								 </tr>

								 </thead><tbody>';

			$count=1;

		while ( $row = mysql_fetch_assoc($result) )

		  {

		      

				$id             =   $row['int_title_id'];		   

				$title          =   $row['str_title'];

				$start_date     =   $this->convert_dates($row['start_date']);                      

				$end_date       =   $this->convert_dates($row['end_date']);

				$show_where     =   $row['show_where'];    

				$active         =   $row['status'];

				$duration       =     $row['duration'];

			    $show_where     =     $row['show_where'];

			    $display_on     =     $row['display_on'];

			    $status         =     $row['status'];

			    $imp            =     $row['imp'];

			  if($active == 1 ){

				$checked = 'checked="checked"';

				$active_title = __("Active");

			  }

			  elseif($active == 0){

				$checked= "";

				$active_title = __("Deactivated");

			  }

			  else

				{

				 $active_title = __("Expired");

				 $checked= 'disabled="disabled"';

				}

			    if($show_where=='inner'){$show_where='inner pages';}

				

				

				echo '<tr valign="top" class="campaign" id="campaign_'.$id.'">';

				echo '<th class="check-column" scope="row" style="width: 5%;white-space: nowrap;"><input type="checkbox" name="id[]" class="chkbox" value="'.$id.'"></th>';

			    echo '<td style="width: 35%;white-space: nowrap;"><a href="admin.php?page=infobar_campaign&amp;title_id='.$id.'" style="text-decoration:none;" id="pro_t_'.$id.'">'.$title.'</a><p style="text-decoration:none;"><a href="admin.php?page=infobar_campaign&amp;title_id='.$id.'" style="text-decoration:none;" >Edit |</a><a href="#" style="text-decoration:none;" class="quick_edit" id="quickedit_'.$id.'"> Quick Edit |</a><a href="'.get_site_url().'?preview='.$id.'" style="text-decoration:none;" target="_blank"> Preview |</a><a href="#" class="trash" id="trash_'.$id.'" style="text-decoration:none;" > Trash |</a><a href="#" style="text-decoration:none;" class="clone" id="clone_'.$id.'"> Clone</a><span id="tspin_'.$id.'"></span></p></td>';

				echo '<td style="width: 15%;white-space: nowrap;"><span id="sdate_'.$id.'">'.$start_date.'</span></td>';

				echo '<td style="width: 15%;white-space: nowrap;"><span id="edate_'.$id.'">'. $end_date.'</span></td>';

				echo '<td style="width: 15%;white-space: nowrap;">Display on '.$show_where.'</td>';

				echo '<td style="width: 15%;white-space: nowrap;"><input type="checkbox" class="status '.$active_title.'" id="status_'.$id.'" name="status[]" value="'.$id.'" '.$checked.'> <span id="statustxt_'.$id.'">'.$active_title.'</span></td>';

			    echo  "</tr>";

				

				$show_where_selected  = '<option value="-1" selected >select</option>';

				$show_where_selected  .= '<option value="home" >HomePage</option>';

				$show_where_selected .= '<option value="inner" >Inner Pages</option>';

				$show_where_selected .= '<option value="all" >All Pages/Posts</option>';

				$show_where_selected .= '<option value="category" >Category</option>';

				$show_where_selected .= '<option value="page" >Page</option>';

				$show_where_selected .= '<option value="post" >Post</option>';

				

				echo '<tr style="display:none" class="inline-edit-row qclone" id="qclone_'.$id.'"><td class="colspanchange" colspan="6">';

				

				echo '<fieldset style="width:10%"><div class="inline-edit-col">';

				echo '<h4>Clone Campaign</h4>';

				echo '</div></fieldset>';

				

				echo '<fieldset style="width:30%"><div class="inline-edit-col">';

				echo '<label>

					<span class="title1" >Show on</span>

					<span class="input-text-wrap1"><select name="show_where" class="show_where" id="show_where_'.$id.'" >

					'.$show_where_selected.'</select></span></label>';

				echo '</div></fieldset>';

				

				echo '<fieldset id="showpages_'.$id.'" style="display:none;width:60%"><div class="inline-edit-col">';

				echo '<span class="input-text-wrap1">';

				echo $this->get_page_list(array(  ),$id);

				echo '</span></div></fieldset>';

				

				echo '<fieldset id="showposts_'.$id.'" style="display:none;width:60%"><div class="inline-edit-col">';

				echo '<span class="input-text-wrap1">';

				echo $this->get_post_list(array(  ),$id);

				echo '</span></div></fieldset>';

				

				echo '<fieldset id="showcategories_'.$id.'" style="display:none;width:60%"><div class="inline-edit-col">';

				echo '<span class="input-text-wrap1">';

				echo $this->get_category_list(array(  ),$id);

				echo '</span></div></fieldset>';

				

				echo '<p class="submit inline-edit-save">

					<a class="button-secondary cancel alignleft cclone" title="Cancel" href="#" id="cclone_'.$id.'">Cancel</a>

					<a class="button-primary save alignright pclone" title="Update" href="#" id="pclone_'.$id.'">Clone</a>

						<span class="alignright" id="cspin_'.$id.'"></span>

					<br class="clear"></p>';

					

				echo '</td></tr>';

				

				

				echo '<tr style="display:none" class="inline-edit-row quicke" id="quicke_'.$id.'"><td class="colspanchange" colspan="6">';

				

				echo '<fieldset style="width:10%"><div class="inline-edit-col">';

				echo '<h4>Quick Edit</h4>';

				echo '</div></fieldset>';

					$header='';

					$footer='';

					 if($display_on)

					 {

					   $header='checked="checked"';

					   $footer='';

					 }

					 else

					 {

					   $footer='checked="checked"';

					   $header='';

					 }

				

				echo '<fieldset style="width:30%"><div class="inline-edit-col">';

				echo '<label>

					<span class="title1" >Title</span></label>

					<span class="input-text-wrap1"><input type="text" value="'.$title.'" class="ptitle" id="qname_'.$id.'" name="campaign_title"></span>';

				echo '<label>

					<span class="title1">Show on</span><br/></label>

					<span class="input-text-wrap1">

					<label for="display_header"><input type="radio" value="1" id="display_header_'.$id.'" name="display_on['.$id.']" '.$header.' >&nbsp;&nbsp;&nbsp;header</label>

					<label for="display_footer"><input type="radio" value="0" id="display_footer_'.$id.'" name="display_on['.$id.']" '.$footer.' >&nbsp;&nbsp;&nbsp;footer</label></span>';

				echo '</div></fieldset>';

				

					if($duration)

					 {

					   $custom='checked="checked"';

					   $everyday='';

					   $style='';

					 }

					 else

					 {

					   

					   $everyday='checked="checked"';

					   $custom='';

					   $style='style="display:none;"';

					 }

					 

				echo '<fieldset style="width:30%"><div class="inline-edit-col">';

                echo '<label>

					<span class="title1">Date settings</span></label>

					<br/>

					<span class="input-text-wrap1">

					<label for="display_everyday"><input type="radio" value="0" class="dispay_setting" id="everyday_'.$id.'" name="display_duration['.$id.']" '.$everyday.' >&nbsp;&nbsp;&nbsp;everyday</label>

					<label for="display_custom"><input type="radio" value="1" class="dispay_setting" id="custom_'.$id.'" name="display_duration['.$id.']"  '.$custom.' >&nbsp;&nbsp;&nbsp;custom</label></span>';

				echo '<label '.$style.' class="sdate">

					<span class="title1">Start Date</span>

					<span class="input-text-wrap1"><input type="text" value="'.$start_date.'" class="start_date" id="start_date_'.$id.'"   name="start_date" readonly ></span></label>';

				echo '<label '.$style.' class="sdate">

					<span class="title1">End Date</span>

					<span class="input-text-wrap1"><input type="text" value="'. $end_date.'" id="end_date_'.$id.'" class="end_date" name="end_date" readonly ></span></label>';

				echo '</div></fieldset>';

				

				echo '<fieldset style="width:30%"><div class="inline-edit-col">';

				echo '<label>

					<span class="title1">Page views</span></label>

					<span class="input-text-wrap1"><input type="text" class="small-text" name="pageviews" value="'.$imp.'" id="pageviews_'.$id.'" ></span>';

				echo '</div></fieldset>';

				

				echo '<p class="submit inline-edit-save">

					<a class="button-secondary cancel alignleft qcancel" title="Cancel" href="#" id="cancel_'.$id.'">Cancel</a>

					<a class="button-primary save alignright qupdate" title="Update" href="#" id="update_'.$id.'">Update</a>

						<span class="alignright" id="spinner_'.$id.'"></span>

					<br class="clear"></p>';

				echo '</td></tr>';

		    $count++;

		  }

		  echo "</tbody></table>";

		echo '</form>';

	  }

	    

      echo '</div>';

	}

        

        //function for adding  New & editing campaigns in Admin

	function infobar_campaign()

	{

	

	  if (!current_user_can('manage_options'))  {

			wp_die( __('You do not have sufficient permissions to access this page.') );

		}

	   echo '<div class="wrap" id="add_new_infobar_page">';

	   if(isset($_GET['title_id']))

	   {

	     $title="Edit Campaign";

	   }

	   else

	   {

	     $title="Add New Campaign";

	   }

		echo '<div class="icon32" id="icon-infobar"><br></div><h2>'.$title.'</h2>';

		if (!isset($_POST['title_id']) && $_POST['save']=="Save")

	  {

	  //echo "if". $_POST['title_id'];

		if (!$this->update_campaigns()) {

		  echo '<div class="updated settings-error" id="setting-error-settings_updated"> 

						<p><strong>!!!Error please try Again</a></strong></p></div>';

		}

		else

		{

				echo '<div class="updated settings-error" id="setting-error-settings_updated"> 

						<p><strong>New campaign created: add a new one below or return to the

				  <a href="admin.php?page=infobar_management">campaign management page.</a></strong></p></div>';

		}

	  }

	  elseif( isset($_POST['title_id']) && $_POST['save']=="Save"){

		

		if (!$this->update_campaigns()) 

		{

		  echo '<div class="updated settings-error" id="setting-error-settings_updated"> 

						<p><strong>Update Failed! Try again</a></strong></p></div>';

		}

		else

		{

				  echo '<div class="updated settings-error" id="setting-error-settings_updated"> 

						<p><strong>Your campaign is live and settings have been saved. Continue editiing, or return to the

				  <a href="admin.php?page=infobar_management">campaign management page.</a></strong></p></div>';
    
		}

		

	  }

	  if( isset($_GET['title_id']) ) {

		

		$title_id   = $_GET['title_id'];

		$p_data  = $this->get_campaign($title_id);

		$p_id     = "&title_id=$title_id";

		if(!$p_data) wp_die("NO DATA");

		

	  }

	  else

	  {

	    $title_id=null;

	  }

		  

	  echo('<form id="form1" action="'.$_SERVER['PHP_SELF'].'?page=infobar_campaign'.$p_id.'" method="post" > ');

	 

	  if(!isset($_GET['title_id']) )

	  {     

		

	   // defaults for for Form

		 $str_title         =        __("Campaign Name");

		 $str_message     =        __("Add Your Message Here");

		 $str_link_text  =        __("Enter Link Text");

		 $str_link_url     =    __("http://example.com");

		 $start_date     =        '';

		 $end_date         =        '';

		 $show_where     =        __("Select");

		 $bgColor        =   "#DDDDDD";

		 $textColor      =   "#333333";

		 $linkColor      =   "#0092cc";

		 $is_home        =   '';

		 $is_int         =   '';

		 $is_all         =   '';

		 $is_category	 =    '';

		 $is_page        =    '';

		 $is_post       =     '';

		 $id			=	'';
     
		

        $show_where_selected  = '<option value="-1" selected >'.$show_where.'</option>';

		

		$show_where_selected  .= '<option value="home" >HomePage</option>';

		

		$show_where_selected .= '<option value="inner" >Inner Pages</option>';

		

		$show_where_selected .= '<option value="all" >All Pages/Posts</option>';

		

		$show_where_selected .= '<option value="category" >Category</option>';

		

		$show_where_selected .= '<option value="page" >Page</option>';

		

		$show_where_selected .= '<option value="post" >Post</option>';
		
		$ex_selected = '<option value="-1" selected >none</option>';
		$ex_selected .= '<option value="post" >Post</option>';
		$ex_selected .= '<option value="page" >Page</option>';
		$ex_selected .= '<option value="category" >Category</option>';

	  }

	  else

	  {

	   // Form label

	   $str_title              =     $p_data['str_title'];

	   $start_date    =     $this->convert_dates($p_data['start_date']);

	   $end_date      =     $this->convert_dates($p_data['end_date']);

	   $duration    =     $p_data['duration'];

	   $show_where    =     $p_data['show_where'];
	   
	   $target			=	$p_data['link_target'];

	   $display_on    =     $p_data['display_on'];

	   $id    =     $title_id;

	   $status    =     $p_data['status'];

	   $pageviews    =     $p_data['imp'];

	   $closetime    =     $p_data['closetime'];

	   $is_home     =  "";

	   $is_int      =  "";

	   $is_all      =  "";

	   $is_category	 =    '';

	   $is_page        =    '';

	   $is_post       =     '';

	   

	   $ids=$this->get_ids($title_id);
       $exclude_ids=$this->get_exclude_ids($title_id);
	   //var_dump($exclude_ids);
	   
		
	   

	   if($show_where=="home")

	   {       

		  $show_where_selected  = '<option value="home" selected >HomePage</option>';
          $is_home  = "selected";
	   }

	   else

	   {

	     

		$show_where_selected  = '<option value="home" >HomePage</option>';

	   }

		if($show_where=="inner")   

		{

		  $show_where_selected  .= '<option value="inner" selected >Inner Page</option>';
		  $is_int  = "selected";
		}

		else

		{

		  

		  $show_where_selected .= '<option value="inner" >Inner Pages</option>';

		}

		if($show_where=="all") 

		{

			$show_where_selected .= '<option value="all" selected >All Pages/Posts</option>';
            $is_all      =  "selected";
		}

		else

		{

			 

			$show_where_selected .= '<option value="all" >All Pages/Posts</option>';

		

		}

		if($show_where=="category") 

		{

			$show_where_selected .= '<option value="category" selected >Category</option>';

		    $is_category  = "selected";

		}

		else

		{

			

		   $show_where_selected .= '<option value="category" >Category</option>';

		

		}

		if($show_where=="page")      

		{

			$show_where_selected .= '<option value="page" selected >Page</option>';

		    $is_page  = "selected";

		}

		else

		{

			$show_where_selected .= '<option value="page" >Page</option>';

		

		}

		if($show_where=="post")      

		{

			$show_where_selected .= '<option value="post" selected >Post</option>';

			$is_post  = "selected";

		}

		else

		{

			$show_where_selected .= '<option value="post" >Post</option>';

		}			   

	  echo('<input type="hidden" name="title_id" value="'.$title_id.'" />');

	  }
	  // output page

	  echo '<table class="form-table"><tbody>';

			echo '<tr valign="top"><th scope="row"><label for="Global Settings"><h3 style="margin:10px 0 0">Global Settings</h3></label></th>

			 <td></td></tr>';

             echo '<tr valign="top"><th scope="row"><label for="Campaign Name">Choose your campaign name</label></th>

			 <td><input type="text" class="regular-text" name="str_title" value="'.$str_title.'" id="infobar_title"/></td></tr>';

			 

			 if(isset($_GET['title_id']) )

			{

				 if($display_on)

				 {

				   $header='checked="checked"';

				   $footer='';

				 }

				 else

				 {

				   $footer='checked="checked"';

				   $header='';

				 }

			}

			 else

	       {   $header='checked="checked"';

			   $footer='';

		   }

			 

			 echo '<tr valign="top">

				<th scope="row"><label for="display-position">Display position</label></th>

					<td>

					<fieldset><p>

					<label for="display_header"><input type="radio" value="1" id="display_header" name="display_on" '.$header.' >&nbsp;&nbsp;&nbsp;header</label><br>

					

					<label for="display_footer"><input type="radio" value="0" id="display_footer" name="display_on" '.$footer.' >&nbsp;&nbsp;&nbsp;footer</label>

					</p></fieldset>

					</td>

				</tr>';

			//Show where pages
			 echo '<tr valign="top">

			<th scope="row"><label for="default_role">Select pages</label></th>

			<td>

			<select name="show_where" class="show_where" id="infobar_show_where" style="width:150px;" >

			'.$show_where_selected.'</select>

			</td></tr>';

			    

				 if($is_page!=''){$pageids=$ids;} else {$pageids= array(  );}

				 if($is_post!=''){$postids=$ids;}  else {$postids= array(  );}

				 if($is_category!=''){$catids=$ids;} else {$catids= array(  );}

				 

				if(empty($pageids)){$style='style="display:none;"';} else {$style='';}

			echo '<tr valign="top" id="showpages" '.$style.'>

			<th scope="row"><label for="default_role"></label></th>

			<td>';

			echo $this->get_page_list($pageids);

			echo '</td></tr>';

			

			if(empty($postids)){$style='style="display:none;"';} else {$style='';}

			echo '<tr valign="top" id="showposts" '.$style.'>

			<th scope="row"><label for="default_role"></label></th>

			<td>';

			echo $this->get_post_list($postids);

			echo '</td></tr>';

			

			if(empty($catids)){$style='style="display:none;"';} else {$style='';}

			echo '<tr valign="top" id="showcategories" '.$style.'>

			<th scope="row"><label for="default_role"></label></th>

			<td>';	

			echo $this->get_category_list($catids);

			echo '</td></tr>';	
			
			//exclude pages	 
			 if($is_home!='' || $is_page!='' || $is_post!='' || $id=='' ){$style='style="display:none;"';}else{$style='';}
			
			echo '<tr valign="top" class="tr-exclude" '.$style.'>
			<th scope="row"><label for="default_role">Exclude</label></th>';
			
            //$style='style="display:none;"';
			$style='';
			 if(!empty($exclude_ids['post'])){$postids=$exclude_ids['post'];}  else {$postids= array(  );}

	  
	       if($is_int!='' || $is_all!='' || $is_category!=''){$style='';}else{$style='style="display:none;"';}
		 
			echo '<td id="showposts_1" '.$style.'>';
			echo $this->get_post_list($postids,1,'post_exclude');
			echo '</td>';
			
			if(!empty($exclude_ids['category'])){$catids=$exclude_ids['category'];} else {$catids= array(  );}
			
			if($is_int!='' || $is_all!=''){$style='';}else{$style='style="display:none;"';}
			
			echo '<td id="showcategories_1" '.$style.'>';	
			echo $this->get_category_list($catids,1,'category_exclude');
			echo '</td>';
			
			if(!empty($exclude_ids['page'])){$pageids=$exclude_ids['page'];} else {$pageids= array(  );}
			
			if($is_int!='' || $is_all!=''){$style='';}else{$style='style="display:none;"';}
			
			echo '<td id="showpages_1" '.$style.'>';
			echo $this->get_page_list($pageids,1,'page_exclude');
			echo '</td>';
				
			echo '</tr>';	
			 if($duration)

			 {

			    $custom='checked="checked"';

			   $everyday='';

			   $style='';

			 }

			 else

			 {

			   

			   $everyday='checked="checked"';

			   $custom='';

			   $style='style="display:none;"';

			 }

			 

			echo '<tr valign="top">

				<th scope="row">Show infobar</th>

					<td>

					<fieldset><p>

					<label for="display_everyday"><input type="radio" value="0" class="dispay-setting" id="display_everyday" name="display_duration" '.$everyday.' >&nbsp;&nbsp;&nbsp;everyday</label><br>

					

					<label for="display_custom"><input type="radio" value="1" class="dispay-setting" id="display_custom" name="display_duration"  '.$custom.' >&nbsp;&nbsp;&nbsp;on selected dates</label>

					</p></fieldset>

					</td>

				</tr>';

				

             echo '<tr valign="top" class="tr-date-picker" '.$style.' ><th scope="row"><label for="start-date">Start date</label></th>

			 <td><input type="text" class="regular-text" name="infobar_start_date"  value="'.$start_date.'" id="infobar_start_date" readonly="readonly"/></td></tr>';

			 

			 echo '<tr valign="top" class="tr-date-picker" '.$style.' ><th scope="row"><label for="end-date">End date</label></th>

			 <td><input type="text" class="regular-text" name="infobar_end_date" value="'.$end_date.'" id="infobar_end_date" readonly="readonly"/></td></tr>';
			 
			 if($target)

			 {
			   $new='checked="checked"';
			   $same='';
			 }

			 else

			 {
			   $same='checked="checked"';
			   $new='';
			 }
			 
			 echo '<tr valign="top">

				<th scope="row">Open link in</th>

					<td>

					<fieldset><p>

					<label for="same_window"><input type="radio" value="0" class="link_target" name="link_target" '.$same.' >&nbsp;&nbsp;&nbsp;same window</label><br>

					

					<label for="new_window"><input type="radio" value="1" class="link_target" name="link_target"  '.$new.' >&nbsp;&nbsp;&nbsp;new window</label>

					</p></fieldset>

					</td>

				</tr>';

			 if($pageviews=='')

			 {

			   $pageviews=1;

			 }

			  echo '<tr valign="top"><th scope="row"><label for="pageviews">Pageviews before infobar appears</label></th>

			 <td><input type="number" class="small-text" name="pageviews" value="'.$pageviews.'" id="pageviews" >&nbsp;views</td></tr>';

			 

			 if($closetime=='')

			 {

			   $closetime=1;

			 }

			  echo '<tr valign="top"><th scope="row"><label for="closetime">Don\'t reshow infobar for</label></th>

			 <td><input type="number" class="small-text" name="closetime" value="'.$closetime.'" id="closetime" >&nbsp;days</td></tr>';

			echo '</tbody></table>';

			

			echo '<div id="bar-variant">';

			if(isset($_GET['title_id']) )

			{

				

				$sql ="SELECT * FROM infobar_variants WHERE int_title_id =".$_GET['title_id']." order by int_title_id ASC";

				$result  = mysql_query($sql);

	             $count=1;

				 while ($row=mysql_fetch_assoc($result))

				 {

				       $mid=$row['int_message_id'];

					   $vname = $row['name'];

					   $mtxt= htmlspecialchars($row['str_message'],ENT_QUOTES);

					   $mltype=$row['link_type'];

					   $mltxt=htmlspecialchars($row['str_link_text'],ENT_QUOTES);

					   $mlimg=$row['str_link_img'];

					   $mlurl=$row['str_link_url'];

					   $status=$row['int_active'];

					   $options     =  json_decode($row['str_options'],true);

					   $bgColor     =  $options['bgColor'];

					   $textColor   =  $options['textColor'];

					   $linkColor   =  $options['linkColor'];

					   $theme   =  $row['theme'];

					   echo '<div id="bvariant_'.$count.'" class="bvariant">';

					    echo '<h3 class="var-header">Infobar Variant '.$count.'</h3>';

					    echo '<input type="hidden" id="mid_'.$count.'" value="'.$mid.'" name="mid[]" >';

						echo '<table class="form-table" ><tbody>';

						

						 echo '<tr valign="top">

						<th scope="row"><label for="mtext">Variant name</label></th>

						<td><input type="text" class="regular-text vname" value="'.$vname.'" id="vname_'.$count.'" name="vname[]"></td>

						</tr>';

						

						 echo '<tr valign="top">

						<th scope="row"><label for="mtext">Message text</label></th>

						<td><input type="text" class="regular-text mtxt" value="'.$mtxt.'" id="mtext_'.$count.'" name="mtext[]"></td>

						</tr>';

						if($mltype==-1)
						 {

						   $txtchk='checked';

						   $imgchk='';

						   $img='http://';

						   $style_img='style="display:none;"';
						 }
						 else if($mltype==0)
						 {

						   $imgchk='checked';

						   $txtchk='';

						   $img=$mlimg;

						   $style_img='';

						 }


						echo '<tr valign="top">

						<th scope="row">Link type</th>

						<td>

						<fieldset><p>

						<label for="linktype"><input type="radio" value="-1" class="linktype" id="typelink_'.$count.'" name="linktype['.$count.']" '.$txtchk.' >&nbsp;&nbsp;&nbsp;text</label><br>

						

						<label for="linktype"><input type="radio" value="0" class="linktype" id="typeimage_'.$count.'" name="linktype['.$count.']" '.$imgchk.' >&nbsp;&nbsp;&nbsp;text and custom image</label>

						</p></fieldset>

						</td>

						</tr>';

						echo '<tr valign="top" '. $style_img.' id="tr-cimage_'.$count.'">

						<th scope="row"><label for="cmage">Image URL</label></th>

						<td><input type="text" class="regular-text cimage" value="'.$img.'" id="cimage_'.$count.'" name="cimage[]">&nbsp; recommended size 32x32</td>

						</tr>'; 					

						echo '<tr valign="top">

						<th scope="row"><label for="preview">Live preview</label></th>

						<td id="preview_'.$count.'" class="preview-ver"></td>

						</tr>';					

						echo '<tr valign="top" >

						<th scope="row"><label for="ltext">Link text</label></th>

						<td><input type="text" class="regular-text ltxt" value="'.$mltxt.'" id="ltext_'.$count.'" name="ltext[]"></td>

						</tr>';

						echo '<tr valign="top">

						<th scope="row"><label for="lurl">Link url</label></th>

						<td><input type="text" class="regular-text lurl" value="'.$mlurl.'" id="lurl_'.$count.'" name="lurl[]"></td>

						</tr>';					

						$schemes=$this->colorschemes();
						
						echo '<tr>

						<th scope="row">Color scheme</th>

						<td>

						<fieldset>

						<select name="theme[]" class="color-picker" id="colorpicker_'.$count.'" >';

						foreach($schemes as $i=>$scheme)

						{

						     $select="";

						    if($i==$theme)

							{

							 $select= "selected";

							}

							echo '<option value="'.$i.'" '.$select.'>'.$scheme.'</option>';

						}

						echo '</select></fieldset></td></tr>';

						 echo '<tr valign="top" class="tr-color_'.$count.'" >

						<th scope="row"><label for="bgColor">Background colour</label></th>

						<td><input type="text" id="bgColor_'.$count.'"   class="pickcolor bgColor" name="options[bgColor][]"   value="'.$bgColor.'" style="background-color:'.$bgColor.'" /></td>

						</tr>';

						echo '<tr valign="top" class="tr-color_'.$count.'" >

						<th scope="row"><label for="textcolor">Text color</label></th>

						<td><input type="text" id="textColor_'.$count.'" class="pickcolor textColor" name="options[textColor][]" value="'.$textColor.'" style="background-color:'.$textColor.'" /></td>

						</tr>';					

						echo '<tr valign="top" class="tr-color_'.$count.'" >

						<th scope="row"><label for="linkColor">Link color</label></th>

						<td><input type="text" id="linkColor_'.$count.'" class="pickcolor linkColor" name="options[linkColor][]" value="'.$linkColor.'" style="background-color:'.$linkColor.'" /></td>

						</tr>';

						if($status)

						{

						  $status="Active";

						}

						else

						{

						  $status="Disabled";

						}

						echo '<tr valign="top">

						<th scope="row"><label for="predel"></label></th>

						<td><a href="#" id="status_'.$count.'" class="status-ver">'.$status.'</a>&nbsp;|&nbsp;<a href="#" id="delete_'.$count.'" class="delete-ver">Delete</a><span id="tspin_'.$count.'"></span></td>

						</tr>';						

						echo '</tbody></table>';

						echo '<div id="bar_'.$count.'" class="popup" style="display:none;overflow:hidden;"></div>';

						echo '</div>';

						$count++;
				 }
			}

			else

			{

			    echo '<div id="bvariant_1" class="bvariant">';

			    echo '<h3 class="var-header">Infobar Variant 1</h3>';

				echo '<table class="form-table" ><tbody>';			

				echo '<tr valign="top">

				<th scope="row"><label for="mtext">Variant name</label></th>

				<td><input type="text" class="regular-text vname" value="Variant 1" id="vname_1" name="vname[]"></td>

				</tr>';						

			     echo '<tr valign="top">

				<th scope="row"><label for="mtext">Message text</label></th>

				<td><input type="text" class="regular-text mtxt" value="'.$str_message.'" id="mtext_1" name="mtext[]"></td>

				</tr>';

				echo '<tr valign="top">

				<th scope="row">Link type</th>

					<td>

					<fieldset><p>

					<label for="linktype"><input type="radio" value="-1" class="linktype" id="typelink_1" name="linktype[1]" checked >&nbsp;&nbsp;&nbsp;text</label><br>

					<label for="linktype"><input type="radio" value="0" class="linktype" id="typeimage_1" name="linktype[1]" >&nbsp;&nbsp;&nbsp;text and custom image</label>

					</p></fieldset>

					</td>

				</tr>';

				echo '<tr valign="top" style="display:none" id="tr-cimage_1">

				<th scope="row"><label for="cmage">Image URL</label></th>

				<td><input type="text" class="regular-text cimage" value="http://" id="cimage_1" name="cimage[]">&nbsp; recommended size 32x32</td>

				</tr>'; 							

				echo '<tr valign="top">

				<th scope="row"><label for="preview">Live preview</label></th>

				<td id="preview_1" class="preview-ver"></td>

				</tr>';

				echo '<tr valign="top" id="tr-ltext_1">

				<th scope="row"><label for="ltext">Link text</label></th>

				<td><input type="text" class="regular-text ltxt" value="'.$str_link_text.'" id="ltext_1" name="ltext[]"></td>

				</tr>';		

				echo '<tr valign="top">

				<th scope="row"><label for="lurl">Link url</label></th>

				<td><input type="text" class="regular-text lurl" value="'.$str_link_url.'" id="lurl_1" name="lurl[]"></td>

				</tr>';		

				$schemes=$this->colorschemes();

				echo'<tr>

				<th scope="row">Color scheme</th>

				<td>

				<fieldset>

				<select name="theme[]" class="color-picker" id="colorpicker_1" >';

				foreach($schemes as $i=>$scheme)

				{

					echo '<option value="'.$i.'" >'.$scheme.'</option>';

				}

				echo '</select></fieldset></td></tr>';		     

				 echo '<tr valign="top" class="tr-color_1">

				<th scope="row"><label for="bgColor">Background colour</label></th>

				<td><input type="text" id="bgColor_1"   class="pickcolor bgColor" name="options[bgColor][]"   value="'.$bgColor.'" style="background-color:'.$bgColor.'" /></td>

				</tr>';

				echo '<tr valign="top" class="tr-color_1">

				<th scope="row"><label for="textcolor">Text color</label></th>

				<td><input type="text" id="textColor_1" class="pickcolor textColor" name="options[textColor][]" value="'.$textColor.'" style="background-color:'.$textColor.'" /></td>

				</tr>';

				echo '<tr valign="top" class="tr-color_1">

				<th scope="row"><label for="linkColor">Link color</label></th>

				<td><input type="text" id="linkColor_1" class="pickcolor linkColor" name="options[linkColor][]" value="'.$linkColor.'" style="background-color:'.$linkColor.'"  /></td>

				</tr>';			

				echo '<tr valign="top">

				<th scope="row"><label for="predel"></label></th>

				<td><a href="#" id="delete_1" class="delete-ver">Delete</a></td>

				</tr>';				

				echo '</tbody></table>';

				echo '<div id="bar_1" class="popup" style="display:none;overflow:hidden;"></div>';

				echo '</div>';
			}

			echo '</div>';

			echo '<table class="form-table"><tbody>';

			echo '<tr valign="top">

				<th scope="row"><label for="opt"></label></th>

				<td><a href="#" id="addv">Add variant</a></td></tr>';

			echo '</tbody></table><br><br>';

			

                echo '<input type="hidden" name="save" value="Save" />';				

				echo '<p class="submit"><input type="button" value="Save" class="button button-primary" id="SubmitButton" name="save" ><span id="tspin"></span></p>';

		echo('</form>');

		echo '</div>';
	}

	

	// function is used get selected campaigns
	function get_campaign($title_id=0,$extra=NULL) 

	{

	 if($title_id===0 OR $title_id===NULL){

	   wp_die("NO RECORD WAS REQUESTED");

	 }

	  global $wpdb;          

		 $where     =  "WHERE int_title_id = $title_id ";

	  $sql       =     "SELECT * FROM infobar_campaigns $where";

	 $result         =   mysql_query($sql);

	 

	  if($result)

	  {

		return mysql_fetch_assoc($result);

	  }

	  else

	  {

		return false;

	  }

	

	}

	// function used to get all campaigns 

  function get_all_campaigns()

	{

	 global $wpdb;

		$sql  =  "SELECT int_title_id , str_title, start_date, end_date, show_where, status FROM infobar_campaigns ORDER BY int_title_id  DESC";

	 $result         =   mysql_query($sql);  

	  if($result)

	  {

	    

		  $table = '<table border="1" cellpadding="0" cellspacing="0" class="infobar-list-table">';

		  $table_head     =   "<thead>

								<tr>                           

									 <th>"  .__("Bar Title"). "</th>             

								   <th>"  .__("Dates").     "</th>           

								   <th>"  .__("Location").  "</th>           

								   <th>"  .__("Active").    "</th>           

								   <th>"  .__("Remove").    "</th>           

								 </tr>

								 </thead>";

			

		  $table         .=   $table_head;

		  while ( $row = mysql_fetch_assoc($result) )

		  {

				$id             =   $row['int_title_id'];	   

				$title          =   $row['str_title'];

				$message        =   $row['message'];

				$start_date     =   $this->convert_dates($row['start_date']);                      

				$end_date       =   $this->convert_dates($row['end_date']);

				$show_where     =   $row['show_where'];    

				$active         =   $row['status'];
				if($show_where=='inner'){$show_where='inner pages';}
			  if($active == 1 ){

				$active = "on";

				$active_title = __("Deactivate");

			  }elseif($active == 0){

				$active= "off";

				$active_title = __("Activate");

			  }

				if( $start_date != null && $end_date != null ){

				  $start_date   =   $start_date . __(" thru");

				}elseif($start_date == null && $end_date != null){

				   $start_date   =   __("Bar Expires");

				}elseif($start_date != null && $end_date == null){

					  $start_date   =   __("Bar Starts on ").$start_date;

				   }	

				$table         .=   "\n<tr>";

			    $table       .=   "\n<td class='infobar_$active $id'><a href='admin.php?page=infobar_campaign&amp;title_id=$id' >$title</a></td>";

				  $table       .=   "\n<td class='infobar_$active $id'>$start_date $end_date</td>";

				  $table       .=   "\n<td class='infobar_$active $id'>Display on $show_where</td>";

				  $table       .=   "\n<td class='infobar_$active $id'><a href='#' id='$id-isactive' class='onoff $active' title='$active_title'>$active</a></td>";

				  $table       .=   "\n<td class='infobar_$active $id'><a href='#' id='$id-del' class='delete'>".__("Delete")."</a></td>";

				$table         .=   "\n</tr>";
		  }   

		$table .= "</table>";

		return $table;       
	  }else {

		return false;

	  }

	}

	// function to add and update campaigns

	function update_campaigns()
   {
	 $this->check_magic_quotes();
	 $start_date= $_POST['infobar_start_date'];
	 $end_date= $_POST['infobar_end_date'];
	 global $current_user;
	  global $wpdb;
	  $active         =   1;
	  $created_by     =   $current_user->user_login;
	   // var_dump($_POST);
	  //echo "<br/>";

	   if ($_POST['save']=="Save")
		  {
			foreach($_POST AS $key => $value)
			{
			  ${$vkey} = $value;

				if($vkey !=="save" && $value !="")
				{
					if(!is_array($value))
					{
					   ${$key} = $value;
					}//end if
				}
			} // end for each
		  }    

		  foreach($_POST['theme'] as $th)
		  {
			$themes[] = $th;
		  }

		  foreach($_POST['linktype'] as $lt)
		  {
			$linktypes[] = $lt;
		  }

		if($show_where=='home')

			{
			    $ids[]=0;
				$exclude=0;
			}
			elseif($show_where=='inner' || $show_where=='all')
			{
			  $ids[]=0;
			  
			  if(empty($_POST['chkpage1']) && empty($_POST['chkpost1']) && empty($_POST['chkcat1']))
			  {
			    $exclude=0;
			  }
			  else
			  {
			    $exclude=1;
			  }
			}
			else

			{
				if($show_where=='page')

				{

				  $ids=$_POST['chkpage'];
				  $exclude=0;

				}

				else if($show_where=='post')

				{

				  $ids=$_POST['chkpost'];
				  $exclude=0;

				}

				else if($show_where=='category')

				{

				  $ids=$_POST['chkcat'];
				  if(empty($_POST['chkpost1']))
				  {
					$exclude=0;
				  }
				  else
				  {
					$exclude=1;
				  }

				}
			}
         
	  if ( !isset($_POST['title_id']) && $_POST['save']=="Save")
	   {
		$start_date = $this->convert_dates_mysql($start_date);

		$end_date   = $this->convert_dates_mysql($end_date,1);


		  $add_to_title_query = "INSERT INTO infobar_campaigns

				(

				  `str_title`,`start_date`,`end_date`,`show_where`,`link_target`,`display_on`,`duration`,`imp`, `closetime`,last_shown

				)

				 VALUES ('".$str_title."','".$start_date."','".$end_date."','".$show_where."','".$link_target."',".$display_on.",".$display_duration.",".$pageviews.",".$closetime.",null)";

			$result =  mysql_query($add_to_title_query);

			$last_inserted_mysql_id = mysql_insert_id();

			//$last_inserted_mysql_id =1;

	    //echo "<br/>id=".$last_inserted_mysql_id."<br/>";

		 if(is_array($ids))
		 {
		    foreach($ids as $id)
			{
				$sql = "INSERT INTO infobar_link_pages

				(`campaign_id`,`link_id`)

				 VALUES ('".$last_inserted_mysql_id."',".$id.")";

			     $result =  mysql_query($sql);
			 }
		 }

		//add exclude pages
		if($exclude)
		{
		   if($show_where=='category' && !empty($_POST['chkpost1']))
		   {
			   foreach($_POST['chkpost1'] as $id)

				 {

					$sql = "INSERT INTO infobar_exclude_pages

					(`campaign_id`,`type`,`link_id`)

					 VALUES ('".$last_inserted_mysql_id."','post',".$id.")";

					 $result =  mysql_query($sql);

				 }
			 }
			 elseif(!empty($_POST['chkpage1']) || !empty($_POST['chkpost1']) || !empty($_POST['chkcat1']))
			 {
			    if(!empty($_POST['chkpage1']))
				{
					foreach($_POST['chkpage1'] as $id)

					 {

						$sql = "INSERT INTO infobar_exclude_pages

						(`campaign_id`,`type`,`link_id`)

						 VALUES ('".$last_inserted_mysql_id."','page',".$id.")";

						 $result =  mysql_query($sql);

					 }
				}
				
				if(!empty($_POST['chkpost1']))
				{
					foreach($_POST['chkpost1'] as $id)

					 {

						$sql = "INSERT INTO infobar_exclude_pages

						(`campaign_id`,`type`,`link_id`)

						 VALUES ('".$last_inserted_mysql_id."','post',".$id.")";

						 $result =  mysql_query($sql);

					 }
				}
				
				if(!empty($_POST['chkcat1']))
				{
					foreach($_POST['chkcat1'] as $id)

					 {

						$sql = "INSERT INTO infobar_exclude_pages

						(`campaign_id`,`type`,`link_id`)

						 VALUES ('".$last_inserted_mysql_id."','category',".$id.")";

						 $result =  mysql_query($sql);

					 }
				}
			 }
		}	  

		foreach($_POST['mtext'] as $row=>$mtext)

		{

		    $str_name=mysql_real_escape_string($_POST['vname'][$row]);

			$str_message=mysql_real_escape_string($_POST['mtext'][$row]);

			$linktype=$linktypes[$row];

			if($linktype==-1)

			{

			 $str_link_img='';

			}

			else if($linktype==0)

			{

			 $str_link_img=mysql_real_escape_string($_POST['cimage'][$row]);

			}

			$str_link_text=mysql_real_escape_string($_POST['ltext'][$row]);

			$str_link_url=mysql_real_escape_string($_POST['lurl'][$row]);

			$option['bgColor']=$_POST['options']['bgColor'][$row];

			$option['textColor']=$_POST['options']['textColor'][$row];

			$option['linkColor']=$_POST['options']['linkColor'][$row];

			$theme=$themes[$row];

			//echo "id=".$id;

			$options=json_encode($option);

			unset($option);

			$row_data_message[]="($last_inserted_mysql_id,'$str_name','$str_message','$linktype','$str_link_text','$str_link_img','$str_link_url' ,'$options','$theme')";
		}

		 $add_to_message_query = "INSERT INTO infobar_variants

				(

				  int_title_id  ,`name`, `str_message`,`link_type`,`str_link_text` ,`str_link_img`,`str_link_url` ,`str_options`,`theme`)

				 VALUES ".implode(',', $row_data_message);

		$result =  mysql_query($add_to_message_query);

		//die;

		 if(!$result){

		  return false;

		}else{

		  return true;

		}
	}

	elseif (isset($_POST['title_id']) && $_POST['save']=="Save")
	{
        //var_dump($ids);

		//die;

	    $this->delete_ids($title_id);

        if(is_array($ids))
		 {
			 foreach($ids as $id)
			 {
				$sql = "INSERT INTO infobar_link_pages

				(`campaign_id`,`link_id`)

				 VALUES ('".$title_id."',".$id.")";

			     $result =  mysql_query($sql);
			 }
		 }
         
		 //add exclude pages
		if($exclude)
		{
		   if($show_where=='category' && !empty($_POST['chkpost1']))
		   {
			   foreach($_POST['chkpost1'] as $id)

				 {

					$sql = "INSERT INTO infobar_exclude_pages

					(`campaign_id`,`type`,`link_id`)

					 VALUES ('".$title_id."','post',".$id.")";

					 $result =  mysql_query($sql);

				 }
			 }
			 elseif(!empty($_POST['chkpage1']) || !empty($_POST['chkpost1']) || !empty($_POST['chkcat1']))
			 {
			    if(!empty($_POST['chkpage1']))
				{
					foreach($_POST['chkpage1'] as $id)

					 {

						$sql = "INSERT INTO infobar_exclude_pages

						(`campaign_id`,`type`,`link_id`)

						 VALUES ('".$title_id."','page',".$id.")";

						 $result =  mysql_query($sql);
					 }
				}
				
				if(!empty($_POST['chkpost1']))
				{
					foreach($_POST['chkpost1'] as $id)
					 {
						$sql = "INSERT INTO infobar_exclude_pages

						(`campaign_id`,`type`,`link_id`)

						 VALUES ('".$title_id."','post',".$id.")";

						 $result =  mysql_query($sql);
					 }
				}
				
				if(!empty($_POST['chkcat1']))
				{
					foreach($_POST['chkcat1'] as $id)
					 {

						$sql = "INSERT INTO infobar_exclude_pages

						(`campaign_id`,`type`,`link_id`)

						 VALUES ('".$title_id."','category',".$id.")";

						 $result =  mysql_query($sql);
					 }
				}
			 }
		}
		
		$start_date = $this->convert_dates_mysql($start_date);

		$end_date   = $this->convert_dates_mysql($end_date,1);

	 // var_dump($_POST);

	  foreach($_POST['mtext'] as $row=>$mtext)
		{
		    $str_name=mysql_real_escape_string($_POST['vname'][$row]);

			$int_message_id=mysql_real_escape_string( $_POST['mid'][$row]);

			$str_message=mysql_real_escape_string($_POST['mtext'][$row]);

			$linktype=$linktypes[$row];

			if($linktype==-1)
			{

			 $str_link_img='';
			}
			else if($linktype==0)
			{
			 $str_link_img=mysql_real_escape_string($_POST['cimage'][$row]);
			}

			$str_link_text=mysql_real_escape_string($_POST['ltext'][$row]);

			$str_link_url=mysql_real_escape_string($_POST['lurl'][$row]);

			$option['bgColor']=$_POST['options']['bgColor'][$row];

			$option['textColor']=$_POST['options']['textColor'][$row];

			$option['linkColor']=$_POST['options']['linkColor'][$row];

			$theme=$themes[$row];

			//echo "id=".$id;

			$options=json_encode($option);

			unset($option);

			$count=0;

			if($int_message_id >=1)

			{

			 $sql="select count(*) as count from infobar_variants where int_message_id=$int_message_id";

			 $result =  mysql_query($sql);

			 $count=mysql_num_rows ($result);
		   }
		   
		 if(!$count)
		 {
		    $sql= "INSERT INTO infobar_variants

				(

				  int_title_id  , `name`,`str_message`,`link_type`,`str_link_text` ,`str_link_img`,`str_link_url` ,`str_options`,`theme`)

				 VALUES($title_id,'$str_name','$str_message','$linktype','$str_link_text','$str_link_img','$str_link_url' ,'$options','$theme')";
		 }
		 else
		 {
	    	 

			 $sql = "UPDATE infobar_variants as m  INNER JOIN infobar_campaigns as t on m.int_title_id=t.int_title_id

			 SET     

			  t.str_title     =     '$str_title',

			  t.show_where     =     '$show_where',
			  
			  t.link_target    =   '$link_target',

			  t.start_date     =    '$start_date',

			  t.end_date       =    '$end_date',

			  t.duration       =     $display_duration,

			  t.display_on     =     $display_on,

			  t.imp    		   =     $pageviews,

			  t.closetime       =     $closetime,

			  m.str_options       =    '$options',

			  m.name           =    '$str_name',

			  m.str_message       =    '$str_message',

			  m.link_type       =    '$linktype',

			  m.str_link_text     =    '$str_link_text',

			  m.str_link_img      =    '$str_link_img',

			  m.str_link_url      =    '$str_link_url',

			  m.theme             =    '$theme'

			 WHERE m.int_message_id=$int_message_id and t.int_title_id=$title_id ;";

		 }

		 $result =  mysql_query($sql);

	    }         

		if(!$result)
		{

			return false;

		}
		else
		{

		  return true;

		 }

	}

	}

	// to function disable all expired campaigns

    function disable_expired($infobar_id=NULL)
	{
	  // clean out the expired ones

	  global $wpdb;

      if($infobar_id==NULL)
	  {
			 $sql = "SELECT int_title_id FROM infobar_campaigns where end_date < now() and duration=1";
			$result =  mysql_query($sql);

			while($id=mysql_fetch_assoc($result))
			{
			   //var_dump($id);
			   $pid=$id['int_title_id'];
			  $sql = "UPDATE infobar_campaigns SET `status`= -1 WHERE int_title_id=$pid";
	          $res =  mysql_query($sql);
			}
	  }
	  else
	  {
	      $sql = "SELECT int_title_id FROM infobar_campaigns where end_date < now() and duration=1 and int_title_id=$infobar_id";
		  $result =  mysql_query($sql);
		  if(mysql_num_rows($result))
		  {
			$sql = "UPDATE infobar_campaigns SET `status`= -1 WHERE int_title_id=$infobar_id";
		  }
		  else
		  {
		      $sql = "UPDATE infobar_campaigns SET `status`= 1 WHERE int_title_id=$infobar_id";
		  }
		     mysql_query($sql);
	  }
	}


	// function to check if the database already have our tables

	function check_table_existance($new_table)
	{
	  //Always set wpdb globally!

	  global $wpdb;

	  foreach ($wpdb->get_col("SHOW TABLES",0) as $table )
	  {
		   if ($table == $new_table)
		   {
			   return true;
		   }
	   }
	  return false;
	}
	 // End check_table_existance

	 //create tables if does not exits
	function install_tables_infobar_campaigns()
	{
	  //Always set wpdb globally!
		global $wpdb;
	  //Table structure

	  $data_sql = "CREATE TABLE infobar_campaigns (

	  `int_title_id` int(11) NOT NULL AUTO_INCREMENT,
	  
	  `str_title` varchar(255) NOT NULL,
	  
	  `start_date` datetime NOT NULL,
	  
	  `end_date` datetime NOT NULL,
	  
	  `duration` int(11) NOT NULL DEFAULT '0',
	  
	  `show_where` varchar(255) NOT NULL,
	  
	  `link_target` tinyint(4) NOT NULL,
	  
	  `display_on` int(11) NOT NULL,
	  
	  `browsers` varchar(100) NOT NULL DEFAULT 'All',
	  
	  `status` int(11) NOT NULL DEFAULT '1',
	  
	  `imp` int(11) NOT NULL DEFAULT '1',
	  
	  `loadtime` int(11) NOT NULL DEFAULT '1',
	  
	  `closetime` int(11) NOT NULL DEFAULT '1',
	  
	  `tracking` int(11) NOT NULL DEFAULT '0',
	  
	  `track_imps` int(11) NOT NULL DEFAULT '0',
	  
	  `dated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  
	  `last_shown` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	  
	  `cookie_name` varchar(100) NOT NULL DEFAULT 'infobar',
	  
	  PRIMARY KEY (`int_title_id`)

	  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

	  mysql_query($data_sql);
	}

	//create tables if does not exits
	function install_tables_infobar_variants()
	{

	   $data_sql1 = "CREATE TABLE IF NOT EXISTS `infobar_variants` (

		  `int_message_id` smallint(16) NOT NULL AUTO_INCREMENT,

		  `int_title_id` int(11) NOT NULL,

		  `name` varchar(255) NOT NULL,

		  `str_message` varchar(255) NOT NULL,

		  `link_type` int(11) NOT NULL DEFAULT '0',

		  `str_link_text` varchar(255) NOT NULL,

		  `str_link_img` text NOT NULL,

		  `str_link_url` varchar(255) NOT NULL,

		  `str_options` varchar(255) NOT NULL,

		  `theme` int(11) NOT NULL,

		  `int_active` int(11) NOT NULL DEFAULT '1',

		  `dated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

		  UNIQUE KEY `int_message_id` (`int_message_id`)

	  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8" ;
  
		  mysql_query($data_sql1);	 
	} //end 

	

    //create tables if does not exits
   function install_tables_infobar_session()
	{  
	
	$data_sql2= "CREATE TABLE IF NOT EXISTS `infobar_session` (

		     `int_session_id` int(11) NOT NULL,

			 `int_title_id` int(11) NOT NULL

		) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

		mysql_query($data_sql2);
	}

	//create tables if does not exits

	function install_tables_infobar_link_pages()
	{  
	   $data_sql2= "CREATE TABLE IF NOT EXISTS `infobar_link_pages` (

		  `id` int(11) NOT NULL AUTO_INCREMENT,

		  `campaign_id` int(11) NOT NULL,

		  `link_id` int(11) NOT NULL,

		  PRIMARY KEY (`id`)

		) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

		mysql_query($data_sql2);
	}

	//create tables if does not exits
	function install_tables_infobar_exclude_pages()
	{
      $data_sql2= "CREATE TABLE IF NOT EXISTS `infobar_exclude_pages` (
	  
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  
	  `campaign_id` int(11) NOT NULL,
	  
	  `type` varchar(50) NOT NULL,
	  
	  `link_id` int(11) NOT NULL,
	  
	  PRIMARY KEY (`id`)
  
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

		mysql_query($data_sql2);
	}
	
	//delete tables on plugin uninstall

	function uninstall_tables($table_name)
	{  

	   $sql= "DROP TABLE IF EXISTS $table_name";

		mysql_query($sql);

		return $sql;
	}

	 //ALter table
   function alter_table()
	{  
	    $sql='ALTER TABLE infobar_campaigns ADD COLUMN link_target tinyint(4) NOT NULL AFTER display_on';
		mysql_query($sql);
				
		$sql='ALTER TABLE infobar_campaigns ADD COLUMN last_shown timestamp NOT NULL AFTER dated';
		mysql_query($sql);
	   
	}
	
	// function to save and update session table

    function update_session($title_id,$max)
    {
        global $wp_query;
	     global $post;

		$key1=  "SELECT int_session_id from infobar_session where int_title_id=$title_id";
		$key1= mysql_query($key1);
		  // print_r($result);
		$count=mysql_num_rows ($key1);
		$session_id=mysql_fetch_assoc($key1);

	   if($count==0)
		{
				//echo "no records";
				$data_sql4="INSERT INTO `infobar_session` (`int_session_id`,`int_title_id`) VALUES(2,$title_id)";
				mysql_query($data_sql4);
		}
		else
		{
			  if($max)
			  {
				$id=1;
			  }
			  else
			  {
				$id=$session_id['int_session_id']+1;
			  }
			  $sql1="update infobar_session set int_session_id=$id where int_title_id=$title_id";
			  mysql_query($sql1);
		}
		
		$sql1="update infobar_campaigns set last_shown=null where int_title_id=$title_id";
			  mysql_query($sql1);
        return true;
    }       

        //function for AJAX call
	function infobar_ajax()
	{
		if (!current_user_can('manage_options'))  
		{
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		global $wpdb;

		$title_id = $_POST['id'];

		$dowhat = $_POST['dowhat'];

		if( !isset($title_id) OR !isset($dowhat) ) die();

		if ($dowhat=="deactivate") 
		{
		$active = 0;

		$sql = "UPDATE infobar_campaigns SET `status`=$active WHERE int_title_id=$title_id";
		}
		else if($dowhat=="activate") 
		{

		$active = 1;
		$sql = "UPDATE infobar_campaigns SET `status`=$active WHERE int_title_id=$title_id";
		}
		else if ($dowhat=="delete") 
		{
			 echo $this->delete_campaign($title_id);
			 die;
		}
		else if ($dowhat=="qupdate") 
		{

			$title= mysql_real_escape_string($_POST['title']);

			$imp= mysql_real_escape_string($_POST['views']);

			$duration= mysql_real_escape_string($_POST['display_duration']);

			$display_on= mysql_real_escape_string($_POST['display_on']);

			$status= mysql_real_escape_string($_POST['status']);

			$start_date = $this->convert_dates_mysql($_POST['sdate']);

			$end_date   = $this->convert_dates_mysql($_POST['edate'],1);

			$sql = "UPDATE infobar_campaigns SET str_title='".$title."',start_date='".$start_date."',end_date='".$end_date."',imp=".$imp.",display_on=".$display_on.",duration=".$duration." WHERE int_title_id=".$title_id."";

				$result =  mysql_query($sql);

				if(!$result){ $count_max= false; }else{ $count_max= mysql_affected_rows();}

			if($status=='Expired')
			{
			  $this->disable_expired($title_id);
			}

			echo $count_max;

			die();
		}
		elseif($dowhat=="clone") 
		{
	       //var_dump($_POST);
		   //die;
		   $sql = "DROP TEMPORARY TABLE IF EXISTS temp" ;
				 mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;	 
				 
	       $sql="CREATE TEMPORARY TABLE tmp (SELECT * FROM infobar_campaigns WHERE int_title_id =".$title_id.")";
           mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;

		   $sql="UPDATE tmp SET show_where='".$_POST['type']."',str_title=concat(str_title, ' copy'),last_shown=null WHERE int_title_id =".$title_id."";
		   mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;

		   $sql="ALTER TABLE tmp drop int_title_id";
           mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;

			$sql="INSERT INTO infobar_campaigns (SELECT 0,tmp.* FROM tmp)";
			 mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;
			 $pid = mysql_insert_id();

		   $sql = "DROP TABLE tmp" ;
			 mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;
				 
		   $sql="CREATE TEMPORARY TABLE tmp (SELECT * FROM infobar_variants WHERE int_title_id =".$title_id." order by int_message_id ASC)";
           mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;

		   $sql="UPDATE tmp SET int_title_id=".$pid."";
		   mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;

		   $sql="ALTER TABLE tmp drop int_message_id";
           mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;

		   $sql="INSERT INTO infobar_variants (SELECT 0,tmp.* FROM tmp)";
			//mysql_query ( $sql ) or ( "Error " . mysql_error () ) ;
			$result =  mysql_query($sql);

			foreach ($_POST['list'] as $id)
			{

			  $sql="INSERT INTO `infobar_link_pages` (`campaign_id`,`link_id`) VALUES($pid,$id)";
			  $result=mysql_query($sql);
			}

			if(!$result){ return false; }else{  echo $pid;}
			die();

				//$tmp=mysql_fetch_assoc($result);
		}
		else if($dowhat=="vdelete")
		{
			
			$sql= "Delete from infobar_variants where int_message_id=$title_id";
		}
		else if($dowhat=="change_status")
		{
			 $status=$_POST['status'];

			$sql = "UPDATE infobar_variants SET `int_active`=$status WHERE int_message_id=$title_id";
		}

		  $result =  mysql_query($sql);

		if(!$result){ return false; }else{ echo mysql_affected_rows();}

			die();
	}



	

	// helper function to compare different dates

	function compare_str_dates($date_1,$date_2,$operation="g")
	{

	  $date_1  = strtotime($date_1);
	  $date_2  = strtotime($date_2);

	  if($operation==='g') // greater then or == to 0
	  {
		 if ($date_1 <= $date_2)
		 {

		  return true;

		 } else {

		 return false;

		 }
	  }

	  if($operation==='l') // lesser then or == to 0
	  {
		  if ($date_1 >= $date_2)
		  {

			 #die("$date_1 <= $date_2");

			return true;

		  } else {

			return false;
		  }

	   }

	}

	//helper function to convert date
	function convert_dates($datetime)
	{
		$datetime=trim($datetime);

		if ( $datetime ==="0000-00-00 00:00:00" || trim($datetime) =="" || $datetime == NULL || $datetime == null ) return null;

		$datetime = strtotime($datetime);

        $date = date("d/m/Y", $datetime);

		return $date;
	}

	 //helper function to convert date to mysql date
	 function convert_dates_mysql($date,$end=0)
	{
		$date=trim($date);
		
		if($date==null || $date=='')
		{
		   $date="0000-00-00 00:00:00";

		   return $date;
		}

		$array = explode("/",$date);

		//star date

		if($end==0)
		{

			$mysql_datetime = $array['2']."-".$array['1']."-".$array['0']." 00:00:00";
		}
		else //end date
		{
			$mysql_datetime = $array['2']."-".$array['1']."-".$array['0']." 23:59:59";
		}

		return $mysql_datetime;

	}            

        

         //function to get pagelist in dropdown    
	 function get_page_list($ids,$id='',$class='page_include')
	{
		echo '<div class="slideboxdiv '.$class.'">

		     <div class="sidebar-name">

			<div id="pages'.$id.'" class="sidebar-name-arrow"><br></div>

			<h3 id="pages'.$id.'" class="slidebox-link"> Pages</h3></div>

			<div id="pages'.$id.'-box" class="slidebox" style="display: none;">

			<div class="dropdownlist"><label for="checkall" style="display:block"><input type="checkbox" class="chkall" id="chkpage'.$id.'" value="1" name="chkallpages'.$id.'">&nbsp;&nbsp;Select all pages</label>';

		 $pages = get_pages();
		 //var_dump($pages);

		foreach ( $pages as $pagg ) 
		 {
		   if(in_array($pagg->ID, $ids))
		  {

			echo '<label for="id'.$id.'_'.$pagg->ID.'" style="display:block"><input type="checkbox" class="chkpage'.$id.'" id="chkpage'.$id.'_'.$pagg->ID.'" value="'.$pagg->ID.'" name="chkpage'.$id.'[]" checked="checked" >&nbsp;&nbsp;'.$pagg->post_title.'</label>';

		   }
		   else
		   {

			echo '<label for="id'.$id.'_'.$pagg->ID.'" style="display:block"><input type="checkbox" class="chkpage'.$id.'" id="chkpage'.$id.'_'.$pagg->ID.'" value="'.$pagg->ID.'" name="chkpage'.$id.'[]" >&nbsp;&nbsp;'.$pagg->post_title.'</label>';

		   }

		}
		
		echo '</div></div></div>';
	}

      //function to get postlist in dropdown 
	function get_post_list($ids,$id='',$class='post_include') 
	{
	   //var_dump($ids);
		echo '<div class="slideboxdiv '.$class.'">

		     <div class="sidebar-name">

			<div id="posts'.$id.'" class="sidebar-name-arrow"><br></div>

			<h3 id="posts'.$id.'" class="slidebox-link">Posts</h3></div>

			<div id="posts'.$id.'-box" class="slidebox" style="display: none;">

			<div class="dropdownlist">

			<label for="checkall" style="display:block"><input type="checkbox" class="chkall" id="chkpost'.$id.'" value="1" name="chkallposts'.$id.'">&nbsp;&nbsp;Select all posts</label>';

		 $posts = get_posts(array('numberposts' => -1));
		 //var_dump($posts);
		foreach ($posts as $poss ) 
		 {
		   if(in_array($poss->ID, $ids))

		  {

			echo '<label for="id'.$id.'_'.$poss->ID.'" style="display:block"><input type="checkbox" class="chkpost'.$id.'" id="chkpost'.$id.'_'.$poss->ID.'" value="'.$poss->ID.'" name="chkpost'.$id.'[]" checked="checked" >&nbsp;&nbsp;'.$poss->post_title.'</label>';

		   }
		   else
		   {		   

			  echo '<label for="id'.$id.'_'.$poss->ID.'" style="display:block"><input type="checkbox" class="chkpost'.$id.'" id="chkpost'.$id.'_'.$poss->ID.'" value="'.$poss->ID.'" name="chkpost'.$id.'[]" >&nbsp;&nbsp;'.$poss->post_title.'</label>';

		   }
		}
		echo '</div></div></div>';
	}

    //function to get categorylist in dropdown 
	function get_category_list($ids,$id='',$class='category_include') 
    {
		echo '<div class="slideboxdiv '.$class.'">

		     <div class="sidebar-name">

			<div id="categories'.$id.'" class="sidebar-name-arrow"><br></div>

			<h3 id="categories'.$id.'" class="slidebox-link">Categories</h3></div>

			<div id="categories'.$id.'-box" class="slidebox" style="display: none;">

			<div class="dropdownlist"><label for="checkall" style="display:block"><input type="checkbox" class="chkall" id="chkcat'.$id.'" value="1" name="chkallcats'.$id.'">&nbsp;&nbsp;Select all categories</label>';

		 $categories = get_categories(array('parent' => 0,'taxonomy'=> 'category'));
		 //var_dump($posts);

		foreach ( $categories as $category )
        {
		   if(in_array($category->cat_ID, $ids))
		  {

			echo '<label for="id'.$id.'_'.$category->cat_ID.'" style="display:block"><input type="checkbox" class="chkcat'.$id.'" id="chkcat'.$id.'_'.$category->cat_ID.'" value="'.$category->cat_ID.'" name="chkcat'.$id.'[]" checked="checked" >&nbsp;&nbsp;'.$category->cat_name.'</label>';

		   }
		   else
		   {	

			 echo '<label for="id'.$id.'_'.$category->cat_ID.'" style="display:block"><input type="checkbox" class="chkcat'.$id.'" id="chkcat'.$id.'_'.$category->cat_ID.'" value="'.$category->cat_ID.'" name="chkcat'.$id.'[]" >&nbsp;&nbsp;'.$category->cat_name.'</label>';

		   }
		}
		echo '</div></div></div>';      
    }

    // function to rotator variants
	function infobar_data($session_id,$title_id,$preview_infobar) 
	{
			  global $wpdb;
				$sql = "SELECT * FROM infobar_campaigns a inner join infobar_variants b on b.int_title_id=a.int_title_id where b.int_title_id=$title_id and b.int_active=1 order by b.int_message_id ASC";
				$var_data= mysql_query($sql);

			    $count_max=mysql_num_rows ($var_data);
				
			   if($count_max < $session_id)
			   {
				$session_id=1;

				$sql1="update infobar_session set int_session_id=1 where int_title_id=$title_id";

				 mysql_query($sql1);
			   }
				$count=1;
			 while ( $row = mysql_fetch_assoc($var_data) )
			{
				//print_r($row);echo $key;           
				if($count==$session_id)
				{
					//echo "<br/>count=".$count;

					$options= $row;

					$options['int_title_id']    =   $row['int_title_id'];

					$options['str_title']       =   $row['str_title'];

					$options['cookie'] 			=$row['cookie_name'];

					$options['imp']             =   $row['imp'];

					$options['closetime']             =   $row['closetime'];

					$options['display_on']    =   $row['display_on'];

					$options['duration']    =   $row['duration'];

					$options['int_message_id']    =   $row['int_message_id'];

					$options['str_message']        =   $row['str_message'];

					$options['start_date']     =   $row['start_date'];

					$options['end_date']       =   $row['end_date'];

					$options['show_where']     =   $row['show_where'];
					
					$options['link_target']       =   $row['link_target'];

					$options['link_type']       =   $row['link_type'];

					$options['str_link_text']       =   $row['str_link_text'];

					$options['str_link_img']      =   $row['str_link_img'];

					$options['str_link_url']      =   $row['str_link_url'];

					$options['str_options']        =   $row['str_options'];

					$options['int_active']         =   $row['status'];

						
						if($session_id >= $count_max)
						{

						 $options['session'] =1;
						 
						  if($preview_infobar==1)
						  {
							$this->update_session($title_id,1);
						  }
						}
						else
						{
						   $options['session'] =0;

						  if($preview_infobar==1)
						  {
							$this->update_session($title_id,0);
						  }

						}
						
						return $options;
				}
				$count++;
			}
			 return false;
	}
	
	//function to delete projets
	function delete_campaign($pid)
	{
		$sql = "DELETE t1,t3 FROM infobar_campaigns as t1

						INNER JOIN  infobar_link_pages as t3 on t3.campaign_id = t1.int_title_id

						WHERE t1.int_title_id=$pid";		

		if(!mysql_query($sql)){ return false; }else{ $result=mysql_affected_rows();}

		$sql= "Delete from infobar_variants where int_title_id=$pid";
		mysql_query($sql);

		$sql= "Delete from infobar_session where int_title_id=$pid";
		mysql_query($sql);
		
		$sql= "Delete from infobar_exclude_pages where campaign_id=$pid";
		mysql_query($sql);

		return $result;
	}

	// function to get list of posts/pages/categories under a selected campaign
	function get_ids($pid)
	{
	  $sql="Select link_id from infobar_link_pages where campaign_id=$pid";
	   $result=mysql_query($sql);

	   while($id=mysql_fetch_array($result))
	   {
	     $ids[]=$id['link_id'];

	   }
	   return $ids;
	}
	
   // function to get list of posts/pages/categories excluded under a selected campaign
	function get_exclude_ids($pid)
	{
	  $sql="Select link_id,type from infobar_exclude_pages where campaign_id=$pid";
	   $result=mysql_query($sql);

	   while($row=mysql_fetch_array($result))
	   {
         if($row['type']=='page')
		 {
	      $ids['page'][]=$row['link_id'];
		 }
		 elseif($row['type']=='post')
		 {
		    $ids['post'][]=$row['link_id'];
		 }
		 elseif($row['type']=='category')
		 {
		    $ids['category'][]=$row['link_id'];
		 }

	   }
       //$ids = array('page'=>$page,'post'=>$post,'category'=>$category);
	   return $ids;
	}
	
	// function to delete list of posts/pages/categories under a selected campaign
	function delete_ids($pid)

	{

	   $sql="Delete from infobar_link_pages where campaign_id=$pid";

	   $result=mysql_query($sql);

	   $sql="Delete from infobar_exclude_pages where campaign_id=$pid";

	   $result=mysql_query($sql);

	}
	
	function check_magic_quotes()
	{
	  //if ( get_magic_quotes_gpc() ) 
		//{
			$_POST      = array_map( 'stripslashes_deep', $_POST );
			$_GET       = array_map( 'stripslashes_deep', $_GET );
			$_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
			$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
			
		//}
		
	}
}   
  // End Class

 global $infobar_object;

  $infobar_object = new wpinfobar();
  //called when plugin activated
  register_activation_hook( __FILE__, 'activate_wpinfobar' );
    
  //  called when plugin is deleted
  if ( function_exists('register_uninstall_hook') )
  {
     //uncomment if u want do delete all table while deleting files
    //register_uninstall_hook(__FILE__, 'uninstall_wpinfobar');
   }
   
  //create plugin tables if not exits
  function activate_wpinfobar()
  {
        global $infobar_object;
		
        if (!$infobar_object->check_table_existance('infobar_campaigns'))
			{
			   $infobar_object->install_tables_infobar_campaigns();
			}
			else
			{
			   //$array=array('link_target tinyint(4) NOT NULL','last_shown timestamp NOT NULL')
			    $infobar_object->alter_table();
			  
			}

		  if (!$infobar_object->check_table_existance('infobar_variants'))

				$infobar_object->install_tables_infobar_variants();

		if (!$infobar_object->check_table_existance('infobar_session'))

				$infobar_object->install_tables_infobar_session(); 

		if (!$infobar_object->check_table_existance('infobar_link_pages'))

				$infobar_object->install_tables_infobar_link_pages();
				
		if (!$infobar_object->check_table_existance('infobar_exclude_pages'))

				$infobar_object->install_tables_infobar_exclude_pages();
  }

  // delete plugin tables
  function uninstall_wpinfobar()
  {
        global $infobar_object;
		
        if ($infobar_object->check_table_existance('infobar_campaigns'))

			   $infobar_object->uninstall_tables('infobar_campaigns');

		  if ($infobar_object->check_table_existance('infobar_variants'))

				$infobar_object->uninstall_tables('infobar_variants');

		if ($infobar_object->check_table_existance('infobar_session'))

				$infobar_object->uninstall_tables('infobar_session');

		if ($infobar_object->check_table_existance('infobar_link_pages'))

				$infobar_object->uninstall_tables('infobar_link_pages');
				
		if ($infobar_object->check_table_existance('infobar_exclude_pages'))

				$infobar_object->uninstall_tables('infobar_exclude_pages');
  }
 ?>