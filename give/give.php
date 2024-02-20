<?php

/**
 * Plugin Name: Give - Donation Plugin
 * Plugin URI: https://givewp.com
 * Description: The most robust, flexible, and intuitive way to accept donations on WordPress.
 * Author: GiveWP
 * Author URI: https://givewp.com/
 * Version: 2.27.2
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Text Domain: give
 * Domain Path: /languages
 *
 * Give is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Give is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Give. If not, see <https://www.gnu.org/licenses/>.
 *
 * A Tribute to Open Source:
 *
 * "Open source software is software that can be freely used, changed, and shared (in modified or unmodified form) by
 * anyone. Open source software is made by many people, and distributed under licenses that comply with the Open Source
 * Definition."
 *
 * -- The Open Source Initiative
 *
 * Give is a tribute to the spirit and philosophy of Open Source. We at GiveWP gladly embrace the Open Source
 * philosophy both in how Give itself was developed, and how we hope to see others build more from our code base.
 *
 * Give would not have been possible without the tireless efforts of WordPress and the surrounding Open Source projects
 * and their talented developers. Thank you all for your contribution to WordPress.
 *
 * - The GiveWP Team
 */

use Give\Container\Container;
use Give\DonationForms\Repositories\DonationFormsRepository;
use Give\DonationForms\ServiceProvider as DonationFormsServiceProvider;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ServiceProvider as DonationServiceProvider;
use Give\DonationSummary\ServiceProvider as DonationSummaryServiceProvider;
use Give\DonorDashboards\Profile;
use Give\DonorDashboards\ServiceProvider as DonorDashboardsServiceProvider;
use Give\DonorDashboards\Tabs\TabsRegister;
use Give\Donors\Repositories\DonorRepositoryProxy;
use Give\Donors\ServiceProvider as DonorsServiceProvider;
use Give\Form\LegacyConsumer\ServiceProvider as FormLegacyConsumerServiceProvider;
use Give\Form\Templates;
use Give\Framework\Database\ServiceProvider as DatabaseServiceProvider;
use Give\Framework\DesignSystem\DesignSystemServiceProvider;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Exceptions\UncaughtExceptionLogger;
use Give\Framework\Http\ServiceProvider as HttpServiceProvider;
use Give\Framework\Migrations\MigrationsServiceProvider;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\ValidationRules\ValidationRulesServiceProvider;
use Give\Framework\WordPressShims\ServiceProvider as WordPressShimsServiceProvider;
use Give\LegacySubscriptions\ServiceProvider as LegacySubscriptionsServiceProvider;
use Give\License\LicenseServiceProvider;
use Give\Log\LogServiceProvider;
use Give\MigrationLog\MigrationLogServiceProvider;
use Give\MultiFormGoals\ServiceProvider as MultiFormGoalsServiceProvider;
use Give\PaymentGateways\ServiceProvider as PaymentGatewaysServiceProvider;
use Give\Promotions\ServiceProvider as PromotionsServiceProvider;
use Give\Revenue\RevenueServiceProvider;
use Give\Route\Form as FormRoute;
use Give\ServiceProviders\GlobalStyles as GlobalStylesServiceProvider;
use Give\ServiceProviders\LegacyServiceProvider;
use Give\ServiceProviders\Onboarding;
use Give\ServiceProviders\PaymentGateways;
use Give\ServiceProviders\RestAPI;
use Give\ServiceProviders\Routes;
use Give\ServiceProviders\ServiceProvider;
use Give\Subscriptions\Repositories\SubscriptionRepository;
use Give\Subscriptions\ServiceProvider as SubscriptionServiceProvider;
use Give\TestData\ServiceProvider as TestDataServiceProvider;
use Give\Tracking\TrackingServiceProvider;
use Give\VendorOverrides\Validation\ValidationServiceProvider;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Give Class
 *
 * @since 2.21.0 Remove php dependency validation logic and constant
 * @since 2.19.6 add $donations, $subscriptions, and replace $donors class with DonorRepositoryProxy
 * @since 2.8.0 build in a service container
 * @since 1.0
 *
 * @property-read Give_API $api
 * @property-read Give_Async_Process $async_process
 * @property-read Give_Comment $comment
 * @property-read Give_DB_Donor_Meta $donor_meta
 * @property-read Give_Emails $emails
 * @property-read Give_Email_Template_Tags $email_tags
 * @property-read Give_DB_Form_Meta $form_meta
 * @property-read Give_Admin_Settings $give_settings
 * @property-read Give_HTML_Elements $html
 * @property-read Give_Logging $logs
 * @property-read Give_Notices $notices
 * @property-read Give_DB_Payment_Meta $payment_meta
 * @property-read Give_Roles $roles
 * @property-read FormRoute $routeForm
 * @property-read Templates $templates
 * @property-read Give_Scripts $scripts
 * @property-read Give_DB_Sequential_Ordering $sequential_donation_db
 * @property-read Give_Sequential_Donation_Number $seq_donation_number
 * @property-read Give_Session $session
 * @property-read Give_DB_Sessions $session_db
 * @property-read Give_Tooltips $tooltips
 * @property-read PaymentGatewayRegister $gateways
 * @property-read DonationRepository $donations
 * @property-read DonorRepositoryProxy $donors
 * @property-read SubscriptionRepository $subscriptions
 * @property-read DonationFormsRepository $donationForms
 * @property-read Profile $donorDashboard
 * @property-read TabsRegister $donorDashboardTabs
 * @property-read Give_Recurring_DB_Subscription_Meta $subscription_meta
 *
 * @mixin Container
 */
