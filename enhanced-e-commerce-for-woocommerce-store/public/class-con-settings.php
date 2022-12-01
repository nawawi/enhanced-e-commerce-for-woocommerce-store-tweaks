<?php
class Con_Settings{
	protected $ga_EC;
  //content grouping start
  protected $ga_optimize_id;
  protected $ga_CG;
  //content grouping end
  public $tvc_eeVer = PLUGIN_TVC_VERSION;
  protected $ga_LC;
  protected $ga_Dname;

  protected $ga_id;
  protected $gm_id;
  protected $google_ads_id;    
  protected $google_merchant_id;

  protected $tracking_option;
  
  protected $ga_ST;
  protected $ga_eeT;
  protected $ga_gUser;

  protected $ga_imTh;

  protected $ga_IPA; 
  //protected $ga_OPTOUT; 

  protected $ads_ert;
  protected $ads_edrt;
  protected $ads_tracking_id;      
  
  protected $ga_PrivacyPolicy;    
  
  protected $ga_gCkout;    
  protected $ga_DF; 
  protected $tvc_options; 
  protected $TVC_Admin_Helper; 
  protected $remarketing_snippet_id;
  protected $remarketing_snippets;
  protected $conversio_send_to;
  protected $ee_options;
  protected $fb_pixel_id;
  protected $c_t_o; //custom_tracking_options
  protected $tracking_method;

  protected $microsoft_ads_pixel_id;
  protected $twitter_ads_pixel_id;
  protected $pinterest_ads_pixel_id;
  protected $snapchat_ads_pixel_id;
  protected $tiKtok_ads_pixel_id;

  protected $want_to_use_your_gtm;
  protected $use_your_gtm_id;
	public function __construct(){
		$this->TVC_Admin_Helper = new TVC_Admin_Helper();
    add_action('wp_head', array($this,'con_set_yith_current_currency'));
		$this->ga_CG = $this->get_option('ga_CG') == "on" ? true : false; // Content Grouping
    $this->ga_optimize_id = sanitize_text_field($this->get_option("ga_optimize_id"));
    $this->ee_options = $this->TVC_Admin_Helper->get_ee_options_settings();

    $this->tracking_method = sanitize_text_field($this->get_option("tracking_method"));

    $this->ga_Dname = "auto";
    $this->ga_id = sanitize_text_field($this->get_option("ga_id"));
    $this->ga_eeT = sanitize_text_field($this->get_option("ga_eeT"));
    $this->ga_ST = sanitize_text_field($this->get_option("ga_ST")); //add_gtag_snippet
    $this->gm_id = sanitize_text_field($this->get_option("gm_id")); //measurement_id
    $this->google_ads_id = sanitize_text_field($this->get_option("google_ads_id"));
    $this->ga_excT = sanitize_text_field($this->get_option("ga_excT")); //exception_tracking
    $this->exception_tracking = sanitize_text_field($this->get_option("exception_tracking")); //exception_tracking
    $this->ga_elaT = sanitize_text_field($this->get_option("ga_elaT")); //enhanced_link_attribution_tracking
    $this->google_merchant_id = sanitize_text_field($this->get_option("google_merchant_id"));
    $this->tracking_option = sanitize_text_field($this->get_option("tracking_option"));
    $this->ga_gCkout = sanitize_text_field($this->get_option("ga_gCkout") == "on" ? true : false); //guest checkout
    $this->ga_gUser = sanitize_text_field($this->get_option("ga_gUser") == "on" ? true : false); //guest checkout
    $this->ga_DF = sanitize_text_field($this->get_option("ga_DF") == "on" ? true : false);
    $this->ga_imTh = sanitize_text_field($this->get_option("ga_Impr") == "" ? 6 : $this->get_option("ga_Impr"));
    //$this->ga_OPTOUT = sanitize_text_field($this->get_option("ga_OPTOUT") == "on" ? true : false); //Google Analytics Opt Out
    $this->ga_PrivacyPolicy = sanitize_text_field($this->get_option("ga_PrivacyPolicy") == "on" ? true : false);
    $this->ga_IPA = sanitize_text_field($this->get_option("ga_IPA") == "on" ? true : false); //IP Anony.
    $this->ads_ert = get_option('ads_ert'); //Enable remarketing tags
    $this->ads_edrt = get_option('ads_edrt'); //Enable dynamic remarketing tags
    $this->ads_tracking_id = sanitize_text_field(get_option('ads_tracking_id'));    
    $this->google_ads_conversion_tracking = get_option('google_ads_conversion_tracking');
    $this->ga_EC = get_option("ga_EC");
    $this->conversio_send_to = get_option('ee_conversio_send_to');

    $remarketing = unserialize(get_option('ee_remarketing_snippets'));
    if(!empty($remarketing) && isset($remarketing['snippets']) && esc_attr($remarketing['snippets'])){
      $this->remarketing_snippets = base64_decode($remarketing['snippets']);
      $this->remarketing_snippet_id = sanitize_text_field(isset($remarketing['id'])?esc_attr($remarketing['id']):"");
    }

    /*pixels*/
    $this->fb_pixel_id = sanitize_text_field($this->get_option('fb_pixel_id'));
    $this->microsoft_ads_pixel_id = sanitize_text_field($this->get_option('microsoft_ads_pixel_id'));
    $this->twitter_ads_pixel_id = sanitize_text_field($this->get_option('twitter_ads_pixel_id'));
    $this->pinterest_ads_pixel_id = sanitize_text_field($this->get_option('pinterest_ads_pixel_id'));
    $this->snapchat_ads_pixel_id = sanitize_text_field($this->get_option('snapchat_ads_pixel_id'));
    $this->tiKtok_ads_pixel_id = sanitize_text_field($this->get_option('tiKtok_ads_pixel_id'));
    /* GTM*/
    $this->want_to_use_your_gtm = sanitize_text_field($this->get_option('want_to_use_your_gtm'));
    $this->use_your_gtm_id = sanitize_text_field($this->get_option('use_your_gtm_id'));
    $this->ga_LC = get_woocommerce_currency(); //Local Currency from Back end
    //$this->wc_version_compare("tvc_lc=" . json_encode(esc_js($this->ga_LC)) . ";");

    $this->c_t_o = $this->TVC_Admin_Helper->get_ee_options_settings();
	}

