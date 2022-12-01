<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       tatvic.com
 * @since      1.0.0
 *
 * @package    Enhanced_Ecommerce_Google_Analytics
 * @subpackage Enhanced_Ecommerce_Google_Analytics/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Enhanced_Ecommerce_Google_Analytics
 * @subpackage Enhanced_Ecommerce_Google_Analytics/public
 * @author     Tatvic
 */
require_once(ENHANCAD_PLUGIN_DIR . 'public/class-con-settings.php');
class Enhanced_Ecommerce_Google_Analytics_Public extends Con_Settings{
    /**
     * Init and hook in the integration.
     *
     * @access public
     * @return void
     */
    //set plugin version
    protected $plugin_name;
    protected $version;
    
    public function __construct($plugin_name, $version) {
      parent::__construct();
      $this->gtm = new Con_GTM_Tracking($plugin_name, $version);
      $this->TVC_Admin_Helper = new TVC_Admin_Helper();
      $this->plugin_name = sanitize_text_field($plugin_name);
      $this->version  = sanitize_text_field($version);
      //$this->ee_options = $this->TVC_Admin_Helper->get_ee_options_settings();
      //$this->c_t_o = $this->TVC_Admin_Helper->get_ee_options_settings();
      $this->tvc_call_hooks();            
    }
    public function tvc_call_hooks(){
      /**
       * add global site tag js or settings
       **/
      if($this->tracking_method == "gtm"){
        add_action("wp_head", array($this->gtm, "begin_datalayer"));
        add_action("wp_head", array($this->gtm, "enqueue_scripts"));
      }else{
        add_action("wp_head", array($this, "enqueue_scripts"));
        add_action("wp_head", array($this, "ee_settings"));
      }
      add_action("wp_head", array($this, "add_google_site_verification_tag"),1);

      if($this->tracking_method == "gtm"){
        add_action("wp_footer", array($this->gtm, "add_gtm_data_layer"));
      }else{
        add_action("wp_footer", array($this, "t_products_impre_clicks"));
      }

      /**Bind Product data **/
      // product list collection method
      if(isset($this->c_t_o['tvc_product_list_data_collection_method']) && $this->c_t_o['tvc_product_list_data_collection_method']){
        if($this->tracking_method == "gtm"){
          add_action($this->c_t_o['tvc_product_list_data_collection_method'], array($this->gtm, "product_list_view"));
        }else{
          add_action($this->c_t_o['tvc_product_list_data_collection_method'], array($this, "bind_product_metadata"));
        }
      }else{
        if($this->tracking_method == "gtm"){
          add_action("woocommerce_after_shop_loop_item", array($this->gtm, "product_list_view"));
        }else{
          add_action("woocommerce_after_shop_loop_item", array($this, "bind_product_metadata"));
        }
      }

      //Thnak you page collection method
      $tvc_thankyou_data_collection_method = isset($this->c_t_o['tvc_thankyou_data_collection_method'])?$this->c_t_o['tvc_thankyou_data_collection_method']:"woocommerce_thankyou";
      if($tvc_thankyou_data_collection_method == "on_page"){
        if($this->tracking_method == "gtm"){
          add_action("wp_head", array($this->gtm, "product_thankyou_view"));
        }else{
          add_action("wp_head", array($this, "ecommerce_tracking_code"));
        }
      }else if($tvc_thankyou_data_collection_method){
        if($this->tracking_method == "gtm"){
          add_action($tvc_thankyou_data_collection_method, array($this->gtm, "product_thankyou_view"));
        }else{
          add_action($tvc_thankyou_data_collection_method, array($this, "ecommerce_tracking_code"));
        }
      } else{
        if($this->tracking_method == "gtm"){
          add_action("woocommerce_thankyou", array($this->gtm, "product_thankyou_view"));
        }else{
          add_action("woocommerce_thankyou", array($this, "ecommerce_tracking_code"));
        }
      }

      //product detail page collection method
      $tvc_product_detail_data_collection_method = isset($this->c_t_o['tvc_product_detail_data_collection_method'])?$this->c_t_o['tvc_product_detail_data_collection_method']:"woocommerce_after_single_product";
      if($tvc_product_detail_data_collection_method == "on_page"){
        if($this->tracking_method == "gtm"){
          add_action("wp_head", array($this->gtm, "product_detail_view"));
        }else{
          add_action("wp_head", array($this, "product_detail_view"));
          add_action("wp_footer", array($this, "single_add_to_cart"));
        }
      }else if($tvc_product_detail_data_collection_method){
        if($this->tracking_method == "gtm"){
          add_action($tvc_product_detail_data_collection_method, array($this->gtm, "product_detail_view"));
        }else{
          //product var init
          add_action($tvc_product_detail_data_collection_method, array($this, "product_detail_view"));     
          //event trigger
          add_action("woocommerce_after_add_to_cart_button", array($this, "single_add_to_cart"));
        }
      }else{
        if($this->tracking_method == "gtm"){
          add_action("woocommerce_after_single_product", array($this->gtm, "product_detail_view")); 
        }else{
          //product var init
          add_action("woocommerce_after_single_product", array($this, "product_detail_view"));     
          //event trigger
          add_action("woocommerce_after_add_to_cart_button", array($this, "single_add_to_cart"));
        }
      }
      if($this->tracking_method == "gtm"){
        add_action("woocommerce_after_cart", array($this->gtm, "product_cart_view"));
      }else{
        add_action("woocommerce_after_cart",array($this, "remove_cart_tracking"));
      } 
      
      //checkout step 1,2,3
      $tvc_checkout_data_collection_method = isset($this->c_t_o['tvc_checkout_data_collection_method'])?$this->c_t_o['tvc_checkout_data_collection_method']:"woocommerce_before_checkout_form";
      if($tvc_checkout_data_collection_method == "on_page"){
        if($this->tracking_method == "gtm"){
          add_action("wp_head", array($this->gtm, "checkout_step_view"));
        }else{
          add_action("wp_head", array($this, "checkout_step_1_tracking"));
          add_action("wp_head", array($this, "checkout_step_2_tracking"));
          add_action("wp_head", array($this, "checkout_step_3_tracking"));
        }
      }else if($tvc_checkout_data_collection_method){
        if($this->tracking_method == "gtm"){
          add_action($tvc_checkout_data_collection_method, array($this->gtm, "checkout_step_view"));
        }else{
          add_action($tvc_checkout_data_collection_method, array($this, "checkout_step_1_tracking"));
          add_action($tvc_checkout_data_collection_method, array($this, "checkout_step_2_tracking"));
          add_action($tvc_checkout_data_collection_method, array($this, "checkout_step_3_tracking"));
        }
      }else{
        if($this->tracking_method == "gtm"){
          add_action("woocommerce_before_checkout_form", array($this->gtm, "checkout_step_view"));
        }else{
          add_action("woocommerce_before_checkout_form", array($this, "checkout_step_1_tracking"));
          add_action("woocommerce_before_checkout_form", array($this, "checkout_step_2_tracking"));
          add_action("woocommerce_before_checkout_form", array($this, "checkout_step_3_tracking"));
        }
      }      
      
      //Add Dev ID
      add_action("wp_head", array($this, "add_dev_id"));
      add_action("wp_footer",array($this, "tvc_store_meta_data"));
    }
    public function add_google_site_verification_tag(){
        $TVC_Admin_Helper = new TVC_Admin_Helper();
        $ee_additional_data = $TVC_Admin_Helper->get_ee_additional_data();
        if(isset($ee_additional_data['add_site_varification_tag']) && isset($ee_additional_data['site_varification_tag_val']) && $ee_additional_data['add_site_varification_tag'] == 1 && $ee_additional_data['site_varification_tag_val'] !="" ){
            echo html_entity_decode(esc_html(base64_decode($ee_additional_data['site_varification_tag_val'])));
        }        
                        
    }
    /**
     * Get store meta data for trouble shoot
     * @access public
     * @return void
     */
    function tvc_store_meta_data() {
        //only on home page
        global $woocommerce;
        $google_detail = $this->TVC_Admin_Helper->get_ee_options_data();
        $sub_data = array();
        if(isset($google_detail['setting'])){
          $googleDetail = $google_detail['setting'];            
        }
        $tvc_sMetaData = array(
          'tvc_wcv' => esc_js($woocommerce->version),
          'tvc_wpv' => esc_js(get_bloginfo('version')),
          'tvc_eev' => esc_js($this->tvc_eeVer),
          'tvc_cnf' => array(
            't_ee' => esc_js($this->ga_eeT),
            't_df' => esc_js($this->ga_DF),
            't_gUser' => esc_js($this->ga_gUser),
            't_UAen' => esc_js($this->ga_ST),
            't_thr' => esc_js($this->ga_imTh),
            't_IPA' => esc_js($this->ga_IPA),
            //'t_OptOut' => esc_js($this->ga_OPTOUT),
            't_PrivacyPolicy' => esc_js($this->ga_PrivacyPolicy)
          ),
          'tvc_sub_data'=> array(
            'sub_id' =>esc_js(isset($googleDetail->id)?sanitize_text_field($googleDetail->id):""),
            'cu_id' => esc_js(isset($googleDetail->customer_id)?sanitize_text_field($googleDetail->customer_id):""),
            'pl_id' => esc_js(isset($googleDetail->plan_id)?sanitize_text_field($googleDetail->plan_id):""),
            'ga_tra_option' => esc_js(isset($googleDetail->tracking_option)?sanitize_text_field($googleDetail->tracking_option):""),
            'ga_property_id' => esc_js(isset($googleDetail->property_id)?sanitize_text_field($googleDetail->property_id):""),
            'ga_measurement_id' => esc_js(isset($googleDetail->measurement_id)?sanitize_text_field($googleDetail->measurement_id):""),
            'ga_ads_id' => esc_js(isset($googleDetail->google_ads_id)?sanitize_text_field($googleDetail->google_ads_id):""),
            'ga_gmc_id' => esc_js(isset($googleDetail->google_merchant_center_id)?sanitize_text_field($googleDetail->google_merchant_center_id):""),
            'op_gtag_js' => esc_js(isset($googleDetail->add_gtag_snippet)?sanitize_text_field($googleDetail->add_gtag_snippet):""),
            'op_en_e_t' => esc_js(isset($googleDetail->enhanced_e_commerce_tracking)?sanitize_text_field($googleDetail->enhanced_e_commerce_tracking):""),
            'op_rm_t_t' => esc_js(isset($googleDetail->remarketing_tags)?sanitize_text_field($googleDetail->remarketing_tags):""),
            'op_dy_rm_t_t' => esc_js(isset($googleDetail->dynamic_remarketing_tags)?esc_attr($googleDetail->dynamic_remarketing_tags):""),
            'op_li_ga_wi_ads' => esc_js(isset($googleDetail->link_google_analytics_with_google_ads)?sanitize_text_field($googleDetail->link_google_analytics_with_google_ads):""),
            'gmc_is_product_sync' => esc_js(isset($googleDetail->is_product_sync)?sanitize_text_field($googleDetail->is_product_sync):""),
            'gmc_is_site_verified' => esc_js(isset($googleDetail->is_site_verified)?sanitize_text_field($googleDetail->is_site_verified):""),
            'gmc_is_domain_claim' => esc_js(isset($googleDetail->is_domain_claim)?sanitize_text_field($googleDetail->is_domain_claim):""),
            'gmc_product_count' => esc_js(isset($googleDetail->product_count)?sanitize_text_field($googleDetail->product_count):""),
            'fb_pixel_id' => esc_js($this->fb_pixel_id),
            'tracking_method'=> esc_js($this->tracking_method),
            'user_gtm_id' => esc_js("conversios-gtm")
          )
        );
        $this->wc_version_compare("tvc_smd=" . json_encode($tvc_sMetaData) . ";");
    }
    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since4.0.0
     */
    public function enqueue_scripts() {
      
    }

