<?php
/**
 * @since      4.0.2
 * Description: Conversios Onboarding page, It's call while active the plugin
 */
if ( ! class_exists( 'Conversios_Footer' ) ) {
	class Conversios_Footer {	
		public function __construct( ){
			add_action('add_conversios_footer',array($this, 'before_end_footer'));
			add_action('add_conversios_footer',array($this, 'before_end_footer_add_script'));
		}	
		public function before_end_footer(){ 
			?>  <div class="tvc_footer_links">
						
					</div>
				</div>
			<?php
		}

		public function before_end_footer_add_script()
		{
			$TVC_Admin_Helper = new TVC_Admin_Helper();
			$subscriptionId =  sanitize_text_field($TVC_Admin_Helper->get_subscriptionId());
			?>
			<script type="text/javascript">
			  jQuery(document).ready(function () {
			  	var screen_name = '<?php echo $_GET['page']; ?>';
			  	var error_msg = 'null';
				  jQuery('.navinfotopnav ul li a').click(function(){
				      var slug = $(this).find('span').text();
				      var menu = $(this).attr('href');
				      str_menu = slug.replace(/\s+/g, '_').toLowerCase();
				      user_tracking_data('click', error_msg,screen_name,'topmenu_'+str_menu);
				  });
				});

			   function user_tracking_data(event_name,error_msg,screen_name,event_label){
			   	// alert();
				    jQuery.ajax({
				      type: "POST",
				      dataType: "json",
				      url: tvc_ajax_url,
				      data: {action: "update_user_tracking_data", event_name:event_name, error_msg:error_msg, screen_name:screen_name,event_label:event_label},
				      success: function (response) {
				           console.log('user tracking');       
				      }
				    });
				  }
			</script>
			<?php
		}
	}
}
new Conversios_Footer();