  public function con_set_yith_current_currency(){
    if ( in_array( "yith-multi-currency-switcher-for-woocommerce/init.php", apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
       $this->ga_LC = yith_wcmcs_get_current_currency_id();
    }
    ?>
   <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
      var tvc_lc = '<?php echo esc_js($this->ga_LC); ?>';
    </script>
    <?php   
  }

	public function get_option($key){
    if(empty($this->ee_options)){
      $this->ee_options = $this->TVC_Admin_Helper->get_ee_options_settings();
    }
    if(isset($this->ee_options[$key])){
      return $this->ee_options[$key];
    }
  }

  public function wc_version_compare($codeSnippet) {
    global $woocommerce;
    if (version_compare($woocommerce->version, "2.1", ">=")) {
      wc_enqueue_js($codeSnippet);
    } else {
      $woocommerce->add_inline_js($codeSnippet);
    }
  }
  public function get_selector_val_fron_array($obj, $key){    
    if( isset($obj[$key.'_val']) && $obj[$key.'_val'] && isset($obj[$key.'_type']) && $obj[$key.'_type'] == "id" ){
      return ",#".$obj[$key.'_val'];
    }else if( isset($obj[$key.'_val']) && $obj[$key.'_val'] && isset($obj[$key.'_type']) && $obj[$key.'_type'] == "class" ){
      $class_list = explode(",",$obj[$key.'_val']);
      if(!empty($class_list)){
        $class_selector = "";
        foreach($class_list as $class){
           $class_selector .= ",.".trim($class);
        }
        return $class_selector;
      }
      
    }
  }

  public function get_selector_val_from_array_for_gmt($obj, $key){    
    if( isset($obj[$key.'_val']) && $obj[$key.'_val'] && isset($obj[$key.'_type']) && $obj[$key.'_type'] == "id" ){
      return "#".$obj[$key.'_val'];
    }else if( isset($obj[$key.'_val']) && $obj[$key.'_val'] && isset($obj[$key.'_type']) && $obj[$key.'_type'] == "class" ){
      $class_list = explode(",",$obj[$key.'_val']);
      if(!empty($class_list)){
        $class_selector = "";
        foreach($class_list as $class){
           $class_selector .= ($class_selector)?",.".trim($class):".".trim($class);
        }
        return $class_selector;
      }
      
    }
  }

  public function disable_tracking($type) {
    if (is_admin() || "" == $type || current_user_can("manage_options")) {
      return true;
    }
  }
  public function tvc_get_order_with_url_order_key(){
    $_get = filter_input_array( INPUT_GET, FILTER_SANITIZE_STRING );      
    if ( isset( $_get['key'] ) ) {
      $order_key = $_get['key'];
      return wc_get_order( wc_get_order_id_by_order_key( $order_key ) );
    }    
  }
  public function tvc_get_order_from_query_vars(){
    global  $wp ;
    $order_id = absint( $wp->query_vars['order-received'] );        
    if ( $order_id && 0 != $order_id && wc_get_order( $order_id ) ) {
        return wc_get_order( $order_id );
    }   
  }
  public function tvc_get_order_from_order_received_page(){        
    if ( $this->tvc_get_order_from_query_vars() ) {
      return $this->tvc_get_order_from_query_vars();
    } else {          
      if ( $this->tvc_get_order_with_url_order_key() ) {
        return $this->tvc_get_order_with_url_order_key();
      } else {
        return false;
      }      
    }    
  }
}
?>