    /**
     * add dev id
     *
     * @access public
     * @return void
     */
    function add_dev_id() {
        ?>
        <script>(window.gaDevIds=window.gaDevIds||[]).push('5CDcaG');</script>
        <?php
    }

    /**
     * woocommerce version compare
     *
     * @access public
     * @return void
     */
    function wc_version_compare($codeSnippet) {
      global $woocommerce;
      if (version_compare($woocommerce->version, "2.1", ">=")) {
        wc_enqueue_js($codeSnippet);
      } else {
        $woocommerce->add_inline_js($codeSnippet);
      }
    }
    /**
     * Enhanced Ecommerce GA plugin Settings
     *
     * @access public
     * @return void
     */
    function ee_settings() {
        global $woocommerce;

        //common validation----start
        if (is_admin() || $this->ga_ST == "" || current_user_can("manage_options")) {
            return;
        }
        
        $tracking_id = $this->ga_id;
        $measurment_id = $this->gm_id;
        $tracking_opt = $this->tracking_option;


        if(!$this->ga_PrivacyPolicy) {
            return;
        }

        //common validation----end
        $set_domain_name = "auto";
        // IP Anonymization
        if ($this->ga_IPA) {
            $ga_ip_anonymization = '"anonymize_ip":true,';
        } else {
            $ga_ip_anonymization ="";
        } ?>
        <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
          var track_option = '<?php echo esc_js($tracking_opt); ?>';
          var ua_track_id = '<?php echo esc_js($tracking_id); ?>';
          var ga4_measure_id = '<?php echo esc_js($measurment_id); ?>';
          var adsTringId = '<?php echo esc_js($this->ads_tracking_id); ?>';
          var ads_ert = '<?php echo esc_js($this->ads_ert); ?>';
          var ads_edrt = '<?php echo esc_js($this->ads_edrt); ?>';
          var remarketing_snippet_id = '<?php echo esc_js($this->remarketing_snippet_id); ?>';
        </script>
        <?php
        /*if($this->ga_OPTOUT) {
          ?>
          <script>
            // Set to the same value as the web property used on the site
            var gaProperty = '<?php echo esc_js($tracking_id); ?>';        
            // Disable tracking if the opt-out cookie exists.
            var disableStr = "ga-disable-" + gaProperty;
            if (document.cookie.indexOf(disableStr + "=true") > -1) {
              window[disableStr] = true;
            }        
            // Opt-out function
            function gaOptout() {
              var expDate = new Date;
              expDate.setMonth(expDate.getMonth() + 26);
              document.cookie = disableStr + "=true; expires="+expDate.toGMTString()+";path=/";
              window[disableStr] = true;
            }
          </script>
        <?php
        }*/

        if(($tracking_opt == "UA" || $tracking_id || $tracking_opt == "") && $tracking_opt != "BOTH"){?>
          <!--Conversios.io – Google Analytics and Google Shopping plugin for WooCommerce-->
          <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js($tracking_id); ?>"></script>
          <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag("js", new Date());
            gtag("config", "<?php echo esc_js($tracking_id); ?>",{<?php echo $ga_ip_anonymization; ?> "cookie_domain":"<?php echo esc_js($set_domain_name); ?>"});
          </script>
         <?php
        }
       
        if($tracking_opt == "GA4"){ ?>
          <!--Conversios.io – Google Analytics and Google Shopping plugin for WooCommerce-->
          <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js($measurment_id); ?>"></script>
          <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag("js", new Date());
            gtag("config", "<?php echo esc_js($measurment_id); ?>",{<?php echo $ga_ip_anonymization; ?> "cookie_domain":"<?php echo esc_js($set_domain_name); ?>"});
          </script>
        <?php            
        }
        if($tracking_opt == "BOTH"){ ?>
          <!--Conversios.io – Google Analytics and Google Shopping plugin for WooCommerce-->
          <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js($measurment_id); ?>"></script>
          <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag("js", new Date());
            gtag("config", "<?php echo esc_js($measurment_id); ?>",{ <?php echo $ga_ip_anonymization; ?> "cookie_domain":"<?php echo esc_js($set_domain_name); ?>"});
            gtag("config", "<?php echo esc_js($tracking_id); ?>");
          </script>
        <?php
        }
        if($this->ads_ert || $this->ads_edrt){
          if(!empty($this->remarketing_snippets) && $this->remarketing_snippets){
            echo html_entity_decode(str_replace("&#039;", "'", esc_html($this->remarketing_snippets)) );
          }else{
            $google_detail = $this->TVC_Admin_Helper->get_ee_options_data();
            if(isset($google_detail['setting'])){
                $googleDetail = $google_detail['setting'];
                echo  html_entity_decode(str_replace("&#039;", "'", esc_html($googleDetail->google_ads_snippets)) );
            }
          }
        }

        /*facebook pixel*/
      if($this->fb_pixel_id != ""){
        ?>
<!-- Conversios.io - Meta Pixel Code -->
<script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo esc_js($this->fb_pixel_id); ?>');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=<?php echo esc_js($this->fb_pixel_id); ?>&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
        <?php
      }
    }

    /**
     * Google Analytics eCommerce tracking
     *
     * @access public
     * @param mixed $order_id
     * @return void
     */
    function ecommerce_tracking_code($order_id) {
        global $woocommerce;
        $order = "";
        if($order_id == null && is_order_received_page()){
          $order = $this->tvc_get_order_from_order_received_page();
          $order_id = $order->get_id();
        }else{
          $order = new WC_Order($order_id);
        }
        if ($this->disable_tracking($this->ga_eeT) || current_user_can("manage_options") || get_post_meta($order_id, "_tracked", true) == 1 || !is_order_received_page() ){
            return;
        }
        // Doing eCommerce tracking so unhook standard tracking from the footer
        remove_action("wp_footer", array($this, "ee_settings"));

        // Get the order and output tracking code
        //$order = new WC_Order($order_id);
        $orderpage_prod_Array = array();
        //Get Applied Coupon Codes
        $coupons_list = '';
        if(version_compare($woocommerce->version, "3.7", ">")){
            if ($order->get_coupon_codes()) {
                $coupons_count = count($order->get_coupon_codes());
                $i = 1;
                foreach ($order->get_coupon_codes() as $coupon) {
                    $coupons_list .= $coupon;
                    if ($i < $coupons_count){
                        $coupons_list .= ', ';
                    }
                    $i++;
                }
            }
        }else{
            if ($order->get_used_coupons()) {
                $coupons_count = count($order->get_used_coupons());
                $i = 1;
                foreach ($order->get_used_coupons() as $coupon) {
                    $coupons_list .= $coupon;
                    if ($i < $coupons_count){
                        $coupons_list .= ', ';
                    }
                    $i++;
                }
            }    
        }
        
        //get domain name if value is set
        if (!empty($this->ga_Dname)) {
            $set_domain_name = esc_js($this->ga_Dname);
        } else {
            $set_domain_name = esc_js("auto");
        }

        // Order items
        if ($order->get_items()) {

            foreach ($order->get_items() as $item) {
                $_product = $item->get_product();
                if(empty($_product)){
                    continue; 
                }
                if (isset($_product->variation_data)) {
                    $categories=get_the_terms($_product->get_parent_id(), "product_cat");
                    $attributes=esc_js(wc_get_formatted_variation($_product->get_variation_attributes(), true));
                    if ($categories) {
                        foreach ($categories as $category) {
                            $out[] = $category->name;
                        }
                    }
                    $categories=esc_js(join(",", $out));
                } else {
                    $out = array();
                    
                    $categories = get_the_terms($_product->get_id(), "product_cat");
                    

                    if ($categories) {
                        foreach ($categories as $category) {
                            $out[] = $category->name;
                        }
                    }
                    $categories=esc_js(join(",", $out));
                }
                //orderpage Prod json
                if (isset($_product->variation_data)) {                    
                  $orderpage_prod_Array[get_permalink($_product->get_id())]=array(
                      "tvc_id" => esc_js($_product->get_id()),
                      "tvc_i" => esc_js($_product->get_sku() ? $_product->get_sku() : $_product->get_id()),
                      "tvc_n" => html_entity_decode(esc_js($item["name"])),
                      "tvc_p" => esc_js($order->get_item_total($item)),
                      "tvc_c" => esc_js($categories),
                      "tvc_attr" => esc_js($attributes),
                      "tvc_q"=>esc_js($item["qty"])
                  );                    
                } else {                   
                  $orderpage_prod_Array[get_permalink($_product->get_id())]=array(
                  "tvc_id" => esc_js($_product->get_id()),
                  "tvc_i" => esc_js($_product->get_sku() ? $_product->get_sku() : $_product->get_id()),
                  "tvc_n" => esc_js($_product->get_title()),
                  "tvc_p" => esc_js($order->get_item_total($item)),
                  "tvc_c" => esc_js($categories),
                  "tvc_q"=>esc_js($item["qty"])
                  );                    
                }  
            }
            //make json for prod meta data on order page
            $this->wc_version_compare("tvc_oc=" . json_encode($orderpage_prod_Array) . ";");
        }
        //get shipping cost
        $tvc_sc = $order->get_total_shipping();
        
        //orderpage transcation data json
        $orderpage_trans_Array=array(
            "id"=> esc_js($order->get_order_number()),      // Transaction ID. Required
            "affiliation"=> esc_js(get_bloginfo('name')), // Affiliation or store name
            "revenue"=>esc_js($order->get_total()),        // Grand Total
            "tax"=> esc_js($order->get_total_tax()),        // Tax
            "shipping"=> esc_js($tvc_sc),    // Shipping
            "coupon"=>esc_js($coupons_list)
        );
        //make json for trans data on order page
        $this->wc_version_compare("tvc_td=" . json_encode($orderpage_trans_Array) . ";");

        if($this->ga_id || $this->tracking_option == "UA" || $this->tracking_option == "BOTH") {
            $code ='
                     var items = [];
                    //set local currencies
                gtag("set", {"currency": tvc_lc});
                for(var t_item in tvc_oc){
                    items.push({
                        "id": tvc_oc[t_item].tvc_i,
                        "name": tvc_oc[t_item].tvc_n, 
                        "category": tvc_oc[t_item].tvc_c,
                        "attributes": tvc_oc[t_item].tvc_attr,
                        "price": tvc_oc[t_item].tvc_p,
                        "quantity": tvc_oc[t_item].tvc_q,
                    });
                   
                }
                gtag("event", "purchase", {
                    "transaction_id":tvc_td.id,
                    "affiliation": tvc_td.affiliation,
                    "value":tvc_td.revenue,
                    "tax": tvc_td.tax,
                    "shipping": tvc_td.shipping,
                    "coupon": tvc_td.coupon,
                    "event_category": "Enhanced-Ecommerce",
                    "event_label":"order_confirmation",
                    "non_interaction": true,
                    "items":items
                });
                if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                    var ads_items = [];
                    var ads_value=0;
                    for(var t_item in tvc_oc){
                        ads_value=ads_value + parseFloat(tvc_oc[t_item].tvc_p);
                        ads_items.push({
                            id: tvc_oc[t_item].tvc_id,
                            google_business_vertical: "retail"
                        });
                    }
                    gtag("event","purchase", {
                        "send_to":remarketing_snippet_id,
                        "value": tvc_td.revenue,
                        "items": ads_items
                      });
                }
        ';

            //check woocommerce version
            $this->wc_version_compare($code);
            update_post_meta($order_id, "_tracked",  1);
        }
        // start GA4 or Both
        if( $this->gm_id && $this->tracking_option == "GA4") {
            $code = '            
            var items = [];
            for(var t_item in tvc_oc){
                items.push({
                    "item_id": tvc_oc[t_item].tvc_i,
                    "item_name": tvc_oc[t_item].tvc_n, 
                    "coupon": tvc_td.coupon,
                    "affiliation": tvc_td.affiliation,
                    "item_category": tvc_oc[t_item].tvc_c,
                    "item_variant": tvc_oc[t_item].tvc_attr,
                    "price": tvc_oc[t_item].tvc_p,
                    "currency": tvc_lc,
                    "quantity": tvc_oc[t_item].tvc_q,
                });
               
            }
            gtag("event", "purchase", {
                "transaction_id":tvc_td.id,
                "shipping": tvc_td.shipping,
                "affiliation": tvc_td.affiliation,
                "value":tvc_td.revenue,
                "currency": tvc_lc,
                "coupon": tvc_td.coupon,
                "tax": tvc_td.tax,
                "event_category": "Enhanced-Ecommerce",
                "event_label":"order_confirmation",
                "non_interaction": true,
                "items":items
            });
            
            if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                    var ads_items = [];
                    var ads_value=0;
                    for(var t_item in tvc_oc){
                    ads_value=ads_value + parseFloat(tvc_oc[t_item].tvc_p);
                        ads_items.push({
                            id: tvc_oc[t_item].tvc_id,
                            google_business_vertical: "retail"
                        });
                    }
                    gtag("event","purchase", {
                        "send_to":remarketing_snippet_id,
                        "value": tvc_td.revenue,
                        "items": ads_items
                      });
                }
        ';