final class Give
{
    /**
     * Give Template Loader Object
     *
     * @since  1.0
     * @access public
     *
     * @var    Give_Template_Loader object
     */
    public $template_loader;

    /**
     * Give No Login Object
     *
     * @since  1.0
     * @access public
     *
     * @var    Give_Email_Access object
     */
    public $email_access;

    /**
     * Give_Stripe Object.
     *
     * @since  2.5.0
     * @access public
     *
     * @var Give_Stripe
     */
    public $stripe;

    /**
     * @since 2.8.0
     *
     * @var Container
     */
    private $container;

    /**
     * @since 2.25.0 added HttpServiceProvider
     * @since      2.19.6 added Donors, Donations, and Subscriptions
     * @since      2.8.0
     *
     * @var array Array of Service Providers to load
     */
    private $serviceProviders = [
        LegacyServiceProvider::class,
        RestAPI::class,
        Routes::class,
        PaymentGateways::class,
        Onboarding::class,
        MigrationsServiceProvider::class,
        RevenueServiceProvider::class,
        MultiFormGoalsServiceProvider::class,
        DonorDashboardsServiceProvider::class,
        TrackingServiceProvider::class,
        TestDataServiceProvider::class,
        MigrationLogServiceProvider::class,
        LogServiceProvider::class,
        FormLegacyConsumerServiceProvider::class,
        LicenseServiceProvider::class,
        Give\Email\ServiceProvider::class,
        DonationSummaryServiceProvider::class,
        PaymentGatewaysServiceProvider::class,
        LegacySubscriptionsServiceProvider::class,
        Give\Exports\ServiceProvider::class,
        DonationServiceProvider::class,
        DonorsServiceProvider::class,
        SubscriptionServiceProvider::class,
        DonationFormsServiceProvider::class,
        PromotionsServiceProvider::class,
        LegacySubscriptionsServiceProvider::class,
        WordPressShimsServiceProvider::class,
        DatabaseServiceProvider::class,
        GlobalStylesServiceProvider::class,
        ValidationServiceProvider::class,
        ValidationRulesServiceProvider::class,
        HttpServiceProvider::class,
        DesignSystemServiceProvider::class,
    ];

    /**
     * @since 2.8.0
     *
     * @var bool Make sure the providers are loaded only once
     */
    private $providersLoaded = false;

    /**
     * Give constructor.
     *
     * Sets up the Container to be used for managing all other instances and data
     *
     * @since 2.8.0
     */
    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * Bootstraps the Give Plugin
     *
     * @since 2.8.0
     */
    public function boot()
    {
        $this->setup_constants();

        // Add compatibility notice for recurring and stripe support with Give 2.5.0.
        add_action('admin_notices', [$this, 'display_old_recurring_compatibility_notice']);

        add_action('plugins_loaded', [$this, 'init'], 0);

        register_activation_hook(GIVE_PLUGIN_FILE, [$this, 'install']);

        do_action('give_loaded');
    }

