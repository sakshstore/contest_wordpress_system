<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}





class AistoreContest{
    
    
// get contents feecccc
    public function get_contest_fee($amount )
{
return (get_option('contest_create_fee') / 100) * $amount;
  
}




      // create contents System
      
public static function aistore_contest()
{ 
   
 
 global $wpdb;   
      
if ( !is_user_logged_in() ) {
    
   return  do_shortcode( '[woocommerce_my_account]' );
    
    
   
}

  

$wallet = new AistoreWallet();
$user_id=get_current_user_id();


if(isset($_POST['submit']) and $_POST['action']=='contest' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' );
   exit;
} 




$title=sanitize_text_field($_REQUEST['title']);

 
$amount=intval($_REQUEST['amount']);


$currency=sanitize_text_field($_REQUEST['currency']);
$term_condition=sanitize_text_field(htmlentities($_REQUEST['term_condition']));
 $ends_date=sanitize_text_field($_REQUEST['ends_date']);
 
$contest_holder_name=sanitize_text_field($_REQUEST['contest_holder_name']);
$comapny_name=sanitize_text_field($_REQUEST['comapny_name']);
$comapny_slogan=sanitize_text_field($_REQUEST['comapny_slogan']);
$industry_type=sanitize_text_field($_REQUEST['industry_type']);
 
 

$contest_fee=$this->get_contest_fee($amount);

   
    $new_amount=$amount-$contest_fee ;
      


$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}contest ( title, amount,user_id,term_condition,contest_fee  ,currency,end_date,contest_holder_name,comapny_name,comapny_slogan,industry_type,created_by) VALUES ( %s, %d, %s ,%s ,%s,%s,%s,%s ,%s,%s,%s,%s)", array( $title, $new_amount, $user_id  ,$term_condition ,$contest_fee,$currency,$ends_date,$contest_holder_name,$comapny_name,$comapny_slogan,$industry_type,$user_id) ) );

$cid = $wpdb->insert_id;
$user_login = get_the_author_meta( 'user_login',$user_id );

	 $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id') ,
	'eid'=> $cid,
), home_url() ) );
  // test notification 
   
  
  	$n=array();
	$n['message']="Contest Created Successfully";
	$n['user_login']=$user_login;
	$n['type']="success";
	$n['url']=$details_contest_page_id_url;
	
	$n['user_id']=$user_id;
	
	aistore_notification_new($n);
	

	
	// notification test end
	
  $upload_dir = wp_upload_dir();
  

        if ( ! empty( $upload_dir['basedir'] ) ) {
            
            
            $user_dirname = $upload_dir['basedir'].'/documents/'.$cid;
            if ( ! file_exists( $user_dirname ) ) {
                wp_mkdir_p( $user_dirname );
            }
 
            $filename = wp_unique_filename( $user_dirname, $_FILES['file']['name'] );
            move_uploaded_file(sanitize_text_field($_FILES['file']['tmp_name']), $user_dirname .'/'. $filename);
            
            $image= $upload_dir['baseurl'].'/documents/'.$cid.'/'.$filename;
            
            // save into database  $image
            
                     

$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}contest_documents ( eid, documents,user_id,documents_name) VALUES ( %d,%s,%d,%s)", array( $cid,$image,$user_id,$filename) ) );
        }
        
        else{
            ?>
            <p> <?php  _e( 'Note : We accept only jpg, png, jpeg images', 'aistore' ) ?></p><?php
        
        }
        




$Payment_details = __( 'Payment transaction for the content id', 'aistore' );

 $details=$Payment_details.$cid ; 
 
 
$wallet->aistore_debit($user_id,$amount,$currency,$details);
$admin_id=get_option('escrow_user_id');
$wallet->aistore_credit($admin_id,$contest_fee ,$currency,$details);
 $wallet->aistore_credit($admin_id,$new_amount,$currency,$details);


?>
<meta http-equiv="refresh" content="0; URL=<?php echo esc_html($details_contest_page_id_url) ; ?>" /> 