            //check woocommerce version
            $this->wc_version_compare($code);
            update_post_meta($order_id, "_tracked", 1);
        }

        /* facebook pixel */
        if($this->fb_pixel_id != ""){
          ?>
          <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
            var products = <?php echo json_encode($orderpage_prod_Array); ?>;
            var cart_total = <?php echo json_encode($order->get_total()); ?>;

            var fb_content_ids = [];
            var fb_contents = [], num_items = 0;
            for(var t_item in products){
              num_items+=parseInt(products[t_item].tvc_q);
              fb_content_ids.push(products[t_item].tvc_id);
              fb_contents.push({"id":products[t_item].tvc_id, "quantity":products[t_item].tvc_q});
            }

            fbq("track", "Purchase", {
                content_type  : "product_group",
                content_name  : "Thankyou Page",
                content_ids   : fb_content_ids,
                currency      : "<?php echo esc_js($this->ga_LC); ?>",
                num_items     : num_items,
                value         : cart_total,
                contents      : fb_contents
            })
          </script>
        <?php
        }
    }

    /**
     * Enhanced E-commerce tracking for single product add to cart
     *
     * @access public
     * @return void
     */
    function single_add_to_cart() {
        if ($this->disable_tracking($this->ga_eeT)){
          return;
        }
        //return if not product page
        if (!is_single() || !is_product() ){
          return;
        }
        global $product,$woocommerce;        
        $category = get_the_terms($product->get_id(), "product_cat");
        $categories = "";
        if ($category) {
            foreach ($category as $term) {
                $categories.=$term->name . ",";
            }
        }
        //remove last comma(,) if multiple categories are there
        $categories = rtrim($categories, ",");
        $product_detail_addtocart_selector = ( isset( $this->c_t_o['tvc_product_detail_addtocart_selector']) && $this->c_t_o['tvc_product_detail_addtocart_selector'] == "custom" )?$this->c_t_o:array();
        $product_detail_addtocart_selector_css = esc_js($this->get_selector_val_fron_array($product_detail_addtocart_selector, 'tvc_product_detail_addtocart_selector'));
        if($this->ga_id || ($this->tracking_option == "UA" || $this->tracking_option == "BOTH")) {
            $code = '
                   var items = [];
                //set local currencies
                gtag("set", {"currency": tvc_lc});
                jQuery("button[class*=\'btn-buy-shop\'],button[class*=\'single_add_to_cart_button\'], button[class*=\'add_to_cart\']'.$product_detail_addtocart_selector_css.'").click(function() {
                // Enhanced E-commerce Add to cart clicks
                    gtag("event", "add_to_cart", {
                        "event_category":"Enhanced-Ecommerce",
                        "event_label":"add_to_cart_click",
                        "non_interaction": true,
                        "items": [{
                            "id" : tvc_po.tvc_i,
                            "name": tvc_po.tvc_n,
                            "category" :tvc_po.tvc_c,
                            "price": tvc_po.tvc_p,
                            "quantity" :jQuery(this).parent().find("input[name=quantity]").val()
                        }]
                    });
                    //add remarketing and dynamicremarketing tags
                    if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                        gtag("event","add_to_cart", {
                            "send_to":remarketing_snippet_id,
                            "value": tvc_po.tvc_p,
                            "items": [
                            {
                              "id": tvc_po.tvc_id, 
                              "google_business_vertical": "retail"
                            }
                          ]
                        });
                    }                                 
                });
                
            ';
            //check woocommerce version
            $this->wc_version_compare($code);
        }
        if($this->gm_id && $this->tracking_option == "GA4") {
            $code = '
            var items = [];
            jQuery("button[class*=\'btn-buy-shop\'],button[class*=\'single_add_to_cart_button\'], button[class*=\'add_to_cart\']").click(function() {
                gtag("event", "add_to_cart", {
                    "event_category":"Enhanced-Ecommerce",
                    "event_label":"add_to_cart_click",
                    "currency": tvc_lc,
                    "non_interaction": true,
                    "items": [{
                        "item_id" : tvc_po.tvc_i,
                        "item_name": tvc_po.tvc_n,
                        "item_category" :tvc_po.tvc_c,
                        "price": tvc_po.tvc_p,
                        "quantity" :jQuery(this).parent().find("input[name=quantity]").val()
                    }]
                });   
                
                //add remarketing and dynamicremarketing tags
                if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                    gtag("event","add_to_cart", {
                        "send_to":remarketing_snippet_id,
                        "value": tvc_po.tvc_p,
                        "items": [
                        {
                          "id": tvc_po.tvc_id, 
                          "google_business_vertical": "retail"
                        }
                      ]
                    });
                }            
            });
        ';
        $this->wc_version_compare($code);
        }
        /* facebook pixel */
        if($this->fb_pixel_id != ""){
          ?>
          <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
            jQuery("button[class*='btn-buy-shop'],button[class*='single_add_to_cart_button'], button[class*='add_to_cart']").click(function() {
              var quantity = jQuery(this).parent().find("input[name=quantity]").val();
              fbq("track", "AddToCart", {
                content_type  : "product",
                content_name  : tvc_po.tvc_n,
                content_ids   : [tvc_po.tvc_id],
                currency      : tvc_lc,
                value         : tvc_po.tvc_p,
                contents   :[{id:tvc_po.tvc_id, 'quantity':quantity}]
              })
            });
          </script>
        <?php
        }
        
    }

    /**
     * Enhanced E-commerce tracking for product detail view
     *
     * @access public
     * @return void
     */
    public function product_detail_view() {

        if ( $this->disable_tracking($this->ga_eeT) || !is_product() ) {
          return;
        }
        global  $wp_query, $woocommerce ;
        $product = wc_get_product();
        $category = get_the_terms($product->get_id(), "product_cat");
        
        $categories = "";
        if ($category) {
            foreach ($category as $term) {
                $categories.=$term->name . ",";
            }
        }
        //remove last comma(,) if multiple categories are there
        $categories = rtrim($categories, ",");
        //product detail view json        
        $prodpage_detail_json = array(
            "tvc_id" => esc_js($product->get_id()),
            "tvc_i" => esc_js($product->get_sku() ? $product->get_sku() : $product->get_id()),
            "tvc_n" => esc_js($product->get_title()),
            "tvc_c" => esc_js($categories),
            "tvc_p" => esc_js($product->get_price())
        );
        

        if (empty($prodpage_detail_json)) {
            //prod page array
            $prodpage_detail_json = array();
        }
        //prod page detail view json
        $this->wc_version_compare("tvc_po=" . json_encode($prodpage_detail_json) . ";");
        if($this->ga_id || $this->tracking_option == "UA" || $this->tracking_option == "BOTH") {
            $code = '
            gtag("event", "view_item", {
                        "event_category":"Enhanced-Ecommerce",
                        "event_label":"product_impression_pp",
                        "items": [
                          {
                            "id": tvc_po.tvc_i,// Product details are provided in an impressionFieldObject.
                            "name":  tvc_po.tvc_n,
                            "category":tvc_po.tvc_c,
                          }
                        ],
                        "non_interaction": true
            })
            //add remarketing and dynamicremarketing tags
            if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                gtag("event","view_item", {
                    "send_to":remarketing_snippet_id,
                    "value": tvc_po.tvc_p,
                    "items": [
                      {
                        "id": tvc_po.tvc_id, 
                        "google_business_vertical": "retail"
                      }
                    ]
                  });
            }
            ';
            //check woocommerce version
            if(is_product()){
                $this->wc_version_compare($code);
            }
        }

        if( $this->gm_id && $this->tracking_option == "GA4") {
            $code = '
                gtag("event", "view_item", {
                    "event_category":"Enhanced-Ecommerce",
                    "event_label":"product_impression_pp",
                    "currency": tvc_lc,
                    "items": [
                      {
                        "item_id": tvc_po.tvc_i,
                        "item_name":  tvc_po.tvc_n,
                        "item_category":tvc_po.tvc_c,
                      }
                    ],
                    "non_interaction": true
                })
                //add remarketing and dynamicremarketing tags
            if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                gtag("event","view_item", {
                    "send_to":remarketing_snippet_id,
                    "value": tvc_po.tvc_p,
                    "items": [
                      {
                        "id": tvc_po.tvc_id, 
                        "google_business_vertical": "retail"
                      }
                    ]
                  });
            }
                ';
            //check woocommerce version
            if (is_product()) {
                $this->wc_version_compare($code);
            }
        }
    }

    /**
     * Enhanced E-commerce tracking for product impressions on category pages (hidden fields) , product page (related section)
     * home page (featured section and recent section)
     *
     * @access public
     * @return void
     */
    public function bind_product_metadata() {
        if ($this->disable_tracking($this->ga_eeT)) {
            return;
        }

        global $product,$woocommerce;
        $category = get_the_terms($product->get_id(), "product_cat");
        $categories = "";
        if ($category) {
          foreach ($category as $term) {
            $categories.=$term->name . ",";
          }
        }
        //remove last comma(,) if multiple categories are there
        $categories = rtrim($categories, ",");
        //declare all variable as a global which will used for make json
        global $homepage_json_fp,$homepage_json_ATC_link, $homepage_json_rp,$prodpage_json_relProd,$catpage_json,$prodpage_json_ATC_link,$catpage_json_ATC_link;
        //is home page then make all necessory json
        if (is_home() || is_front_page()) {
            if (!is_array($homepage_json_fp) && !is_array($homepage_json_rp) && !is_array($homepage_json_ATC_link)) {
                $homepage_json_fp = array();
                $homepage_json_rp = array();
                $homepage_json_ATC_link=array();
            }

            // ATC link Array            
            $homepage_json_ATC_link[$product->add_to_cart_url()]=
            array(
                "ATC-link"=> esc_url_raw(get_permalink($product->get_id()))
            );
            
            //check if product is featured product or not
            if ($product->is_featured()) {
              //check if product is already exists in homepage featured json                
              if(!array_key_exists(get_permalink($product->get_id()),$homepage_json_fp)){
                  $homepage_json_fp[get_permalink($product->get_id())] = array(
                      "tvc_id" => esc_js($product->get_id()),
                      "tvc_i" => esc_js($product->get_sku() ? $product->get_sku() : $product->get_id()),
                      "tvc_n" => esc_js($product->get_title()),
                      "tvc_p" => esc_js($product->get_price()),
                      "tvc_c" => esc_js($categories),
                      "ATC-link"=> esc_url_raw($product->add_to_cart_url())
                  );
                  //else add product in homepage recent product json
              }else {
                  $homepage_json_rp[get_permalink($product->get_id())] =array(
                      "tvc_id" => esc_js($product->get_id()),
                      "tvc_i" => esc_js($product->get_sku() ? $product->get_sku() : $product->get_id()),
                      "tvc_n" => esc_js($product->get_title()),
                      "tvc_p" => esc_js($product->get_price()),
                      "tvc_c" => esc_js($categories)
                  );
              }
            } else {
              //else prod add in homepage recent json                
              $homepage_json_rp[get_permalink($product->get_id())] =array(
                  "tvc_id" => esc_js($product->get_id()),
                  "tvc_i" => esc_js($product->get_sku() ? $product->get_sku() : $product->get_id()),
                  "tvc_n" => esc_js($product->get_title()),
                  "tvc_p" => esc_js($product->get_price()),
                  "tvc_c" => esc_js($categories)
              );
            }
        } else if(is_product()){
          //if product page then related product page array
            if(!is_array($prodpage_json_relProd) && !is_array($prodpage_json_ATC_link)){
                $prodpage_json_relProd = array();
                $prodpage_json_ATC_link = array();
            }
            // ATC link Array            
            $prodpage_json_ATC_link[$product->add_to_cart_url()]=
            array( 
                "ATC-link"=> esc_url_raw(get_permalink($product->get_id()))
            );
            $prodpage_json_relProd[get_permalink($product->get_id())] = array(
                "tvc_id" => esc_js($product->get_id()),
                "tvc_i" => esc_js($product->get_sku() ? $product->get_sku() : $product->get_id()),
                "tvc_n" => esc_js($product->get_title()),
                "tvc_p" => esc_js($product->get_price()),
                "tvc_c" => esc_js($categories)

            );
            
        } else if (is_product_category() || is_search() || is_shop()) {
          //category page, search page and shop page json
            if (!is_array($catpage_json) && !is_array($catpage_json_ATC_link)){
              $catpage_json=array();
              $catpage_json_ATC_link=array();
            }
            //cat page ATC array            
            $catpage_json_ATC_link[$product->add_to_cart_url()]=array(
              "ATC-link"=> esc_url_raw(get_permalink($product->get_id()))
            );
            $catpage_json[get_permalink($product->get_id())] =array(
              "tvc_id" => esc_js($product->get_id()),
              "tvc_i" => esc_js($product->get_sku() ? $product->get_sku() : $product->get_id()),
              "tvc_n" => esc_js($product->get_title()),
              "tvc_p" => esc_js($product->get_price()),
              "tvc_c" => esc_js($categories)
            );
            
        }
    }

    /**
     * Enhanced E-commerce tracking for product impressions,clicks on Home pages
     *
     * @access public
     * @return void
     */
    function t_products_impre_clicks() {
        if ($this->disable_tracking($this->ga_eeT)) {
            return;
        }

        //get impression threshold
        $impression_threshold = $this->ga_imTh;

        //Product impression on Home Page
        global $homepage_json_fp,$homepage_json_ATC_link, $homepage_json_rp,$prodpage_json_relProd,$catpage_json,$prodpage_json_ATC_link,$catpage_json_ATC_link;
        //home page json for featured products and recent product sections
        //check if php array is empty
        if(empty($homepage_json_ATC_link)){
            $homepage_json_ATC_link=array(); //define empty array so if empty then in json will be []
        }
        if(empty($homepage_json_fp)){
            $homepage_json_fp=array(); //define empty array so if empty then in json will be []
        }
        if(empty($homepage_json_rp)){ //home page recent product array
            $homepage_json_rp=array();
        }
        if(empty($prodpage_json_relProd)){ //prod page related section array
            $prodpage_json_relProd=array();
        }
        if(empty($prodpage_json_ATC_link)){
            $prodpage_json_ATC_link=array(); //prod page ATC link json
        }
        if(empty($catpage_json)){ //category page array
            $catpage_json=array();
        }
        if(empty($catpage_json_ATC_link)){ //category page array
            $catpage_json_ATC_link=array();
        }
        //home page json
        $this->wc_version_compare("homepage_json_ATC_link=" . json_encode($homepage_json_ATC_link) . ";");
        $this->wc_version_compare("tvc_fp=" . json_encode($homepage_json_fp) . ";");
        $this->wc_version_compare("tvc_rcp=" . json_encode($homepage_json_rp) . ";");
        //product page json
        $this->wc_version_compare("tvc_rdp=" . json_encode($prodpage_json_relProd) . ";");
        $this->wc_version_compare("prodpage_json_ATC_link=" . json_encode($prodpage_json_ATC_link) . ";");
        //category page json
        $this->wc_version_compare("tvc_pgc=" . json_encode($catpage_json) . ";");
        $this->wc_version_compare("catpage_json_ATC_link=" . json_encode($catpage_json_ATC_link) . ";");
        if($this->ga_id || $this->tracking_option == "UA" || $this->tracking_option == "BOTH") {
            $hmpg_impressions_jQ = '
                var items = [];
                //set local currencies
                gtag("set", {"currency": tvc_lc});
                function t_products_impre_clicks(t_json_name,t_action){
                       t_send_threshold=0;
                       t_prod_pos=0;
                        t_json_length=Object.keys(t_json_name).length;
                            
                        for(var t_item in t_json_name) {
                    t_send_threshold++;
                    t_prod_pos++;
                    items.push({
                        "id": t_json_name[t_item].tvc_i,
                        "name": t_json_name[t_item].tvc_n,
                        "category": t_json_name[t_item].tvc_c,
                        "price": t_json_name[t_item].tvc_p,
                    });    
                            
                        if(t_json_length > ' . esc_js($impression_threshold) .' ){

                            if((t_send_threshold%' . esc_js($impression_threshold) . ')==0){
                                t_json_length=t_json_length-' . esc_js($impression_threshold) . ';
                                    gtag("event", "view_item_list", { "event_category":"Enhanced-Ecommerce",
                                         "event_label":"product_impression_"+t_action, "items":items,"non_interaction": true});
                                         items = [];
                                        }
                                        if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                                            gtag("event","view_item_list", {
                                                "send_to":remarketing_snippet_id,
                                                "value": t_json_name[t_item].tvc_p,
                                                "items": [
                                                  {
                                                    "id": t_json_name[t_item].tvc_id, 
                                                    "google_business_vertical": "retail"
                                                  }
                                               ]
                                            });
                                        }
                            }else{
                
                               t_json_length--;
                               if(t_json_length==0){
                                       gtag("event", "view_item_list", { "event_category":"Enhanced-Ecommerce",
                                            "event_label":"product_impression_"+t_action, "items":items,"non_interaction": true});
                                            items = [];
                                        }
                                       if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                                            gtag("event","view_item_list", {
                                                "send_to":remarketing_snippet_id,
                                                "value": t_json_name[t_item].tvc_p,
                                                "items": [
                                                  {
                                                    "id": t_json_name[t_item].tvc_id, 
                                                    "google_business_vertical": "retail"
                                                  }
                                               ]
                                            });
                                        }
                                }   
                            }
            }
                    
            //function for comparing urls in json object
            function prod_exists_in_JSON(t_url,t_json_name,t_action){
                                        if(t_json_name.hasOwnProperty(t_url)){
                                            t_call_fired=true;
                                            gtag("event", "select_content", {
                                                "event_category":"Enhanced-Ecommerce",
                                                "event_label":"product_click_"+t_action,
                                                "content_type": "product",
                                                "items": [
                                                {
                                                    "id":t_json_name[t_url].tvc_i,
                                                    "name": t_json_name[t_url].tvc_n,
                                                     "category":t_json_name[t_url].tvc_c,
                                                     "price": t_json_name[t_url].tvc_p,
                                                }
                                                ],
                                                "non_interaction": true
                                            });                    
                                       }else{
                                            t_call_fired=false;
                                        }
                                        return t_call_fired;
                                }
                    function prod_ATC_link_exists(t_url,t_ATC_json_name,t_prod_data_json,t_qty){
                        t_prod_url_key=t_ATC_json_name[t_url]["ATC-link"];
                        
                            if(t_prod_data_json.hasOwnProperty(t_prod_url_key)){
                                    t_call_fired=true;
                                    /*facebook pixel */
                                    var fb_pixel_id = "'.esc_js($this->fb_pixel_id).'";
                                    var product = t_prod_data_json[t_prod_url_key];
                                    if(fb_pixel_id != ""){
                                      fbq("track", "AddToCart", {
                                        content_type  : "product",
                                        content_name  : product.tvc_n,
                                        content_ids   : [product.tvc_id],
                                        currency      : tvc_lc,
                                        value         : product.tvc_p,
                                        contents   :[{id:product.tvc_id, quantity:t_qty}]
                                      })
                                    }
                                    /*end facebook pixel */
                                // Enhanced E-commerce Add to cart clicks
                                    gtag("event", "add_to_cart", {
                                        "event_category":"Enhanced-Ecommerce",
                                        "event_label":"add_to_cart_click",
                                        "non_interaction": true,
                                        "items": [{
                                            "id" : t_prod_data_json[t_prod_url_key].tvc_i,
                                            "name":t_prod_data_json[t_prod_url_key].tvc_n,
                                            "category" : t_prod_data_json[t_prod_url_key].tvc_c,
                                            "price": t_prod_data_json[t_prod_url_key].tvc_p,
                                            "quantity" :t_qty
                                        }]
                                    });
                                    if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                                        gtag("event","add_to_cart", {
                                            "send_to":remarketing_snippet_id,
                                            "value": t_prod_data_json[t_prod_url_key].tvc_p,
                                            "items": [
                                              {
                                                "id": t_prod_data_json[t_prod_url_key].tvc_id, 
                                                "google_business_vertical": "retail"
                                              }
                                            ]
                                        });
                                    }
                                 
                            }else{
                                       t_call_fired=false;
                            }    
                             return t_call_fired;
                     
                    }
                    
                    ';
                }
        if($this->gm_id && $this->tracking_option == "GA4") {
                    $hmpg_impressions_jQ = '
            var items = [];
            function t_products_impre_clicks(t_json_name,t_action){
               t_send_threshold=0;
               t_prod_pos=0;
               t_json_length=Object.keys(t_json_name).length;
            for(var t_item in t_json_name) {
                t_send_threshold++;
                t_prod_pos++;
                items.push({
                    "item_id": t_json_name[t_item].tvc_i,
                    "item_name": t_json_name[t_item].tvc_n,
                    "item_category": t_json_name[t_item].tvc_c,
                    "price": t_json_name[t_item].tvc_p,
                    "currency": tvc_lc
                });    
            if(t_json_length > ' . esc_js($impression_threshold) . ' ){
                        if((t_send_threshold%' . esc_js($impression_threshold) . ')==0){
                            t_json_length=t_json_length-' . esc_js($impression_threshold) . ';
                                gtag("event", "view_item_list", { 
                                    "event_category":"Enhanced-Ecommerce",
                                    "event_label":"product_impression_"+t_action, 
                                    "items":items,
                                    "non_interaction": true
                                });
                                items = [];
                            }
                        if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                            gtag("event","view_item_list", {
                                "send_to":remarketing_snippet_id,
                                "value": t_json_name[t_item].tvc_p,
                                "items": [
                                  {
                                    "id": t_json_name[t_item].tvc_id, 
                                    "google_business_vertical": "retail"
                                  }
                               ]
                            });
                        }
                        }else{
                            t_json_length--;
                       if(t_json_length==0){
                               gtag("event", "view_item_list", { 
                                   "event_category":"Enhanced-Ecommerce",
                                   "event_label":"product_impression_"+t_action, 
                                   "items":items,
                                   "non_interaction": true
                               });
                               items = [];
                           }
                           if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                            gtag("event","view_item_list", {
                                "send_to":remarketing_snippet_id,
                                "value": t_json_name[t_item].tvc_p,
                                "items": [
                                  {
                                    "id": t_json_name[t_item].tvc_id, 
                                    "google_business_vertical": "retail"
                                  }
                               ]
                            });
                        }
                       }   
                    }
            }
                
        //function for comparing urls in json object
        function prod_exists_in_JSON(t_url,t_json_name,t_action){
                if(t_json_name.hasOwnProperty(t_url)){
                    t_call_fired=true;
                    gtag("event", "select_item", {
                        "event_category":"Enhanced-Ecommerce",
                        "event_label":"product_click_"+t_action,
                        "items": [
                            {
                                "item_id":t_json_name[t_url].tvc_i,
                                "item_name": t_json_name[t_url].tvc_n,
                                "item_category":t_json_name[t_url].tvc_c,
                                "price": t_json_name[t_url].tvc_p,
                                "currency": tvc_lc
                            }
                        ],
                        "non_interaction": true
                    });      
                                  
               }else{
                    t_call_fired=false;
               }
               return t_call_fired;
            }
                function prod_ATC_link_exists(t_url,t_ATC_json_name,t_prod_data_json,t_qty){
                    t_prod_url_key=t_ATC_json_name[t_url]["ATC-link"];
                        if(t_prod_data_json.hasOwnProperty(t_prod_url_key)){
                                t_call_fired=true;
                                /*facebook pixel */
                                  var fb_pixel_id = "'.esc_js($this->fb_pixel_id).'";
                                  var product = t_prod_data_json[t_prod_url_key];
                                  if(fb_pixel_id != ""){
                                    fbq("track", "AddToCart", {
                                      content_type  : "product",
                                      content_name  : product.tvc_n,
                                      content_ids   : [product.tvc_id],
                                      currency      : tvc_lc,
                                      value         : product.tvc_p,
                                      contents   :[{id:product.tvc_id, quantity:t_qty}]
                                    })
                                  }
                                /*end facebook pixel */
                            // Enhanced E-commerce Add to cart clicks
                                gtag("event", "add_to_cart", {
                                    "event_category":"Enhanced-Ecommerce",
                                    "event_label":"add_to_cart_click",
                                    "non_interaction": true,
                                    "items": [{
                                        "item_id" : t_prod_data_json[t_prod_url_key].tvc_i,
                                        "item_name":t_prod_data_json[t_prod_url_key].tvc_n,
                                        "item_category" : t_prod_data_json[t_prod_url_key].tvc_c,
                                        "price": t_prod_data_json[t_prod_url_key].tvc_p,
                                        "currency": tvc_lc,
                                        "quantity" :t_qty
                                    }]
                                });
                                
                            if(adsTringId != "" && ( ads_ert == 1 || ads_edrt == 1)){
                                gtag("event","add_to_cart", {
                                    "send_to":remarketing_snippet_id,
                                    "value": t_prod_data_json[t_prod_url_key].tvc_p,
                                    "items": [
                                      {
                                        "id": t_prod_data_json[t_prod_url_key].tvc_id, 
                                        "google_business_vertical": "retail"
                                      }
                                   ]
                                });
                            }
                        }else{
                            t_call_fired=false;
                        }    
                         return t_call_fired;
                }
                ';
                }
        if(is_home() || is_front_page()){
            $hmpg_impressions_jQ .='
                if(tvc_fp.length !== 0){
                    t_products_impre_clicks(tvc_fp,"fp");       
                }
                if(tvc_rcp.length !== 0){
                    t_products_impre_clicks(tvc_rcp,"rp");    
                }
                jQuery("a:not([href*=add-to-cart],.product_type_variable, .product_type_grouped)").on("click",function(){
            t_url=jQuery(this).attr("href");
                        //home page call for click
                        t_call_fired=prod_exists_in_JSON(t_url,tvc_fp,"fp");
                        if(!t_call_fired){
                            prod_exists_in_JSON(t_url,tvc_rcp,"rp");
                        }    
                });
                //ATC click
                jQuery("a[href*=add-to-cart]").on("click",function(){
            t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there             
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                        t_call_fired=prod_ATC_link_exists(t_url,homepage_json_ATC_link,tvc_fp,t_qty);
                        if(!t_call_fired){
                            prod_ATC_link_exists(t_url,homepage_json_ATC_link,tvc_rcp,t_qty);
                        }
                    });   
             
                ';
        }else if(is_search()){
            $hmpg_impressions_jQ .='
                //search page json
                if(tvc_pgc.length !== 0){
                    t_products_impre_clicks(tvc_pgc,"srch");   
                }
                //search page prod click
                jQuery("a:not(.product_type_variable, .product_type_grouped)").on("click",function(){
                    t_url=jQuery(this).attr("href");
                     //cat page prod call for click
                     prod_exists_in_JSON(t_url,tvc_pgc,"srch");
                     });
                
            ';
        }else if (is_product()) {
            //product page releted products
            $hmpg_impressions_jQ .='
                if(tvc_rdp.length !== 0){
                    t_products_impre_clicks(tvc_rdp,"rdp");  
                }          
                //product click - image and product name
                jQuery("a:not(.product_type_variable, .product_type_grouped)").on("click",function(){
                    t_url=jQuery(this).attr("href");
                     //prod page related call for click
                     prod_exists_in_JSON(t_url,tvc_rdp,"rdp");
                });  
                //Prod ATC link click in related product section
                jQuery("a[href*=add-to-cart]").on("click",function(){
            t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there             
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                prod_ATC_link_exists(t_url,prodpage_json_ATC_link,tvc_rdp,t_qty);
                });   
            ';
        }else if (is_product_category()) {
            $hmpg_impressions_jQ .='
                //category page json
                if(tvc_pgc.length !== 0){
                    t_products_impre_clicks(tvc_pgc,"cp");  
                }
               //Prod category ATC link click in related product section
                jQuery("a:not(.product_type_variable, .product_type_grouped)").on("click",function(){
                     t_url=jQuery(this).attr("href");
                     //cat page prod call for click
                     prod_exists_in_JSON(t_url,tvc_pgc,"cp");
                     });
               
        ';
        }else if(is_shop()){
            $hmpg_impressions_jQ .='
                //shop page json
                if(tvc_pgc.length !== 0){
                    t_products_impre_clicks(tvc_pgc,"sp");  
                }
                //shop page prod click
                jQuery("a:not(.product_type_variable, .product_type_grouped)").on("click",function(){
                    t_url=jQuery(this).attr("href");
                     //cat page prod call for click
                     prod_exists_in_JSON(t_url,tvc_pgc,"sp");
                     });
                
                     
        ';
        }
        //common ATC link for Category page , Shop Page and Search Page
        if(is_product_category() || is_shop() || is_search()){
            $hmpg_impressions_jQ .='
                     //ATC link click
                jQuery("a[href*=add-to-cart]").on("click",function(){
            t_url=jQuery(this).attr("href");
                        t_qty=$(this).parent().find("input[name=quantity]").val();
                             //default quantity 1 if quantity box is not there             
                            if(t_qty=="" || t_qty===undefined){
                                t_qty="1";
                            }
                       prod_ATC_link_exists(t_url,catpage_json_ATC_link,tvc_pgc,t_qty);
                    });      
                    ';
        }
        //on home page, product page , category page
        if (is_home() || is_front_page() || is_product() || is_product_category() || is_search() || is_shop()){
            $this->wc_version_compare($hmpg_impressions_jQ);
        }
    }

    /**
     * Enhanced E-commerce tracking for remove from cart
     *
     * @access public
     * @return void
     */
    public function remove_cart_tracking() {
        if ($this->disable_tracking($this->ga_eeT)) {
            return;
        }
        global $woocommerce;
        $cartpage_prod_array_main = array();
        foreach ($woocommerce->cart->cart_contents as $key => $item) {
          $prod_meta = wc_get_product($item["product_id"]);          
          if (version_compare($woocommerce->version, "3.3", "<")) {
              $cart_remove_link=html_entity_decode($woocommerce->cart->get_remove_url($key));
          } else {
              $cart_remove_link=html_entity_decode(wc_get_cart_remove_url($key));
          }
          $category = get_the_terms($item["product_id"], "product_cat");
          $categories = "";
          if ($category) {
              foreach ($category as $term) {
                  $categories.=$term->name . ",";
              }
          }
          //remove last comma(,) if multiple categories are there
          $categories = rtrim($categories, ",");            
          $cartpage_prod_array_main[$cart_remove_link] =array(
            "tvc_id" => esc_js($prod_meta->get_id()),
            "tvc_i" => esc_js($prod_meta->get_sku() ? $prod_meta->get_sku() : $prod_meta->get_id()),
            "tvc_n" => html_entity_decode(esc_js($prod_meta->get_title())),
            "tvc_p" => esc_js($prod_meta->get_price()),
            "tvc_c" => esc_js($categories),
            "tvc_q"=> esc_js($woocommerce->cart->cart_contents[$key]["quantity"])
          );            
        }

        //Cart Page item Array to Json
        $this->wc_version_compare("tvc_cc=" . json_encode($cartpage_prod_array_main) . ";");
        if($this->ga_id || $this->tracking_option == "UA" || $this->tracking_option == "BOTH") {
            $code = 'gtag("set", {"currency": tvc_lc});$( document.body ).on("click", "a[href*=\"?remove_item\"]", function(){
                     t_url=jQuery(this).attr("href");
                        gtag("event", "remove_from_cart", {
                            "event_category":"Enhanced-Ecommerce",
                            "event_label":"remove_from_cart_click",
                            "items": [{
                                "id":tvc_cc[t_url].tvc_i,
                                "name": tvc_cc[t_url].tvc_n,
                                "category":tvc_cc[t_url].tvc_c,
                                "price": tvc_cc[t_url].tvc_p,
                                "quantity": tvc_cc[t_url].tvc_q
                            }],
                            "non_interaction": true
                        });
                    });';
            //check woocommerce version
            $this->wc_version_compare($code);
        }

        if($this->gm_id && $this->tracking_option == "GA4") {
             $code = '$( document.body ).on("click", "a[href*=\"?remove_item\"]", function(){
                      t_url=jQuery(this).attr("href");
                        gtag("event", "remove_from_cart", {
                            "event_category":"Enhanced-Ecommerce",
                            "event_label":"remove_from_cart_click",
                            "currency": tvc_lc,
                            "items": [{
                                "item_id":tvc_cc[t_url].tvc_i,
                                "item_name": tvc_cc[t_url].tvc_n,
                                "item_category":tvc_cc[t_url].tvc_c,
                                "price": tvc_cc[t_url].tvc_p,
                                "currency": tvc_lc,
                                "quantity": tvc_cc[t_url].tvc_q
                            }],
                            "non_interaction": true
                        });
                    });';
                //check woocommerce version
                $this->wc_version_compare($code);
        }
    }

    /**
     * Enhanced E-commerce tracking checkout step 1
     *
     * @access public
     * @return void
     */
    public function checkout_step_1_tracking() {
      if( $this->disable_tracking($this->ga_eeT) || !is_checkout()  || is_order_received_page() ) {
        return;
      }
        //call fn to make json
        $chkout_json = $this->get_ordered_items();
        $cart_total = WC()->cart->total;
        if($this->ga_id || $this->tracking_option == "UA" || $this->tracking_option == "BOTH") {
            $code= '
                    var items = [];
                    gtag("set", {"currency": tvc_lc});
                    for(var t_item in tvc_ch){
                        items.push({
                            "id": tvc_ch[t_item].tvc_i,
                            "name": tvc_ch[t_item].tvc_n,
                            "category": tvc_ch[t_item].tvc_c,
                            "attributes": tvc_ch[t_item].tvc_attr,
                            "price": tvc_ch[t_item].tvc_p,
                            "quantity": tvc_ch[t_item].tvc_q
                        });
                        }';

            $code_step_1 = $code . 'gtag("event", "begin_checkout", {"event_category":"Enhanced-Ecommerce",
                            "event_label":"checkout_step_1","items":items,"non_interaction": true });';

            //check woocommerce version and add code
            $this->wc_version_compare($code_step_1);
        }

        if( $this->gm_id && $this->tracking_option == "GA4") {
            $code = '
                var items = [];
                for(var t_item in tvc_ch){
                    items.push({
                        "item_id": tvc_ch[t_item].tvc_i,
                        "item_name": tvc_ch[t_item].tvc_n,
                        "item_category": tvc_ch[t_item].tvc_c,
                        "item_variant": tvc_ch[t_item].tvc_attr,
                        "price": tvc_ch[t_item].tvc_p,
                        "quantity": tvc_ch[t_item].tvc_q
                    });
                    }';

            $code_step_1 = $code . 'gtag("event", "begin_checkout", {
                "event_category":"Enhanced-Ecommerce",
                "event_label":"checkout_step_1",
                "items":items,
                "non_interaction": true
            });';

            //check woocommerce version and add code
            $this->wc_version_compare($code_step_1);
        }
        /* facebook pixel */
        if($this->fb_pixel_id != ""){
          ?>
          <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
            var products = <?php echo json_encode($chkout_json); ?>;
            var cart_total = <?php echo json_encode($cart_total); ?>;

            var fb_content_ids = [];
            var fb_contents = [], num_items = 0;
            for(var t_item in products){
              num_items+=parseInt(products[t_item].tvc_q);
              fb_content_ids.push(products[t_item].tvc_id);
              fb_contents.push({"id":products[t_item].tvc_id, "quantity":products[t_item].tvc_q});
            }
            fbq("track", "InitiateCheckout", {
              content_type  : "product_group",
              content_name  : "Checkout Page",
              content_ids   : fb_content_ids,
              currency      : "<?php echo esc_js($this->ga_LC); ?>",
              num_items     : num_items,
              value         : cart_total,
              contents      : fb_contents
            })
          </script>
        <?php
        }
    }

    /**
     * Enhanced E-commerce tracking checkout step 2
     *
     * @access public
     * @return void
     */
    public function checkout_step_2_tracking() {
      if ($this->disable_tracking($this->ga_eeT) || !is_checkout()  || is_order_received_page() ) {
          return;
      }
        if($this->ga_id || $this->tracking_option == "UA" || $this->tracking_option == "BOTH" || $this->gm_id) {
            $code= '
                   var items = [];
                    gtag("set", {"currency": tvc_lc});
                    for(var t_item in tvc_ch){
                        items.push({
                            "id": tvc_ch[t_item].tvc_i,
                            "name": tvc_ch[t_item].tvc_n,
                            "category": tvc_ch[t_item].tvc_c,
                            "attributes": tvc_ch[t_item].tvc_attr,
                            "price": tvc_ch[t_item].tvc_p,
                            "quantity": tvc_ch[t_item].tvc_q
                        });
                        }';

            $code_step_2 = $code . 'gtag("event", "checkout_progress", {"checkout_step": 2,"event_category":"Enhanced-Ecommerce",
                            "event_label":"checkout_step_2","items":items,"non_interaction": true });';

            //if logged in and first name is filled - Guest Check out
            if (is_user_logged_in()) {
                $step2_onFocus = 't_tracked_focus=0;  if(t_tracked_focus===0){' . $code_step_2 . ' t_tracked_focus++;}';
            } else {
              $checkout_step_2_selector = ( isset( $this->c_t_o['tvc_checkout_step_2_selector']) && $this->c_t_o['tvc_checkout_step_2_selector'] == "custom" )?$this->c_t_o:array();
              $checkout_step_2_selector = $this->get_selector_val_from_array_for_gmt($checkout_step_2_selector, 'tvc_checkout_step_2_selector');
              $checkout_step_2_selector = ($checkout_step_2_selector)?$checkout_step_2_selector:"input[name=billing_first_name]";
                //first name on focus call fire
              $step2_onFocus = 't_tracked_focus=0; jQuery("'. esc_js($checkout_step_2_selector).'").on("focus",function(){ if(t_tracked_focus===0){' . $code_step_2 . ' t_tracked_focus++;}});';
            }
            //check woocommerce version and add code
            $this->wc_version_compare($step2_onFocus);
        }
    }

    /**
     * Enhanced E-commerce tracking checkout step 3
     *
     * @access public
     * @return void
     */
    public function checkout_step_3_tracking() {
      if ( $this->disable_tracking($this->ga_eeT) || !is_checkout()  || is_order_received_page() ) {
        return;
      }
        if($this->ga_id || $this->tracking_option == "UA" || $this->tracking_option == "BOTH" || $this->gm_id) {
            $code= '
             var items = [];
                for(var t_item in tvc_ch){
                        items.push({
                            "id": tvc_ch[t_item].tvc_i,
                            "name": tvc_ch[t_item].tvc_n,
                            "category": tvc_ch[t_item].tvc_c,
                            "attributes": tvc_ch[t_item].tvc_attr,
                            "price": tvc_ch[t_item].tvc_p,
                            "quantity": tvc_ch[t_item].tvc_q
                        });
                        }';

            //check if guest check out is enabled or not
            $step_2_on_proceed_to_pay = (!is_user_logged_in() && !$this->ga_gCkout ) || (!is_user_logged_in() && $this->ga_gCkout && $this->ga_gUser);

            $code_step_3 = $code . 'gtag("event", "checkout_progress", {"checkout_step": 3,"event_category":"Enhanced-Ecommerce",
                            "event_label":"checkout_step_3","items":items,"non_interaction": true });';
            $checkout_step_3_selector = ( isset( $this->c_t_o['tvc_checkout_step_3_selector']) && $this->c_t_o['tvc_checkout_step_3_selector'] == "custom" )?$this->c_t_o:array();
            $checkout_step_3_selector = $this->get_selector_val_from_array_for_gmt($checkout_step_3_selector, 'tvc_checkout_step_3_selector');
            $checkout_step_3_selector = ($checkout_step_3_selector)?$checkout_step_3_selector:"#place_order";
            $inline_js = 't_track_clk=0; jQuery(document).on("click","'. esc_js($checkout_step_3_selector).'",function(e){ if(t_track_clk===0){';
            if ($step_2_on_proceed_to_pay) {
                if (isset($code_step_2))
                    $inline_js .= $code_step_2;
            }
            $inline_js .= $code_step_3;
            $inline_js .= "t_track_clk++; }});";

            //check woocommerce version and add code
            $this->wc_version_compare($inline_js);
        }
    }

    /**
     * Get oredered Items for check out page.
     *
     * @access public
     * @return void
     */
    public function get_ordered_items() {
        global $woocommerce;
        $code = "";
        //get all items added into the cart
        foreach ($woocommerce->cart->cart_contents as $item) {
            $p = wc_get_product($item["product_id"]);
            $category = get_the_terms($item["product_id"], "product_cat");
            $categories = "";
            if ($category) {
                foreach ($category as $term) {
                    $categories.=$term->name . ",";
                }
            }
            //remove last comma(,) if multiple categories are there
            $categories = rtrim($categories, ",");            
            $chkout_json[get_permalink($p->get_id())] = array(
                "tvc_id" => esc_js($p->get_id()),
                "tvc_i" => esc_js($p->get_sku() ? $p->get_sku() : $p->get_id()),
                "tvc_n" => html_entity_decode(esc_js($p->get_title())),
                "tvc_p" => esc_js($p->get_price()),
                "tvc_c" => esc_js($categories),
                "tvc_q" => esc_js($item["quantity"]),
                "isfeatured" => esc_js($p->is_featured())
            );
            
        }
        //return $code;
        //make product data json on check out page
        $this->wc_version_compare("tvc_ch=" . json_encode($chkout_json) . ";");
        return $chkout_json;
    }
}
/**
 * GTM Tracking Data Layer Push
 **/