    /**
     * Init Give when WordPress Initializes.
     *
     * @since 1.8.9
     */
    public function init()
    {
        /**
         * Fires before the Give core is initialized.
         *
         * @since 1.8.9
         */
        do_action('before_give_init');

        // Set up localization.
        $this->load_textdomain();

        $this->bindClasses();

        $this->setupExceptionHandler();

        $this->loadServiceProviders();

        // Load form template
        $this->templates->load();

        // Load routes.
        $this->routeForm->init();

        /**
         * Fire the action after Give core loads.
         *
         * @since 1.8.7
         *
         * @param Give class instance.
         *
         */
        do_action('give_init', $this);
    }

    /**
     * Binds the initial classes to the service provider.
     *
     * @since 2.8.0
     */
    private function bindClasses()
    {
        $this->container->singleton('templates', Templates::class);
        $this->container->singleton('routeForm', FormRoute::class);
    }

    /**
     * Setup plugin constants
     *
     * @since  1.0
     * @access private
     *
     * @return void
     */
    private function setup_constants()
    {
        // Plugin version.
        if (!defined('GIVE_VERSION')) {
            define('GIVE_VERSION', '2.27.2');
        }

        // Plugin Root File.
        if (!defined('GIVE_PLUGIN_FILE')) {
            define('GIVE_PLUGIN_FILE', __FILE__);
        }

        // Plugin Folder Path.
        if (!defined('GIVE_PLUGIN_DIR')) {
            define('GIVE_PLUGIN_DIR', plugin_dir_path(GIVE_PLUGIN_FILE));
        }

        // Plugin Folder URL.
        if (!defined('GIVE_PLUGIN_URL')) {
            define('GIVE_PLUGIN_URL', plugin_dir_url(GIVE_PLUGIN_FILE));
        }

        // Plugin Basename aka: "give/give.php".
        if (!defined('GIVE_PLUGIN_BASENAME')) {
            define('GIVE_PLUGIN_BASENAME', plugin_basename(GIVE_PLUGIN_FILE));
        }

        // Make sure CAL_GREGORIAN is defined.
        if (!defined('CAL_GREGORIAN')) {
            define('CAL_GREGORIAN', 1);
        }
    }

    /**
     * Loads the plugin language files.
     *
     * @since  1.0
     * @access public
     *
     * @return void
     */
    public function load_textdomain()
    {
        // Set filter for Give's languages directory
        $give_lang_dir = dirname(plugin_basename(GIVE_PLUGIN_FILE)) . '/languages/';
        $give_lang_dir = apply_filters('give_languages_directory', $give_lang_dir);

        // Traditional WordPress plugin locale filter.
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $locale = apply_filters('plugin_locale', $locale, 'give');

        unload_textdomain('give');
        load_textdomain('give', WP_LANG_DIR . '/give/give-' . $locale . '.mo');
        load_plugin_textdomain('give', false, $give_lang_dir);
    }

    /**
     * Display compatibility notice for Give 2.5.0 and Recurring 1.8.13 when Stripe premium is not active.
     *
     * @since 2.5.0
     *
     * @return void
     */
    public function display_old_recurring_compatibility_notice()
    {
        // Show notice, if incompatibility found.
        if (
            defined('GIVE_RECURRING_VERSION')
            && version_compare(GIVE_RECURRING_VERSION, '1.9.0', '<')
            && defined('GIVE_STRIPE_VERSION')
            && version_compare(GIVE_STRIPE_VERSION, '2.2.0', '<')
        ) {
            $message = sprintf(
                __(
                    '<strong>Attention:</strong> GiveWP 2.5.0+ requires the latest version of the Recurring Donations add-on to process payments properly with Stripe. Please update to the latest version add-on to resolve compatibility issues. If your license is active, you should see the update available in WordPress. Otherwise, you can access the latest version by <a href="%1$s" target="_blank">logging into your account</a> and visiting <a href="%1$s" target="_blank">your downloads</a> page on the GiveWP website.',
                    'give'
                ),
                esc_url('https://givewp.com/wp-login.php'),
                esc_url('https://givewp.com/my-account/#tab_downloads')
            );

            Give()->notices->register_notice(
                [
                    'id' => 'give-compatibility-with-old-recurring',
                    'description' => $message,
                    'dismissible_type' => 'user',
                    'dismiss_interval' => 'shortly',
                ]
            );
        }
    }