<?php

}
else{
?>
    
    <form method="POST" action="" name="contest" enctype="multipart/form-data"> 

<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>

                
                 
<label for="title"><?php   _e('Title', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="title" name="title" required><br><br>
  
 
	  <label for="contest_holder_name"><?php   _e( 'Contest Holder Name', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="contest_holder_name" name="contest_holder_name"  required><br><br>
  
  <label for="comapny_name"><?php   _e( 'Company Name', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="comapny_name" name="comapny_name"  required><br><br>
  
    <label for="comapny_slogan"><?php   _e( 'Company Slogan', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="comapny_slogan" name="comapny_slogan"  required><br><br>
  
  <label for="industry_type"><?php   _e( 'Industry Type', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="industry_type" name="industry_type"  required><br><br>
  <!--//-->
  

  <label for="amount"><?php   _e( 'Amount', 'aistore' ); ?></label><br>
  
  <input class="input" type="number" id="amount" name="amount" min="1" max="10000" required><br><br>
 
  <label><?php _e( 'Currency:', 'aistore' ) ;?></label>
<br>
 <select name="currency">
	<option value="USD" selected="selected"><?php _e( 'United States Dollars:', 'aistore' ) ;?></option>
	<option value="EUR"><?php _e( 'Euro:', 'aistore' ) ;?></option>
	<option value="GBP"><?php _e( 'United Kingdom Pounds:', 'aistore' ) ;?></option>
	<option value="DZD"><?php _e( 'Algeria Dinars', 'aistore' ) ;?></option>
	<option value="ARP"><?php _e( 'Argentina Pesos', 'aistore' ) ;?></option>
	<option value="AUD"><?php _e( 'Australia Dollars', 'aistore' ) ;?></option>
	<option value="ATS"><?php _e( 'Austria Schillings:', 'aistore' ) ;?></option>
	<option value="BSD"><?php _e( 'Bahamas Dollars:', 'aistore' ) ;?></option>
	<option value="BBD"><?php _e( 'Barbados Dollars:', 'aistore' ) ;?></option>
	<option value="BEF"><?php _e( 'Belgium Francs:', 'aistore' ) ;?></option>
	<option value="BMD"><?php _e( 'Bermuda Dollars:', 'aistore' ) ;?></option>
	<option value="BRR"><?php _e( 'Brazil Real:', 'aistore' ) ;?></option>
	<option value="BGL"><?php _e( 'Bulgaria Lev:', 'aistore' ) ;?></option>
	<option value="CAD"><?php _e( 'Canada Dollars:', 'aistore' ) ;?></option>
	<option value="CLP"><?php _e( 'Chile Pesos:', 'aistore' ) ;?></option>
	<option value="CNY"><?php _e( 'China Yuan Renmimbi:', 'aistore' ) ;?></option>
	<option value="CYP"><?php _e( 'Cyprus Pounds:', 'aistore' ) ;?></option>
	<option value="CSK"><?php _e( 'Czech Republic Koruna:', 'aistore' ) ;?></option>
	<option value="DKK"><?php _e( 'Denmark Kroner:', 'aistore' ) ;?></option>
	<option value="NLG"><?php _e( 'Dutch Guilders:', 'aistore' ) ;?></option>
	<option value="XCD"><?php _e( 'Eastern Caribbean Dollars:', 'aistore' ) ;?></option>
	<option value="EGP"><?php _e( 'Egypt Pounds:', 'aistore' ) ;?></option>
	<option value="FJD"><?php _e( 'Fiji Dollars:', 'aistore' ) ;?></option>
	<option value="FIM"><?php _e( 'Finland Markka:', 'aistore' ) ;?></option>
	<option value="FRF"><?php _e( 'France Francs:', 'aistore' ) ;?></option>
	<option value="DEM"><?php _e( 'Germany Deutsche Marks:', 'aistore' ) ;?></option>
	<option value="XAU"><?php _e( 'Gold Ounces:', 'aistore' ) ;?></option>
	<option value="GRD"><?php _e( 'Greece Drachmas:', 'aistore' ) ;?></option>
	<option value="HKD"><?php _e( 'Hong Kong Dollars:', 'aistore' ) ;?></option>
	<option value="HUF"><?php _e( 'Hungary Forint:', 'aistore' ) ;?></option>
	<option value="ISK"><?php _e( 'Iceland Krona:', 'aistore' ) ;?></option>
	<option value="INR"><?php _e( 'India Rupees:', 'aistore' ) ;?></option>
	<option value="IDR"><?php _e( 'Indonesia Rupiah:', 'aistore' ) ;?></option>
	<option value="IEP"><?php _e( 'Ireland Punt:', 'aistore' ) ;?></option>
	<option value="ILS"><?php _e( 'Israel New Shekels:', 'aistore' ) ;?></option>
	<option value="ITL"><?php _e( 'Italy Lira:', 'aistore' ) ;?></option>
	<option value="JMD"><?php _e( 'Jamaica Dollars:', 'aistore' ) ;?></option>
	<option value="JPY"><?php _e( 'Japan Yen:', 'aistore' ) ;?></option>
	<option value="JOD"><?php _e( 'Jordan Dinar:', 'aistore' ) ;?></option>
	<option value="KRW"><?php _e( 'Korea (South) Won:', 'aistore' ) ;?></option>
	<option value="LBP"><?php _e( 'Lebanon Pounds:', 'aistore' ) ;?></option>
	<option value="LUF"><?php _e( 'Luxembourg Francs:', 'aistore' ) ;?></option>
	<option value="MYR"><?php _e( 'Malaysia Ringgit:', 'aistore' ) ;?></option>
	<option value="MXP"><?php _e( 'Mexico Pesos:', 'aistore' ) ;?></option>
	<option value="NLG"><?php _e( 'Netherlands Guilders:', 'aistore' ) ;?></option>
	<option value="NZD"><?php _e( 'New Zealand Dollars:', 'aistore' ) ;?></option>
	<option value="NOK"><?php _e( 'Norway Kroner:', 'aistore' ) ;?></option>
	<option value="PKR"><?php _e( 'Pakistan Rupees:', 'aistore' ) ;?></option>
	<option value="XPD"><?php _e( 'Palladium Ounces:', 'aistore' ) ;?></option>
	<option value="PHP"><?php _e( 'Philippines Pesos:', 'aistore' ) ;?></option>
	<option value="XPT"><?php _e( 'Platinum Ounces:', 'aistore' ) ;?></option>
	<option value="PLZ"><?php _e( 'Poland Zloty:', 'aistore' ) ;?></option>
	<option value="PTE"><?php _e( 'Portugal Escudo:', 'aistore' ) ;?></option>
	<option value="ROL"><?php _e( 'Romania Leu:', 'aistore' ) ;?></option>
	<option value="RUR"><?php _e( 'Russia Rubles:', 'aistore' ) ;?></option>
	<option value="SAR"><?php _e( 'Saudi Arabia Riyal:', 'aistore' ) ;?></option>
	<option value="XAG"><?php _e( 'Silver Ounces:', 'aistore' ) ;?></option>
	<option value="SGD"><?php _e( 'Singapore Dollars:', 'aistore' ) ;?></option>
	<option value="SKK"><?php _e( 'Slovakia Koruna:', 'aistore' ) ;?></option>
	<option value="ZAR"><?php _e( 'South Africa Rand:', 'aistore' ) ;?></option>
	<option value="KRW"><?php _e( 'South Korea Won:', 'aistore' ) ;?></option>
	<option value="ESP"><?php _e( 'Spain Pesetas:', 'aistore' ) ;?></option>
	<option value="XDR"><?php _e( 'Special Drawing Right (IMF):', 'aistore' ) ;?></option>
	<option value="SDD"><?php _e( 'Sudan Dinar:', 'aistore' ) ;?></option>
	<option value="SEK"><?php _e( 'Sweden Krona:', 'aistore' ) ;?></option>
	<option value="CHF"><?php _e( 'Switzerland Francs:', 'aistore' ) ;?></option>
	<option value="TWD"><?php _e( 'Taiwan Dollars:', 'aistore' ) ;?></option>
	<option value="THB"><?php _e( 'Thailand Baht:', 'aistore' ) ;?></option>
	<option value="TTD"><?php _e( 'Trinidad and Tobago Dollars:', 'aistore' ) ;?></option>
	<option value="TRL"><?php _e( 'Turkey Lira:', 'aistore' ) ;?></option>
	<option value="VEB"><?php _e( 'Venezuela Bolivar:', 'aistore' ) ;?></option>
	<option value="ZMK"><?php _e( 'Zambia Kwacha:', 'aistore' ) ;?></option>
	<option value="EUR"><?php _e( 'Euro:', 'aistore' ) ;?></option>
	<option value="XCD"><?php _e( 'Eastern Caribbean Dollars:', 'aistore' ) ;?></option>
	<option value="XDR"><?php _e( 'Special Drawing Right (IMF):', 'aistore' ) ;?></option>
	<option value="XAG"><?php _e( 'Silver Ounces:', 'aistore' ) ;?></option>
	<option value="XAU"><?php _e( 'Gold Ounces:', 'aistore' ) ;?></option>
	<option value="XPD"><?php _e( 'Palladium Ounces:', 'aistore' ) ;?></option>
	<option value="XPT"><?php _e( 'Platinum Ounces:', 'aistore' ) ;?></option>
</select><br><br>



	  <label for="ends_date"><?php   _e( 'Ends Date', 'aistore' ); ?></label><br>
  
  <input class="input" type="date" id="ends_date" name="ends_date"  required><br><br>
	

  
  
   <label for="term_condition"> <?php  _e( 'Description', 'aistore' ) ?></label><br>
   
   



  
  <?php
  
$content   = '';
$editor_id = 'term_condition';

 
   $settings = array(
    'tinymce'       => array(
        'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright   ',
        'toolbar2'      => '',
        'toolbar3'      => ''
       
   
      ),   
         'textarea_rows' => 1 ,
    'teeny' => true,
    'quicktags' => false,
     'media_buttons' => false 
);



wp_editor( $content, $editor_id,$settings);
?>
  



<br><br>

	<label for="documents"><?php  _e( 'Documents', 'aistore' ) ?>: </label>
     <input type="file" name="file"  required /><br>
    


	
	<br><br>
<input 
 type="submit" class="btn" name="submit" value="<?php  _e( 'Make Payment', 'aistore' ) ?>"/>
<input type="hidden" name="action" value="contest" />
</form> 
<?php
}
 

}


// public contest list

public static function aistore_contest_list_page(){
     global $wpdb;   
	 
      ob_start();  
      

                 
$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'contest WHERE status="approved" and  end_date >= NOW()');

 if($results==null)
	{
	      echo "<div class='no-result'>";
	      
	     _e( 'contest List Not Found', 'aistore' ); 
	  echo "</div>";
	}
	else{
	    
	    
    
     
    foreach($results as $row):
  
	 
	 $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id'),
    'eid' => $row->id,
), home_url() ) ); 

 ?>
 
 
       
	 <div class="col-md-4 " >


  <div class="card ">
      
    <a href="<?php echo esc_url($details_contest_page_id_url); ?>" >  
    <br>
    
 <?php
 printf(__( "# : %s", 'aistore' ),$row->id." ".$row->title."<br>"); ?> 
    
    
    </a><br><br>
    
    
    
    <p class="card-text">  <?php 
	printf(__( "Amount: %s", 'aistore' ),number_format($row->amount) ." ".  $row->currency); ?><br />  
   <?php printf(__( "Contest Ends In : %s", 'aistore' ),$row->end_date);?>

  
  <?php 
    
   $contest_entries = $wpdb->get_row($wpdb->prepare( "SELECT count(*) as contest_entries FROM {$wpdb->prefix}contest_documents WHERE id=%s ",$row->id));

 
    
 
  ?><br />

   <?php printf(__( "Submitted Entries: %s", 'aistore' ),$contest_entries->contest_entries);?>
  </p>
  
<hr />
  </div>
</div>
    
		 
		   

           
    <?php endforeach;


 return ob_get_clean();   

}
	

}