class Con_GTM_Tracking extends Con_Settings {
  protected $plugin_name;
  protected $version;
  public function __construct($plugin_name, $version) {
    parent::__construct();
    $this->plugin_name = $plugin_name;
    $this->version = $version;
    $this->TVC_Admin_Helper = new TVC_Admin_Helper();
    $this->tvc_options = array(      
      "affiliation"=>esc_js(get_bloginfo('name')),      
      "is_admin"=>esc_attr(is_admin()),
      "currency"=>esc_js($this->ga_LC),
      "tracking_option"=>esc_js($this->tracking_option),
      "property_id"=>esc_js($this->ga_id),
      "measurement_id"=>esc_js($this->gm_id),
      "google_ads_id"=>esc_js($this->google_ads_id),      
      "fb_pixel_id"=>esc_js($this->fb_pixel_id),
      "tvc_ajax_url"=>esc_url_raw(admin_url( 'admin-ajax.php' ))
    );
  }
  /**
   * begin datalayer like settings
   **/
  public function begin_datalayer(){
    if ($this->disable_tracking($this->ga_eeT)) {
      return;
    }
    $dataLayer = array( "event" => "begin_datalayer" );
    if($this->ga_id != ""){
      $dataLayer["cov_ga3_propety_id"] = esc_js($this->ga_id);
    }
    if($this->gm_id != ""){
      $dataLayer["cov_ga4_measurment_id"] = esc_js($this->gm_id);
    }
    if($this->remarketing_snippet_id != ""){
      $dataLayer["cov_remarketing_conversion_id"] = esc_js($this->remarketing_snippet_id);
    }
    if($this->fb_pixel_id != ""){
      $dataLayer["cov_fb_pixel_id"] = esc_js($this->fb_pixel_id);
    }
    if($this->microsoft_ads_pixel_id != ""){
      $dataLayer["cov_microsoft_uetq_id"] = esc_js($this->microsoft_ads_pixel_id);
    }
    if($this->twitter_ads_pixel_id != ""){
      $dataLayer["cov_twitter_pixel_id"] = esc_js($this->twitter_ads_pixel_id);
    }
    if($this->pinterest_ads_pixel_id != ""){
      $dataLayer["cov_pintrest_pixel_id"] = esc_js($this->pinterest_ads_pixel_id);
    }
    if($this->snapchat_ads_pixel_id != ""){
      $dataLayer["cov_snapchat_pixel_id"] = esc_js($this->snapchat_ads_pixel_id);
    }
    if($this->tiKtok_ads_pixel_id != ""){
      $dataLayer["cov_tiktok_sdkid"] = esc_js($this->tiKtok_ads_pixel_id);
    }
    $this->add_gtm_begin_datalayer_js($dataLayer);
  }
  /**
   * product list page
   **/
  public function product_list_view(){      
    if ($this->disable_tracking($this->ga_eeT)) {
      return;
    }
    $listtype = '';
    if ( isset( $woocommerce_loop['listtype'] ) && ( '' !== $woocommerce_loop['listtype'] ) ) {
      $listtype = $woocommerce_loop['listtype'];
    }
    global $product, $woocommerce_loop;
    $this->con_product_list_item_extra_tag($product, $listtype);    
  }
  /**
   * product page
   **/
  public function product_detail_view(){
    if ( $this->disable_tracking($this->ga_eeT) || !is_product() ) {
      return;
    }
    global  $wp_query, $woocommerce, $product, $con_view_item;
    $con_view_item = $this->con_item_product(
      $product,
      array(
        'productlink'  => get_permalink()
      )
    );
  }
  /**
   * product cart page
   **/
  public function product_cart_view() {
    if($this->disable_tracking($this->ga_eeT)){
      return;
    }
    global $woocommerce, $con_cart_item_list;
    foreach($woocommerce->cart->get_cart() as $key => $item){
      $product_id = $item["product_id"];
      $product = wc_get_product( $product_id );
      $remove_cart_item_link = "";
      if (version_compare($woocommerce->version, "3.3", "<")) {
        $remove_cart_item_link=html_entity_decode($woocommerce->cart->get_remove_url($key));
      } else {
        $remove_cart_item_link=html_entity_decode(wc_get_cart_remove_url($key));
      }
      $con_cart_item_list[] = $this->con_item_product(
        $product,
        array(
          "productlink"  => get_permalink(),
          "quantity" => $item["quantity"],
          "remove_cart_link" => $remove_cart_item_link
        )
      );
      $con_cart_item_list["value"] = WC()->cart->total;
    }
  }
  /**
   * product checkout page
   **/
  public function checkout_step_view(){    
    global $woocommerce, $con_checkout_cart_item_list;
    foreach($woocommerce->cart->get_cart() as $key => $item){
      $product_id = $item["product_id"];
      $product = wc_get_product( $product_id );
      $con_checkout_cart_item_list[] = $this->con_item_product(
        $product,
        array(
          "productlink"  => get_permalink(),
          "quantity" => $item["quantity"]
        )
      );
      $con_checkout_cart_item_list["value"] = WC()->cart->total;
    }  
  }
  /**
   * Thank You page
   **/
  public function product_thankyou_view($order_id = null){
    global $woocommerce, $con_ordered_item_list;
    $order = "";
    if($order_id == null && is_order_received_page()){
      $order = $this->tvc_get_order_from_order_received_page();
      $order_id = $order->get_id();
    }else{
      $order = new WC_Order($order_id);
    }
    if ($this->disable_tracking($this->ga_eeT) || current_user_can("manage_options") || get_post_meta($order_id, "_tracked", true) == 1 || !is_order_received_page() ){
      return;
    }
    $order_items = $order->get_items();
    if ( $order_items ) {
      foreach ( $order_items as $item ) {
        $product = $item->get_product();
        $con_ordered_item_list[] = $this->con_item_product(
          $product,
          array(
            "productlink"  => get_permalink(),
            "quantity" => $item["quantity"]
          )
        );
      }
      $con_ordered_item_list["value"] = esc_js($order->get_total());
      $con_ordered_item_list["transaction_id"] = esc_js($order->get_order_number());
      $con_ordered_item_list["affiliation"] = esc_js(get_bloginfo('name'));
      $con_ordered_item_list["tax"] = esc_js($order->get_total_tax());
      $con_ordered_item_list["shipping"] = esc_js($order->get_shipping_total());
      $con_ordered_item_list["coupon"] = esc_js(implode( ', ', ( $woocommerce->version > "3.7" ? $order->get_coupon_codes() : $order->get_used_coupons() ) ));
    }
    update_post_meta($order_id, "_tracked", 1);
  }
  /** 
   * dataLayer for setting and GTM global tag
   **/  
  public function add_gtm_begin_datalayer_js($data_layer){
    $has_html5_support    = current_theme_supports( 'html5' );
    echo '<script data-cfasync="false" data-pagespeed-no-defer' . ( $has_html5_support ? ' type="text/javascript"' : '' ) . '>
      window.dataLayer = window.dataLayer || [];
      dataLayer.push(' . wp_json_encode( $data_layer ) . ');
    </script>';
    ?>
    <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-K7X94DG');</script>
<!-- End Google Tag Manager -->
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K7X94DG"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <?php
  }
  /** 
   * DataLayer to JS
   **/
  public function add_gtm_data_layer_js($data_layer){
    $has_html5_support    = current_theme_supports( 'html5' );
    echo '<script data-cfasync="false" data-pagespeed-no-defer' . ( $has_html5_support ? ' type="text/javascript"' : '' ) . '>
      window.dataLayer = window.dataLayer || [];
      dataLayer.push(' . wp_json_encode( $data_layer ) . ');
    </script>
    ';
  }