    public function install()
    {
        $this->loadServiceProviders();
        give_install();
    }

    /**
     * Load all the service providers to bootstrap the various parts of the application.
     *
     * @since 2.8.0
     */
    private function loadServiceProviders()
    {
        if ($this->providersLoaded) {
            return;
        }

        $providers = [];

        foreach ($this->serviceProviders as $serviceProvider) {
            if (!is_subclass_of($serviceProvider, ServiceProvider::class)) {
                throw new InvalidArgumentException(
                    "$serviceProvider class must implement the ServiceProvider interface"
                );
            }

            /** @var ServiceProvider $serviceProvider */
            $serviceProvider = new $serviceProvider();

            $serviceProvider->register();

            $providers[] = $serviceProvider;
        }

        foreach ($providers as $serviceProvider) {
            $serviceProvider->boot();
        }

        $this->providersLoaded = true;
    }

    /**
     * Register a Service Provider for bootstrapping
     *
     * @since 2.8.0
     *
     * @param string $serviceProvider
     */
    public function registerServiceProvider($serviceProvider)
    {
        $this->serviceProviders[] = $serviceProvider;
    }

    /**
     * Magic properties are passed to the service container to retrieve the data.
     *
     * @since 2.8.0 retrieve from the service container
     * @since 2.7.0
     *
     * @param string $propertyName
     *
     * @return mixed
     * @throws Exception
     */
    public function __get($propertyName)
    {
        return $this->container->get($propertyName);
    }

    /**
     * Magic methods are passed to the service container.
     *
     * @since 2.8.0
     *
     * @param $arguments
     *
     * @param $name
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }

    /**
     * Retrieves the underlying container instance. This isn't usually necessary, but sometimes we want to pass along
     * the container itself.
     *
     * @since 2.24.0
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Sets up the Exception Handler to catch and handle uncaught exceptions
     *
     * @since 2.11.1
     */
    private function setupExceptionHandler()
    {
        $handler = new UncaughtExceptionLogger();
        $handler->setupExceptionHandler();
    }
}

/**
 * Start Give
 *
 * The main function responsible for returning the one true Give instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $give = Give(); ?>
 *
 * @since 2.8.0 add parameter for quick retrieval from container
 * @since 1.0
 *
 * @param null $abstract Selector for data to retrieve from the service container
 *
 * @return object|Give
 */
function give($abstract = null)
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Give();
    }

    if ($abstract !== null) {
        return $instance->make($abstract);
    }

    return $instance;
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/vendor-prefixed/autoload.php';

give()->boot();


//----------------------------------Custom User Dashboard -----------------

function add_custom_meta_box() {
    $screens = array( 'give_forms' ); // Specify the post types where you want to add the meta box

    foreach ( $screens as $screen ) {
        add_meta_box(
            'custom_meta_box', // Unique ID of the meta box
            'Donation URL', // Title of the meta box
            'render_custom_meta_box', // Callback function to render the content of the meta box
            $screen // Post type(s) where the meta box should be added
        );
    }
}
add_action( 'add_meta_boxes', 'add_custom_meta_box' );

// Render meta box content
function render_custom_meta_box( $post ) {
    // Retrieve the current meta value, if it exists
    $custom_meta_value = get_post_meta( $post->ID, 'custom_meta_key', true );

    // Display the input field for the custom meta value
    echo '<label for="custom_meta_field">Custom Meta Field:</label>';
    echo '<input type="text" id="custom_meta_field" name="custom_meta_field" value="' . esc_attr( $custom_meta_value ) . '" />';
}

// Save meta box data
function save_custom_meta_box( $post_id ) {
    if ( isset( $_POST['custom_meta_field'] ) ) {
        $custom_meta_value = sanitize_text_field( $_POST['custom_meta_field'] );
        update_post_meta( $post_id, 'custom_meta_key', $custom_meta_value );
    }
}
add_action( 'save_post', 'save_custom_meta_box' );