//contest list

public static function contest_list(){
     global $wpdb;   
	 
      ob_start();  
      
      	 
$user_id = get_current_user_id();


                 
                 
$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'contest WHERE status="approved" and  end_date >= NOW()');

 if($results==null)
	{
	      echo "<div class='no-result'>";
	      
	     _e( 'contest List Not Found', 'aistore' ); 
	  echo "</div>";
	}
	else{
	    
	    
    
     
    foreach($results as $row):
  
	 
	 $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id'),
    'eid' => $row->id,
), home_url() ) ); 

 ?>
 
 
       
	 <div class="col-md-4 " >


  <div class="card ">
      
    <a href="<?php echo esc_url($details_contest_page_id_url); ?>" class=" ">  
    <br>
    

    
     <?php printf(__( "# : %s", 'aistore' ),$row->id." ".$row->title); ?>
    
    </a><br><br>
    
    
    
    <p class="card-text"> 
<?php printf(__( "Amount : %s", 'aistore' ),number_format($row->amount)." ".$row->currency);?><br />  
  <?php  printf(__( "Contest Ends In : %s", 'aistore' ),$row->end_date);?>
  
  <?php 
    
   $contest_entries = $wpdb->get_row($wpdb->prepare( "SELECT count(*) as contest_entries FROM {$wpdb->prefix}contest_documents WHERE id=%s ",$row->id));

 
    
 
  ?><br />
    <?php  printf(__( "Submitted Entries: %s", 'aistore' ),$contest_entries->contest_entries);?>

  </p>
  