  public function enqueue_scripts(){
    wp_enqueue_script(esc_js($this->plugin_name), esc_url_raw(ENHANCAD_PLUGIN_URL . '/public/js/con-gtm-google-analytics.js'), array('jquery'), esc_js($this->version), false);
  }
  /**
   * Creat DataLyer object for create JS data layer
   **/
  public function add_gtm_data_layer(){
    if ($this->disable_tracking($this->ga_eeT)) {
      return;
    }
    $affiliation = get_bloginfo('name');
    $impression_threshold = $this->ga_imTh;
    global $con_view_item_list,$con_view_item,$con_cart_item_list,$con_checkout_cart_item_list,$con_ordered_item_list;

    /**
     * Thankyou Page
     **/
    if(empty($con_ordered_item_list)){
      $con_ordered_item_list=array(); //define empty array so if empty
    }else{
      $dataLayer = array();
      $dataLayer["event"] = "purchase";
      if(!empty($con_ordered_item_list)){
        $dataLayer["ecommerce"]["transaction_id"] = (isset($con_ordered_item_list["transaction_id"]))?$con_ordered_item_list["transaction_id"]:"";
        $dataLayer["ecommerce"]["value"] = (isset($con_ordered_item_list["value"]))?$con_ordered_item_list["value"]:"";
        $dataLayer["ecommerce"]["affiliation"] = (isset($con_ordered_item_list["affiliation"]))?$con_ordered_item_list["affiliation"]:"";
        $dataLayer["ecommerce"]["tax"] = (isset($con_ordered_item_list["tax"]))?$con_ordered_item_list["tax"]:"";
        $dataLayer["ecommerce"]["shipping"] = (isset($con_ordered_item_list["shipping"]))?$con_ordered_item_list["shipping"]:"";
        $dataLayer["ecommerce"]["coupon"] = (isset($con_ordered_item_list["coupon"]))?$con_ordered_item_list["coupon"]:"";
        
        $dataLayer["ecommerce"]["currency"] =  $this->ga_LC;
        unset($con_ordered_item_list["transaction_id"]);
        unset($con_ordered_item_list["value"]);
        unset($con_ordered_item_list["affiliation"]);
        unset($con_ordered_item_list["tax"]);
        unset($con_ordered_item_list["shipping"]);
        unset($con_ordered_item_list["coupon"]);
        foreach($con_ordered_item_list as $key => $view_item){
          $dataLayer["ecommerce"]["items"][] = 
          array(
            "item_id" => isset($view_item["id"])?esc_js($view_item["id"]):"",
            "item_name" => isset($view_item["name"])?esc_js($view_item["name"]):"",
            "affiliation" => $affiliation,
            "currency" => $this->ga_LC,
            "item_category" => isset($view_item["category"])?esc_js($view_item["category"]):"",
            "price" => isset($view_item["price"])?esc_js($view_item["price"]):"",
            "quantity" => isset($view_item["quantity"])?esc_js($view_item["quantity"]):""
          );
        }
      }
      $this->add_gtm_data_layer_js($dataLayer);      
    }
    /**
     * Checkout Page
     **/
    if(empty($con_checkout_cart_item_list)){
      $con_checkout_cart_item_list=array(); //define empty array so if empty
    }else{
      $dataLayer = array();
      $dataLayer["event"] = "begin_checkout";
      if(!empty($con_checkout_cart_item_list)){
        if(isset($con_checkout_cart_item_list["value"]) && $con_checkout_cart_item_list["value"]){
          $dataLayer["ecommerce"]["value"] = $con_checkout_cart_item_list["value"];
        }
        $dataLayer["ecommerce"]["currency"] =  $this->ga_LC;
        unset($con_checkout_cart_item_list["value"]);
        foreach($con_checkout_cart_item_list as $key => $view_item){
          $dataLayer["ecommerce"]["items"][] = 
          array(
            "item_id" => isset($view_item["id"])?esc_js($view_item["id"]):"",
            "item_name" => isset($view_item["name"])?esc_js($view_item["name"]):"",
            "affiliation" => $affiliation,
            "currency" => $this->ga_LC,
            "item_category" => isset($view_item["category"])?esc_js($view_item["category"]):"",
            "price" => isset($view_item["price"])?esc_js($view_item["price"]):"",
            "quantity" => isset($view_item["quantity"])?esc_js($view_item["quantity"]):""
          );
        }
      }
      $this->add_gtm_data_layer_js($dataLayer);

      $checkout_step_2_selector = ( isset( $this->c_t_o['tvc_checkout_step_2_selector']) && $this->c_t_o['tvc_checkout_step_2_selector'] == "custom" )?$this->c_t_o:array();      
      $checkout_step_2_selector = $this->get_selector_val_from_array_for_gmt($checkout_step_2_selector, 'tvc_checkout_step_2_selector');
      $checkout_step_2_selector = ($checkout_step_2_selector)?$checkout_step_2_selector:"input[name=billing_first_name]";

      $checkout_step_3_selector = ( isset( $this->c_t_o['tvc_checkout_step_3_selector']) && $this->c_t_o['tvc_checkout_step_3_selector'] == "custom" )?$this->c_t_o:array();      
      $checkout_step_3_selector = $this->get_selector_val_from_array_for_gmt($checkout_step_3_selector, 'tvc_checkout_step_3_selector');
      $checkout_step_3_selector = ($checkout_step_3_selector)?$checkout_step_3_selector:"#place_order";
      
      ?>
      <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
        jQuery( document.body ).on("focus", "<?php echo $checkout_step_2_selector; ?>", function(event){
          tvc_js = new TVC_GTM_Enhanced(<?php echo json_encode($this->tvc_options); ?>);
          tvc_js.checkout_step_2_tracking();
        });
        jQuery( document.body ).on("click", "<?php echo $checkout_step_3_selector; ?>", function(event){
          tvc_js = new TVC_GTM_Enhanced(<?php echo json_encode($this->tvc_options); ?>);
          tvc_js.checkout_step_3_tracking();
        });
      </script>

      <?php    
    }
    /**
     * Cart Page
     **/
    if(empty($con_cart_item_list)){
      $con_cart_item_list=array(); //define empty array so if empty
    }else{
      $dataLayer = array();
      $dataLayer["event"] = "view_cart";
      if(!empty($con_cart_item_list)){
        if(isset($con_cart_item_list["value"]) && $con_cart_item_list["value"]){
          $dataLayer["ecommerce"]["value"] = $con_cart_item_list["value"];
        }
        $dataLayer["ecommerce"]["currency"] =  $this->ga_LC;
        unset($con_cart_item_list["value"]);
        foreach($con_cart_item_list as $key => $view_item){
          $dataLayer["ecommerce"]["items"][] = 
          array(
            "item_id" => isset($view_item["id"])?esc_js($view_item["id"]):"",
            "item_name" => isset($view_item["name"])?esc_js($view_item["name"]):"",
            "affiliation" => $affiliation,
            "currency" => $this->ga_LC,
            "item_category" => isset($view_item["category"])?esc_js($view_item["category"]):"",
            "price" => isset($view_item["price"])?esc_js($view_item["price"]):"",
            "quantity" => isset($view_item["quantity"])?esc_js($view_item["quantity"]):""
          );
        }
      }
      $this->add_gtm_data_layer_js($dataLayer);

      /*** Remove Cart item ***/
      ?>
      <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
        window.conCarttList = window.productList || [];
        conCarttList.push(<?php echo wp_json_encode($con_cart_item_list); ?>);
        jQuery( document.body ).on("click", "a[href*=\"?remove_item\"]", function(event){
          tvc_js = new TVC_GTM_Enhanced(<?php echo json_encode($this->tvc_options); ?>);
          tvc_js.remove_item_click(this);
        });
      </script>
      <?php   
    }
    /**
     * Product detail page
     **/
    if(empty($con_view_item)){
      $con_view_item=array(); //define empty array so if empty
    }else{
      $dataLayer = array();
      $dataLayer["event"] = "view_item";
      $dataLayer["ecommerce"]["items"][] = 
      array(
        "item_id" => isset($con_view_item["id"])?esc_js($con_view_item["id"]):"",
        "item_name" => isset($con_view_item["name"])?esc_js($con_view_item["name"]):"",
        "affiliation" => $affiliation,
        "currency" =>$this->ga_LC,
        "item_category" =>isset($con_view_item["category"])?esc_js($con_view_item["category"]):"",
        "price" =>isset($con_view_item["price"])?esc_js($con_view_item["price"]):"",
        "quantity" => 1
      );
      $this->add_gtm_data_layer_js($dataLayer);

      /*** Add to Cart product detail page ***/
      global $product,$woocommerce;
      $variations_data = array();
      if ( $product->is_type('variable') ) {
        $variations_data['default_attributes'] = $product->get_default_attributes();
        $variations_data['available_variations'] = $product->get_available_variations(); //get all child variations
        $variations_data['available_attributes'] = $product->get_variation_attributes();
      }
      $product_detail_addtocart_selector = ( isset( $this->c_t_o['tvc_product_detail_addtocart_selector']) && $this->c_t_o['tvc_product_detail_addtocart_selector'] == "custom" )?$this->c_t_o:array();
      ?>
      <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>           
        window.addEventListener('load', call_tvc_enhanced,true);
        function call_tvc_enhanced(){
          tvc_js = new TVC_GTM_Enhanced(<?php echo json_encode($this->tvc_options); ?>);
          tvc_js.singleProductaddToCartEventBindings(<?php echo json_encode($variations_data); ?>,"<?php echo esc_js($this->get_selector_val_fron_array($product_detail_addtocart_selector, 'tvc_product_detail_addtocart_selector')); ?>");
        }
      </script>
      <?php
    }

    /**
     * view_item_list
     **/
    if(empty($con_view_item_list)){
      $con_view_item_list=array(); //define empty array so if empty
    }else{
      $dataLayer = array();
      $dataLayer["event"] = "view_item_list";
      $items = array();      
      if(!empty($con_view_item_list)){
        foreach($con_view_item_list as $key => $view_item){                    
          $items[] = array(
            "item_id" => isset($view_item["id"])?esc_js($view_item["id"]):"",
            "item_name" => isset($view_item["name"])?esc_js($view_item["name"]):"",
            "affiliation" => $affiliation,
            "currency" =>$this->ga_LC,
            "index" =>$key,
            "item_category" =>isset($view_item["category"])?esc_js($view_item["category"]):"",
            "price" =>isset($view_item["price"])?esc_js($view_item["price"]):"",
            "quantity" => 1
          );
          if(count($items) >= $impression_threshold || $key >= (count($con_view_item_list)-1) ){
            $dataLayer["ecommerce"]["items"] = $items;
            $this->add_gtm_data_layer_js($dataLayer);
            $items = array();
          }          
        }

        /*** Add to Cart, product List  page ***/
        ?>
        <script data-cfasync="false" data-no-optimize="1" data-pagespeed-no-defer>
          window.conProductList = window.productList || [];
          conProductList.push(<?php echo wp_json_encode( $con_view_item_list); ?>);      
          window.addEventListener('load', call_tvc_enhanced_1ist_product,true);
          function call_tvc_enhanced_1ist_product(){
            tvc_js = new TVC_GTM_Enhanced(<?php echo json_encode($this->tvc_options); ?>);
            tvc_js.ListProductaddToCartEventBindings();
            tvc_js.ListProductSelectItemEventBindings();
          }
        </script>
        <?php
      }
    }    
  }
  public function con_product_list_item_extra_tag($product, $listtype){
    global $wp_query, $woocommerce_loop;
    global $con_view_item_list;

    if ( ! isset( $product ) ) {
      return;
    }
    if ( ! ( $product instanceof WC_Product ) ) {
      return false;
    }
    $product_id = $product->get_id();
    $product_cat = '';
    if ( is_product_category() ) {
      global $wp_query;
      $cat_obj = $wp_query->get_queried_object();    
      $product_cat = $cat_obj->name;    
    } else {
      $product_cat = $this->con_get_product_category( $product_id );
    }
    
    if ( is_search() ) {
      $list_name = __( 'Search Results', 'duracelltomi-google-tag-manager' );
    } elseif ( '' !== $listtype ) {
      $list_name = $listtype;
    } else {
      $list_name = __( 'General Product List', 'duracelltomi-google-tag-manager' );
    }
    $itemix = '';
    if ( isset( $woocommerce_loop['loop'] ) && ( '' !== $woocommerce_loop['loop'] ) ) {
      $itemix = $woocommerce_loop['loop'];
    }
    $paged          = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    $posts_per_page = get_query_var( 'posts_per_page' );
    if ( $posts_per_page < 1 ) {
      $posts_per_page = 1;
    }
    $item = $this->con_item_product(
      $product,
      array(
        'productlink'  => get_permalink(),
        'listname'     => $list_name,
        'listposition' => (int) $itemix + ( $posts_per_page * ( $paged - 1 ) ),
      )
    );
    $con_view_item_list[]= $item;
  }