function create_shortcode(){





$current_user = wp_get_current_user();

if ( is_user_logged_in() ) :
    $user_id      = get_current_user_id();
    $display_name = $current_user->display_name;


    $donor        = new Give_Donor( $user_id, true );
    $address      = $donor->get_donor_address();
    $company_name = $donor->get_meta( '_give_donor_company', true );
    $donations             = array();
    $donation_history_args = Give()->session->get( 'give_donation_history_args' );
    $donations = give_get_users_donations( get_current_user_id(), 20, true, 'any' );

$currency_code   = give_get_payment_currency_code( $donations[0]->ID );
$donation_amount = give_donation_amount( $donations[0]->ID, true );


$currentDate = date('Y-m-d');

// Calculate the date 6 months ago
$sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));

// Initialize variables for total values
$totalValue = 0;
$totalValueLast6Months = 0;
$i = 0;
// Iterate through the donations
foreach ($donations as $donation) {
    // Get the donation ID and pass it to the give_donation_amount() function
    $donationID = $donation->ID;
    $donationAmount = give_donation_amount($donations[$i]->ID);
    //prinr_r(give_donation_amount($donation->ID, true));

    // Calculate the total value of all donations
    $totalValue += $donationAmount;

    // Check if the donation falls within the last 6 months
    $donationDate = $donation->post_date;
    if ($donationDate >= $sixMonthsAgo && $donationDate <= $currentDate) {
        // Calculate the total value of donations within the last 6 months
        $totalValueLast6Months += $donationAmount;
    }
    $i++;
}


// echo "<pre>";
// print_r($address['country']);
?>
<div class="elementor-section ">
	

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- Top container -->
    
    
    <!-- Sidebar/menu -->
    <nav class="w3-sidebar w3-collapse w3-dark_blue w3-animate-left" style="width:300px;" id="mySidebar"><br>
      <div class="w3-container w3-row">
        <div class="w3-col s4">
			</div>
        <div class="w3-col w3-bar">
          <h3><?php the_title(); ?></h3>
			<span>Welcome <?php echo $display_name; ?></span>
          <!-- <a href="#" class="w3-bar-item w3-button"><i class="fa fa-envelope"></i></a>
          <a href="#" class="w3-bar-item w3-button"><i class="fa fa-user"></i></a>
          <a href="#" class="w3-bar-item w3-button"><i class="fa fa-cog"></i></a> -->
        </div>
      </div>
      <hr>
      <div class="w3-container">
        <!-- <h5>Dashboard</h5> -->
      </div>
      <div class="w3-bar-block">
        <a href="#" class="w3-bar-item w3-button w3-padding-16 w3-hide-large w3-dark-grey w3-hover-black" onclick="w3_close()" title="close menu"><i class="fa fa-remove fa-fw"></i>  Close Menu</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding-16 w3-margin-top w3-blue" onclick="clickHandle(event, 'custom_dashboard', w3_close() )"><i class="fa fa-home fa-fw"></i>Dashboard</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top one-link" onclick="clickHandle(event, 'custom_One_Time_Donations', w3_close())"><i class="fa fa-cc-mastercard fa-fw"></i>  One-Time Donations</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top" onclick="clickHandle(event, 'custom_Subcribtions', w3_close())"><i class="fa fa-circle-o-notch fa-fw"></i>  Subscriptions</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top" onclick="clickHandle(event, 'custom_donate_now', w3_close() )"><i class="fa fa-credit-card-alt fa-fw"></i>  Donate Now</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top" onclick="clickHandle(event, 'custom_Tax_Receipts', w3_close())"><i class="fa fa-file-text-o fa-fw"></i>  Tax Receipts</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top" onclick="clickHandle(event, 'custom_Settings', w3_close())"><i class="fa fa-cogs fa-fw"></i>  Settings</a>
        <br>
		   <span class="w3-bar-item ">
		<form method="POST" action="">
		  <button type="submit" class="btn_logout" name="submit_logout" style="border:1px solid white; background:#009688; color:white !important; " >Log Out</button>
		</form>
	  </span>
		   <br><br>
      </div>
    </nav>
    
    
    <!-- Overlay effect when opening sidebar on small screens -->
    <div class="w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>
    
    <!-- !PAGE CONTENT! -->
    <div class="w3-main" style="margin-left:300px;">
    
      <!-- Header -->
		<div class="w3-bar w3-black w3-large" style="z-index:0">
      <button class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey" onclick="w3_open();"><i class="fa fa-bars"></i> Menu</button>
      </div>