<hr />
  </div>
</div>
    
		 
		   

           
    <?php endforeach;


 return ob_get_clean();   

}
	

}



// your contest List

 public static function aistore_contest_list(){
     
     if ( !is_user_logged_in() ) {
    
   return  do_shortcode( '[aistore_contest_list_page]' );
    
    
   
}

	  global $wpdb;   
	 
      ob_start();  

   // incorrect  query
   
   
  
   
   $results1 = $wpdb->get_row(' 
    SELECT count(*) as total_contest FROM '.$wpdb->prefix.'contest WHERE status="approve" and  end_date >= NOW() ' );
    

 printf(__( "Active Contests: %s", 'aistore' ),$results1->total_contest);
  

	 
$user_id = get_current_user_id();


             
   $results = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}contest WHERE created_by=%d ",$user_id));
   
 
 if($results==null)
	{
	    
	    
	      echo "<div class='no-result'>";
	      
	     _e( 'contest List Not Found', 'aistore' ); 
	  echo "</div>";
	}
	
	
	else{
   

     
  ?>
 
 
 

	 <div class="row " >
    <?php 
    
     
    foreach($results as $row):
  
	 
	 $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id'),
    'eid' => $row->id,
), home_url() ) ); 

 ?>
 
 
       
	 <div class="col-md-4 " >


  <div class="card ">
      
    <a href="<?php echo esc_url($details_contest_page_id_url); ?>" class=" ">  
    <br>
    

     <?php printf(__( "# : %s", 'aistore' ),$row->id." ".$row->title); ?>
    
    </a><br><br>
    
    
    
    <p class="card-text"> 


  <?php printf(__( "Amount : %s", 'aistore' ),number_format($row->amount)." ".$row->currency);?><br />
    <?php  printf(__( "Contest Ends In : %s", 'aistore' ),$row->end_date);?>
  
  <?php 
    
   $contest_entries = $wpdb->get_row($wpdb->prepare( "SELECT count(*) as contest_entries FROM {$wpdb->prefix}contest_documents WHERE id=%s ",$row->id));

 
    
 
  ?><br />
    <?php  printf(__( "Submitted Entries: %s", 'aistore' ),$contest_entries->contest_entries);?>
 
  </p>
    <?php
     

    // need to remove from here
    if(isset($_POST['submit']) and $_POST['action']=='delete_contest' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' );
   exit;
} 