  public function con_item_product( $product, $additional_product_attributes ) {
    global $gtm4wp_options;
    if ( ! $product ) {
      return false;
    }

    if ( ! ( $product instanceof WC_Product ) ) {
      return false;
    }

    $product_id     = $product->get_id();
    $product_type   = $product->get_type();
    $remarketing_id = $product_id;
    $product_sku    = $product->get_sku();

    if ( 'variation' === $product_type ) {
      $parent_product_id = $product->get_parent_id();
      $product_cat       = $this->con_get_product_category( $parent_product_id);
    } else {
      $product_cat = $this->con_get_product_category( $product_id );
    }

    $_temp_productdata = array(
      'id'         => $remarketing_id,
      'name'       => $product->get_title(),
      'sku'        => $product_sku ? $product_sku : $product_id,
      'category'   => $product_cat,
      'price'      => round( (float) wc_get_price_to_display( $product ), 2 ),
      'stocklevel' => $product->get_stock_quantity(),
    );   

    if ( 'variation' === $product_type ) {
      $_temp_productdata['variant'] = implode( ',', $product->get_variation_attributes() );
    }
    return array_merge( $_temp_productdata, $additional_product_attributes );
  }

  public function con_get_product_category_hierarchy( $category_id ) {
    $cat_hierarchy = '';

    $category_parent_list = get_term_parents_list(
      $category_id,
      'product_cat',
      array(
        'format'    => 'name',
        'separator' => '/',
        'link'      => false,
        'inclusive' => true,
      )
    );

    if ( is_string( $category_parent_list ) ) {
      $cat_hierarchy = trim( $category_parent_list, '/' );
    }

    return $cat_hierarchy;
  }

  public function con_get_product_category( $product_id, $fullpath = false ) {
    $product_cat = '';

    $_product_cats = wp_get_post_terms(
      $product_id,
      'product_cat',
      array(
        'orderby' => 'parent',
        'order'   => 'ASC',
      )
    );

    if ( ( is_array( $_product_cats ) ) && ( count( $_product_cats ) > 0 ) ) {
      $first_product_cat = array_pop( $_product_cats );
      if ( $fullpath ) {
        $product_cat = $this->con_get_product_category_hierarchy( $first_product_cat->term_id );
      } else {
        $product_cat = $first_product_cat->name;
      }
    }

    return $product_cat;
  }
}