<!--       <header class="w3-container w3-show w3-margin-bottom w3-padding-16" id="custom_nav_dashboard" style=" box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;"> 
        <a href="#" class="w3-button  w3-white">Home</a>
        <a href="#" class="w3-button w3-white">Who we are</a>
        <a href="#" class="w3-button w3-white">Domestic Projects</a>
        <a href="#" class="w3-button w3-white">International Projects</a>
        <a href="#" class="w3-button w3-white">Contact</a>
      </header>-->
    	
      <div id="custom_dashboard" class="tabcontent w3-row-padding w3-margin-bottom">
        <div class="w3-half">
          <div class="w3-container w3-teal w3-padding-16">
            <div class="w3-left w3-xxxlarge"><i class="fa fa-dollar w3-xxxlarge"></i><?php echo $totalValue; ?></div>
            <div class="w3-right">
              <h3 class="w3-large">This Year</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Total Donations</h4>
          </div>
        </div>
        <div class="w3-half">
            <div class="w3-container w3-blue w3-padding-16">
              <div class="w3-left w3-xxxlarge"><i class="fa fa-dollar w3-xxxlarge"></i><?php echo $totalValueLast6Months; ?></div>
              <div class="w3-right">
                <h3 class="w3-large">6 Months</h3>
              </div>
              <div class="w3-clear"></div>
              <h4>Total Donations</h4>
            </div>
          </div>
        
      
    
      <div class="w3-panel">
        <div class="w3-row-padding" style="margin:0 -16px">
          
          <div class="w3-full">
            <h5 style="margin-top:30px;">Donation by month</h5>
            <table class="w3-table w3-striped w3-white">
              <tr>

                <th>Project Name</th>
                <th>Project Type</th>
                <th>Donation Type</th>
                <th>Amount</th>
              </tr>
        		
				<?php 

// $donation_id     = $donation->ID;
// $donation_number = Give()->seq_donation_number->get_serial_code( $donation_id );
// $form_id         = give_get_payment_meta( $donation_id, '_give_payment_form_id', true );
// $form_name       = give_get_donation_form_title( $donation_id );
// $user            = give_get_payment_meta_user_info( $donation_id );
// $email           = give_get_payment_user_email( $donation_id );
// $status          = $donation->post_status;
// $status_label    = give_get_payment_status( $donation_id, true );
// $company_name    = give_get_payment_meta( $donation_id, '_give_donation_company', true );
// $company_name = $donor->get_meta( '_give_donor_company', true );
//$donations = give_get_users_donations( 0, 20, true, 'any' );
//$donation = give_get_users_donations( $email, give_get_limit_display_donations(), true, 'any' );

	
				$donor        = new Give_Donor( $user_id, true );
				$donations = give_get_users_donations( get_current_user_id(), 20, true, 'any' ); 
				//echo '<pre>';
				//print_r($donation);
				//
				foreach($donations as $dno){
					$donation_data = give_get_payment_meta( $dno->ID );
					$categories = get_the_terms( $donation_data['form_id'], 'give_forms_category' );
					$category_name = $categories[0]->name;
					
					//echo '<pre>';
				   // print_r($donation_data);
					$subs = 0;
					if (isset($donation_data['_give_is_donation_recurring']) && 															$donation_data['_give_is_donation_recurring'] != 0) {
					$subs = 'Monthly Subscription';
				} else {
					$subs = 'One-Time';
				}
					$floatValue = $donation_data['_give_payment_total'];
					$formattedValue = number_format($floatValue, 2, '.', '');
	?>
				
				<tr>
					<td><?php echo $donation_data['_give_payment_form_title']; ?></td>
					<td><?php echo $category_name; ?></td>
					<td><?php echo $subs; ?></td>
<!-- 					<td><?php // echo $address['country']; ?></td> -->
					<td>$ <?php echo $formattedValue; ?></td>
				</tr>
				<?php } ?>

            </table>

<!-- <h5 style="margin-top:30px;">Top 10 Donors</h5> -->
<!--  <table class="w3-table w3-striped w3-white">
              <tr>
				<th>Serial Number</th>
                <th>Donor Name</th>
                <th>Donated Amount</th>
				
              </tr>
			  <?php //$wpdb->prepare("SELECT name, purchase_value FROM wp_give_donors order by purchase_value desc limit 3 WHERE user_id = %s", $user_id);

global $wpdb;