$document_id=sanitize_text_field($_REQUEST['document_id']);
$wpdb->delete( $wpdb->prefix.'contest', array( 'id' => $document_id, 'created_by'=>$user_id) );
}
else{


 
    if($row->created_by==$user_id){
        ?>
    
     <form method="POST" action="" name="delete_contest" enctype="multipart/form-data"> 

<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="document_id" value="<?php echo $row->id ; ?>">
<input 
 type="submit" class="btn btn-primary btn-sm" name="submit" value="&#128465;"/>	
<input type="hidden" name="action" value="delete_contest" />
</form>

<?php
}
}
?>

<hr />
  </div>
</div>
    
		 
		   

           
    <?php endforeach;


 return ob_get_clean();   


}

}




// contest Details

public static function aistore_contest_detail( ){
         
         
         if ( !is_user_logged_in() ) {
    
  return do_shortcode( '[woocommerce_my_account]' );
}



 global $wpdb;   
 
 
    if(isset($_POST['submit']) and $_POST['action']=='delete_image' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' );
   exit;
} 

$document_id=sanitize_text_field($_REQUEST['document_id']);
$wpdb->delete( $wpdb->prefix.'contest_documents', array( 'id' => $document_id) );
}

 
   if(isset($_POST['submit']) and $_POST['action']=='winner_contest')
{


    global $wpdb;  
    $user_id= get_current_user_id();
    $entry_id=sanitize_text_field($_REQUEST['eid']);
$contest_id=sanitize_text_field($_REQUEST['cid']);

$contest_entries = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_documents WHERE id=%d", $entry_id ));

$entry_user_id=$contest_entries->user_id;

$contest_escrow = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest WHERE id=%d and created_by=%d", $contest_id,$user_id ));


 $receiver_email = get_the_author_meta( 'user_email', $entry_user_id );
$amount=$contest_escrow->amount;
$title=$contest_escrow->title;
$term_condition=$contest_escrow->term_condition;
 

$escrow=new AistoreEscrowSystem();
$res=$escrow->add_esrow($amount,$user_id,$receiver_email,$title,$term_condition);



$user_login = get_the_author_meta( 'user_login',$user_id );


$user_login_winner = get_the_author_meta( 'user_login',$entry_user_id );


$escrow_id=$res['eid'];


	 $details_escrow_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_escrow_page_id'),
    'eid' => $escrow_id,
), home_url() ) ); 

   	$n=array();
	$n['message']="Escrow Created Successfully4";
	$n['user_login']=$user_login;
	$n['type']="success";
	$n['url']=$details_escrow_page_id_url;
	
	$n['user_id']=$user_id;
	
	$a=array();
	$a['message']="Escrow Created Successfully5";
	$a['user_login']=$user_login_winner;
	$a['type']="success";
	$a['url']=$details_escrow_page_id_url;
	
	$a['user_id']=$entry_user_id;
	
	aistore_notification_new($n);
	aistore_notification_new($a);
	
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}contest
    SET status = '%s'  WHERE id = '%d' ", 
   'closed' , $contest_id) );
	
?>
<meta http-equiv="refresh" content="0; URL=<?php echo esc_html($details_escrow_page_id_url) ; ?>" /> 

<?php

}




else{


   if(!sanitize_text_field($_REQUEST['eid'])){
    
    	 $add_contest_page_url  =  esc_url( add_query_arg( array(
    'page_id' => get_option('add_contest_page_id') ,
), home_url() ) );

    ?>
    
   
<meta http-equiv="refresh" content="0; URL=<?php echo esc_html($add_contest_page_url) ; ?>" /> 
  
 <?php   }
 
 
 
 
 $user_id= get_current_user_id();
	 
$email_id = get_the_author_meta( 'user_email',$user_id );
 

ob_start();

    $eid=sanitize_text_field($_REQUEST['eid']);
    
     echo aistore_echo_notification() ;
    
    $results1 = $wpdb->get_row($wpdb->prepare( "SELECT count(*) as total_entries  FROM {$wpdb->prefix}contest_documents WHERE eid=%s and user_id!=%d  ",$eid, $user_id ));
    

  printf(__( "<br>Submitted Entries : %s", 'aistore' ),$results1->total_entries); 

$contest = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}contest WHERE id=%s ",$eid ));
  

   printf(__( "<br>Contest Ends In  : %s", 'aistore' ),$contest->end_date); 

 ?><br>
	  <div><br>
	      
	      
	      
	      
	      
	      
	      
	      <div class="alert alert-success" role="alert">
 <strong> 
 <?php printf(__( "Contest Status  %s", 'aistore' ),$contest->status); ?></strong>
  </div>
	  
	  
	  
	      <?php
	 printf(__( "# %s", 'aistore' ), $contest->id." ".$contest->title);
     //echo "<strong>#". $contest->id ." ".$contest->title ."</strong><br>";
       printf(__( "<strong>Contest Holder Name</strong> <br> %s", 'aistore' ),html_entity_decode($contest->contest_holder_name)."<br><br>");
       
         printf(__( "<strong>Company Name</strong><br>  %s", 'aistore' ),html_entity_decode($contest->comapny_name)."<br><br>");
         
           printf(__( "<strong>Company Slogan</strong><br>  %s", 'aistore' ),html_entity_decode($contest->comapny_slogan)."<br><br>");
           
             printf(__( "<strong>Industry Type</strong><br>  %s", 'aistore' ),html_entity_decode($contest->industry_type)."<br><br>");
     
  printf(__( "<strong>Description</strong><br>  %s", 'aistore' ),html_entity_decode($contest->term_condition)."<br><br>");


 echo "<hr />";

  $object=new AistoreContest();



 