// $query = "SELECT name, purchase_value FROM {$wpdb->prefix}give_donors ORDER BY purchase_value DESC LIMIT 10";

// $results = $wpdb->get_results($query);
// $counter=0;
// if ($results) {
//     foreach ($results as $result) {
// 		 $counter=$counter+1;
//         $name = $result->name;
//         $purchase_value = $result->purchase_value;
        ?>
<tr>
<td><?php // echo $counter; ?></td>
<td><?php // echo $name; ?></td>
<td><?php // echo $purchase_value; ?></td>
</tr>
	 <?php
//     }
// } else {
//     echo "No results found.";
// }
// 			  ?>
			  </table> -->
          </div>
        </div>
      </div>
   </div>
        <!-- One Time Donations-->
  <div id="custom_One_Time_Donations" class="tabcontent one-show">
            <h2>Donations List</h2>
            <?php echo do_shortcode( '[donation_history donor="true" status="true" payment_method="true"]' );?>
            
        </div>
        <!-- Subcribtions-->

        <!-- Subcribtions-->
        <div id="custom_Subcribtions" class="tabcontent">
            <h2>Subscriptions</h2>
			<?php echo do_shortcode('[give_subscriptions]'); ?>
			
			
			
        </div>
        <!-- Subcribtions-->
        
        <!-- Tax Receipts-->
        <div id="custom_Tax_Receipts" class="tabcontent">
            <h2>Annual Receipts</h2>
            <table class="table">
				<thead>
					<tr>
						<td>Annual Reciept Type</td>
						<td>Year</td>
						<td>PDF Download</td>
					</tr>
				</thead>
				<?php $args = array(
                    'post_type'     => 'give_forms',
                    'post_status'   => 'publish',
                    'order'         => 'DESC',
                    
                    'posts_per_page' => -1,
                );
                
                $slider = get_posts($args);
                // echo "<pre>";
                // print_r($slider);
		


                $i = 0;  
                if ($slider) { 
                foreach ($slider as $donation) {
                setup_postdata($donation);
                
                
                $donationID = $donation->ID;
                $donationAmount = give_donation_amount($donations[$i]->ID);
                $email = Give()->session->get( 'give_email' );
                $donor = Give()->donors->get_donor_by( 'email', $email );
                
                $pdate = $donation->post_date; 
                $datetime = new DateTime($pdate);
                $dy = $datetime->format('Y');
//                	echo "<pre>";
//                 print_r($donor->id);
            ?>
              <tr>
                <td><?php echo $donation->post_title; ?></td>
                <td><?php echo $dy; /*echo $donationID;*/ ?></td>
                <td><a class="anc-pdf" href="<?php echo get_home_url();?>/?give_action=preview_annual_receipts&donor=<?php echo $donor->id; ?>&receipt_year=<?php echo $dy; ?>" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a></td>
              </tr>
          <?php $i++; } }else{echo "No Post Found"; } ?>
				
				
			</table>
        </div>
        <!-- Tax Receipts-->
        
        <!-- Settings-->
        <div id="custom_Settings" class="tabcontent">
            <h2>Settings</h2>
            <?php echo do_shortcode( '[give_profile_editor]' );?>
        </div>
        <!-- Settings-->
        <!-- Tabs    -->
        <div id="custom_donate_now" class="tabcontent">
            <div class="w3-row-padding w3-margin-bottom">
                <div class="w3-full">
                <h2>Donate now</h2>
                </div>    
                <?php 

                $args = array(
                    'post_type'     => 'give_forms',
                    'post_status'   => 'publish',
                    'order'         => 'DESC',
                    
                    'posts_per_page' => -1,
                );

                
                
                $slider = get_posts($args);
                // echo "<pre>";
                // print_r($slider);
                //exit;
                $i = 0;   
                foreach ($slider as $donation) { 


                ?>
                <div class="w3-half">
                <div class="w3-container w3-blue w3-padding-16 w3-margin">
                    <!-- <div class="w3-left w3-xxxlarge"><i class="fa fa-dollar w3-xxxlarge"></i>0</div> -->
                    <div class="w3-right">
                    <!-- <h3 class="w3-large">This Years</h3> -->
                    </div>
                    <div class="w3-clear"></div>
                    <h4 class="w3-xlarge"><?php echo $donation->post_title; ?></h4>
                    <h3 class="w3-large"><?php echo $donation->post_excerpt; ?>
                    </h3>
                    <a href="<?php echo get_permalink($donation->ID);  ?>" class="w3-button w3-margin-bottom w3-margin-top w3-white">Donate now</a>
        
                </div>
                </div>
                <?php $i++; } wp_reset_postdata(); ?>


               

            </div>
        </div>
        <!-- Tabs    -->
    
      <!-- End page content -->
    </div>
    
    <script>
    // Get the Sidebar
    var mySidebar = document.getElementById("mySidebar");
    
    // Get the DIV with overlay effect
    var overlayBg = document.getElementById("myOverlay");
    
    // Toggle between showing and hiding the sidebar, and add overlay effect
    function w3_open() {
      if (mySidebar.style.display === 'block') {
        mySidebar.style.display = 'none';
        overlayBg.style.display = "none";
      } else {
        mySidebar.style.display = 'block';
        overlayBg.style.display = "block";
      }
    }
    
    // Close the sidebar with the close button
    function w3_close() {
      mySidebar.style.display = "none";
      overlayBg.style.display = "none";
    }
        
//  Custom Tabs 
    
    function clickHandle(evt, custom_dashboard) {
  let i, tabcontent, tablinks;

  // This is to clear the previous clicked content.
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Set the tab to be "active".
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
    
  }

  var diva = document.querySelector(".give-donation-details a");
  

  // Display the clicked tab and set it to active.
  document.getElementById(custom_dashboard).style.display = "block";
  evt.currentTarget.className += " active";
}
// Simulate a click on the first tab button when the page loads
    window.addEventListener("load", function () {
      document.getElementsByClassName("tablinks")[0].click();
    });



    
    jQuery('.give-donation-details a').click(function(e) {
        localStorage.clear();
        localStorage.setItem("one-link", "active");
        localStorage.setItem("one-show", "show");
    });
    jQuery(document).ready(function() {
        var activeTabWithClass = localStorage.getItem('one-link');
        if (activeTabWithClass) {
            
            setTimeout(function() {
            jQuery('.tablinks').removeClass('active');
            jQuery('.one-link').addClass('active');
            jQuery('.tabcontent').hide();
            jQuery('.one-show').show();
            console.log('Timeout executed!');
            }, 500);
            
        }
    });



    </script>
    
    

</div>


<?php

endif;



// Function to get the donation amount


}

add_shortcode('custom_donation_dashboard', 'create_shortcode');



if (isset($_POST['give_profile_editor_submit'])) {
    $user_id = $_POST['user_id'];
    $address1 = $_POST['_give_donor_address_billing_line1_0'];
    $address2 = $_POST['_give_donor_address_billing_line2_0'];
    $country = $_POST['_give_donor_address_billing_country_0'];
    $state = $_POST['_give_donor_address_billing_state_0'];
    $city = $_POST['_give_donor_address_billing_city_0'];
    $zip = $_POST['_give_donor_address_billing_zip_0'];
    $table_name = $wpdb->prefix . 'give_donormeta';
    global $wpdb;

// Getting Current Donor Id
    $donorID = $wpdb->prepare("SELECT id FROM wp_give_donors WHERE user_id = %s", $user_id);
    $d_ID = $wpdb->get_var($donorID);


if ($d_ID) {
// 	adr 1
        $existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_line1_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_line1_0'", $address1, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_line1_0',
                    'meta_value' => $address1,
                )
            );
        }
//end
	
// 	adr 2
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_line2_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_line2_0'", $address2, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_line2_0',
                    'meta_value' => $address2,
                )
            );
        }
// 	end

// 	adr 3
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_country_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_country_0'", $country, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_country_0',
                    'meta_value' => $country,
                )
            );
        }
// 	end
		
// 	adr 4
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_state_0'));
	//	print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_state_0'", $state, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_state_0',
                    'meta_value' => $state,
                )
            );
        }
// 	end

	// 	adr 5
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_city_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_city_0'", $city, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_city_0',
                    'meta_value' => $city,
                )
            );
        }
// 	end
		
		// 	adr 6
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_zip_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_zip_0'", $zip, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_zip_0',
                    'meta_value' => $zip,
                )
            );
        }
// 	end
	
    }

    sleep(1);
}