$object->aistore_contest_file_uploads($contest);

    
    ?>
</div>

<?php
     return ob_get_clean();  
}
}



// contest  file uploads

private	function aistore_contest_file_uploads($contest){
       global $wpdb;   
$eid=  $contest->id;
$created_by=$contest->created_by;
$user_id=get_current_user_id();
     	
     	
     	// we need two tables for this not all in one 
     	
   $contest_documents_login = $wpdb->get_results( 
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_documents WHERE user_id=%d and eid=%d", $contest->user_id,$eid));
?>
 <table class="table"><tr>
         <h3><?php   _e( 'Design Styles We Would Like To See:', 'aistore' ); ?></h3>
        <tr>
    <?php
    
    foreach($contest_documents_login as $row):
        ?>
        <td class="box"> 
     <img   src="<?php echo $row->documents; ?>" class="img-fluid"></td>
     	    
     	    <?php
     
    endforeach;
    ?>
    </tr>
    </table>
    
    <?php
    
    
    
 echo "<hr />";


   $contest_documents = $wpdb->get_results( 
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_documents WHERE eid=%d and user_id!=%d", $eid,$contest->user_id ));
 
  if(count($contest_documents)>1)
  { 
?>  <h3><?php   _e( 'Contest Entries:', 'aistore' ); ?></h3>
  <?php 
  }
 
 
    
    foreach($contest_documents as $row):
        	$user_login = get_the_author_meta( 'user_login', $row->user_id );
     
    ?>

	<div class="document_list">
<div class="box">
  <div >

			<span class="b"><?php   _e( 'Entry ID:', 'aistore' ); ?> #<?php echo  esc_attr($row->id);?> <br>
			
	<?php
	   if(isset($_POST['submit']) and $_POST['action']=='rating' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' );
   exit;
} 

$document_id=sanitize_text_field($_REQUEST['document_id']);
$document_rate=sanitize_text_field($_REQUEST['rate']);


   
      $q1=$wpdb->prepare("INSERT INTO {$wpdb->prefix}contest_rating ( document_id,rating,  user_id ,cid) VALUES ( %d, %d, %d ,%d) ", array(  $document_id,$document_rate, $user_id,$row->eid));
     $wpdb->query($q1);
     
    $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id') ,
	'eid'=> $row->eid,
), home_url() ) );


?>
<meta http-equiv="refresh" content="0; URL=<?php echo esc_html($details_contest_page_id_url) ; ?>" /> 

<?php
}

    
    ?>
<img class="img-fluid " src="<?php echo $row->documents; ?>" ><br>
<!-- RATING - Form -->

    
    <?php
        $this->aistore_contest_print_rating($row);
        
    
    $this->aistore_contest_rating_form($row);
    
    if($created_by==$user_id){
    $this->aistore_delete_contest_document($row);
  $this->aistore_contest_choose_him_as_winner_button($contest,$row);
    }
    
echo "<hr/>";
    endforeach;
    
    
    ?>
     
<br>
	   <div>



<?php

  
if($contest->status=='approved'){
    
?>



<label for="my_checkbox"><h3><u> <?php _e( 'Submit Entry', 'aistore' );  ?></u></h3></label><br>



<input type="checkbox" id="my_checkbox" style="display:none;">
<div id="hidden">
	<label for="documents"> <?php   _e( 'Documents', 'aistore' ); ?> : </label>
<form  method="post"  action="<?php echo esc_url(admin_url('admin-ajax.php').'?action=custom_action&eid='.$eid); ?>" class="dropzone" id="dropzone">
    <?php 
wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' );
?>
  <div class="fallback">
    <input id="file" name="file" type="file"  multiple   />
    <input type="hidden" name="action" value="custom_action" type="submit"  />
  </div>

</form></div>


    <?php }
    ?>
     
     </div>
     <br>
     
     <?php 
      
      
}



function aistore_contest_rating_form($row)
{global $wpdb;

    
    ?>
    
    <form class="rating-form" action="" method="POST" name="rating">
<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="document_id" value="<?php echo esc_attr($row->id); ?>">
<select name="rate" id="rate" class="form-control">
     <option value="0" selected><?php _e( 'Select Rating', 'aistore' );  ?></option>
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
   <option value="5">5</option>
</select>
<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
<input type="hidden" name="action" value="rating" />
</form>




 <?php 

}
 
 
 function aistore_contest_print_rating($row)
 {
     
 global $wpdb;
$rating = $wpdb->get_results( "SELECT avg(rating) as rate FROM {$wpdb->prefix}contest_rating WHERE document_id = '".   $row->id."' order by id desc  limit 1"   );


  foreach($rating as $row1):

         endforeach;
    
 ?>
<?php if(round($row1->rate)==1){
?> <strong style="color:orange; ">*</strong>   
<?php }
 if(round($row1->rate)==2){
?> <strong style="color:orange; ">* *</strong>   
<?php }
 if(round($row1->rate)==3){
?> <strong style="color:orange; ">* * *</strong>   
<?php }

 if(round($row1->rate)==4){
?> <strong style="color:orange; ">* * * *</strong>   
<?php }

 if(round($row1->rate)==5){
?> <strong style="color:orange; ">* * * * *</strong>   
<?php }
 printf(__( "Submission # %s", 'aistore' ),$row->id );?>
				</span>
						</div> 
</div>


<?php
 }
 
 
 function aistore_delete_contest_document($entry){
      
 
 
?>
    
     <form method="POST" action="" name="delete_image" enctype="multipart/form-data"> 

<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="document_id" value="<?php echo esc_attr($entry->id) ; ?>">
<input 
 type="submit" class="btn btn-primary btn-sm" name="submit" value="<?php _e( 'Delete', 'aistore' );  ?>"/>	
<input type="hidden" name="action" value="delete_image" />
</form>

<?php

}

 
	
function aistore_contest_choose_him_as_winner_button($contest,$entry)
{
     
      
  
 
?>
<br>
  <form class="rating-form" action="" method="POST" name="winner_contest" enctype="multipart/form-data">
<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="eid" value="<?php printf(__( "%s", 'aistore' ),$entry->id);?>">
<input type="hidden" name="cid" value="<?php printf(__( "%s", 'aistore' ),$contest->id); ?>">
<input type="submit" class="btn" name="submit" value="<?php _e( 'Choose him as winner', 'aistore' );  ?>"/>
<input type="hidden" name="action" value="winner_contest" />
</form>

<?php 
 
    
}







	
	
	


}



 add_action( 'wp_ajax_custom_action', 'aistore_contest_upload_file' );


function aistore_contest_upload_file() {
    
 global $wpdb;   
  $eid=sanitize_text_field($_REQUEST['eid']);

$contest = $wpdb->get_row($wpdb->prepare( "SELECT count(id) as count FROM {$wpdb->prefix}contest WHERE id=%s ",$eid ));

  $c=(int)$contest->count;
 if($c>0){
     
    
 if ( isset($_POST['aistore_nonce']) ) {
        $upload_dir = wp_upload_dir();
 
        if ( ! empty( $upload_dir['basedir'] ) ) {
            $user_dirname = $upload_dir['basedir'].'/documents/'.$eid;
            if ( ! file_exists( $user_dirname ) ) {
                wp_mkdir_p( $user_dirname );
            }

            $filename = wp_unique_filename( $user_dirname, $_FILES['file']['name'] );
          
         printf(__( "filename :%s", 'aistore' ),$filename);
            
                
            move_uploaded_file(sanitize_text_field($_FILES['file']['tmp_name']), $user_dirname .'/'. $filename);
            
            
            $image=$upload_dir['baseurl'].'/documents/'.$eid.'/'.$filename;
//             // save into database $image;
      

$user_id=get_current_user_id();
$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}contest_documents ( eid, documents,user_id,documents_name) VALUES ( %d,%s,%d,%s)", array( $eid,$image,$user_id,$filename) ) );

        }
    }

  wp_die();
}
else{
     _e( 'Unauthorized user', 'aistore' ); 
}


}
